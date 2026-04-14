<?php
session_start();
include '../config/koneksi.php';

// proteksi role petugas
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

?>

<h2>Check-in Tiket</h2>

<form method="POST">
    Masukkan Kode Tiket:
    <input type="text" name="kode" required>
    <button type="submit">Check-in</button>
</form>

<hr>

<?php
if (isset($_POST['kode'])) {

    $kode = $_POST['kode'];

    $data = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM attendee WHERE kode_tiket='$kode'
    "));

    if ($data) {

        if ($data['status_checkin'] == 'belum') {

            mysqli_query($conn, "
                UPDATE attendee 
                SET status_checkin='sudah', waktu_checkin=NOW()
                WHERE kode_tiket='$kode'
            ");

            echo "<p style='color:green;'>✅ Check-in berhasil!</p>";

        } else {
            echo "<p style='color:red;'>❌ Tiket sudah digunakan!</p>";
        }

    } else {
        echo "<p style='color:red;'>❌ Kode tiket tidak ditemukan!</p>";
    }
}
?>