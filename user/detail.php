<?php
session_start();
include '../config/koneksi.php';

// proteksi login
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ambil id event
$id = $_GET['id'] ?? 0;

// ambil data event
$event = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM event WHERE id_event=$id
"));

// ambil tiket berdasarkan event
$tiket = mysqli_query($conn, "
    SELECT * FROM tiket WHERE id_event=$id
");
?>

<h2>Pilih Tiket</h2>

<!-- INFO EVENT -->
<h3><?= $event['nama_event'] ?? 'Event tidak ditemukan' ?></h3>
<p>Tanggal: <?= $event['tanggal'] ?? '-' ?></p>

<hr>

<form action="order.php" method="POST">

<?php 
$ada_tiket = false;
while($t = mysqli_fetch_assoc($tiket)) { 
    $ada_tiket = true;
?>
    <div style="margin-bottom:15px; border:1px solid #ccc; padding:10px;">
        <b><?= $t['nama_tiket'] ?></b><br>
        Harga: Rp <?= number_format($t['harga']) ?><br>
        Sisa Kuota: <?= $t['kuota'] ?><br>

        Jumlah:
        <input 
            type="number" 
            name="qty[<?= $t['id_tiket'] ?>]" 
            min="0" 
            max="<?= $t['kuota'] ?>" 
            value="0"
        >
    </div>
<?php } ?>

<?php if (!$ada_tiket) { ?>
    <p><i>Tidak ada tiket tersedia untuk event ini.</i></p>
<?php } ?>

<hr>

<!-- INPUT VOUCHER -->
<h4>Kode Voucher (opsional)</h4>
<input type="text" name="kode_voucher" placeholder="Masukkan kode voucher">

<br><br>

<button type="submit">Pesan</button>

</form>