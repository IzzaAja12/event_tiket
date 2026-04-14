<?php
session_start();
include '../config/koneksi.php';

// proteksi akses
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: event.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$qty = $_POST['qty'] ?? [];
$kode_voucher = $_POST['kode_voucher'] ?? '';

// =======================
// VALIDASI QTY
// =======================
if (empty($qty)) {
    echo "Tidak ada tiket yang dipilih!";
    exit;
}

$ada = false;
foreach ($qty as $jumlah) {
    if ($jumlah > 0) {
        $ada = true;
        break;
    }
}

if (!$ada) {
    echo "Silakan isi jumlah tiket!";
    exit;
}

// =======================
// HITUNG TOTAL
// =======================
$total = 0;

foreach ($qty as $id_tiket => $jumlah) {
    if ($jumlah > 0) {

        $data = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT harga FROM tiket WHERE id_tiket=$id_tiket
        "));

        $subtotal = $data['harga'] * $jumlah;
        $total += $subtotal;
    }
}

// =======================
// CEK VOUCHER
// =======================
$potongan = 0;
$id_voucher = NULL;

if (!empty($kode_voucher)) {

    $voucher = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM voucher WHERE kode_voucher='$kode_voucher'
    "));

    if ($voucher) {
        if ($voucher['status'] == 'aktif' && $voucher['kuota'] > 0) {

            $potongan = $voucher['potongan'];
            $id_voucher = $voucher['id_voucher'];

            // kurangi kuota
            mysqli_query($conn, "
                UPDATE voucher 
                SET kuota = kuota - 1 
                WHERE id_voucher = {$voucher['id_voucher']}
            ");

        } else {
            echo "Voucher tidak valid atau habis!";
            exit;
        }
    } else {
        echo "Kode voucher tidak ditemukan!";
        exit;
    }
}

// =======================
// TOTAL AKHIR
// =======================
$total_akhir = $total - $potongan;

if ($total_akhir < 0) {
    $total_akhir = 0;
}

// =======================
// SIMPAN KE ORDERS
// =======================
mysqli_query($conn, "
INSERT INTO orders (id_user, tanggal_order, total, status, id_voucher)
VALUES ('$id_user', NOW(), '$total_akhir', 'pending', " . ($id_voucher ? $id_voucher : "NULL") . ")
");

$id_order = mysqli_insert_id($conn);

// =======================
// SIMPAN KE ORDER DETAIL
// =======================
foreach ($qty as $id_tiket => $jumlah) {
    if ($jumlah > 0) {

        $data = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT harga FROM tiket WHERE id_tiket=$id_tiket
        "));

        $subtotal = $data['harga'] * $jumlah;

        mysqli_query($conn, "
        INSERT INTO order_detail (id_order, id_tiket, qty, subtotal)
        VALUES ('$id_order','$id_tiket','$jumlah','$subtotal')
        ");
    }
}

// =======================
// GENERATE TIKET (ATTENDEE)
// =======================
foreach ($qty as $id_tiket => $jumlah) {
    if ($jumlah > 0) {

        // ambil id_detail terakhir
        $detail = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT id_detail FROM order_detail 
            WHERE id_order='$id_order' AND id_tiket='$id_tiket'
            ORDER BY id_detail DESC LIMIT 1
        "));

        $id_detail = $detail['id_detail'];

        for ($i = 0; $i < $jumlah; $i++) {

            $kode = "TKT-" . strtoupper(uniqid());

            mysqli_query($conn, "
                INSERT INTO attendee (id_detail, kode_tiket, status_checkin)
                VALUES ('$id_detail', '$kode', 'belum')
            ");
        }
    }
}

// =======================
// OUTPUT
// =======================
echo "<h3>Pesanan berhasil!</h3>";
echo "Total sebelum diskon: Rp " . number_format($total) . "<br>";
echo "Diskon: Rp " . number_format($potongan) . "<br>";
echo "<b>Total bayar: Rp " . number_format($total_akhir) . "</b><br><br>";

echo "<b>Tiket kamu sudah dibuat 🎫</b>";
?>