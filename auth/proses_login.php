<?php
session_start();
include '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['role'] = $data['role'];

    if ($data['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($data['role'] == 'user') {
        header("Location: ../user/dashboard.php");
    } elseif ($data['role'] == 'petugas') {
        header("Location: ../petugas/checkin.php");
    }
} else {
    echo "Login gagal!";
}
?>