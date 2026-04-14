<?php
session_start();
include '../config/koneksi.php';

$data = mysqli_query($conn, "
SELECT event.*, venue.nama_venue 
FROM event 
JOIN venue ON event.id_venue = venue.id_venue
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Daftar Event</h2>

    <div class="row">
        <?php while($row = mysqli_fetch_assoc($data)) { ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title"><?= $row['nama_event'] ?></h5>
                    <p class="card-text">
                        📅 <?= $row['tanggal'] ?><br>
                        📍 <?= $row['nama_venue'] ?>
                    </p>
                    <a href="detail.php?id=<?= $row['id_event'] ?>" class="btn btn-primary w-100">
                        Lihat Tiket
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

</body>
</html>