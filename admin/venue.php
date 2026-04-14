<?php
session_start();
include '../../config/koneksi.php';

/* =======================
   DELETE
======================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue=$id");
    header("Location: venue.php");
    exit;
}

/* =======================
   INSERT
======================= */
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_venue'];
    $alamat = $_POST['alamat'];
    $kapasitas = $_POST['kapasitas'];

    mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas)
    VALUES ('$nama','$alamat','$kapasitas')");

    header("Location: venue.php");
    exit;
}

/* =======================
   UPDATE
======================= */
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_venue'];
    $alamat = $_POST['alamat'];
    $kapasitas = $_POST['kapasitas'];

    mysqli_query($conn, "UPDATE venue 
    SET nama_venue='$nama', alamat='$alamat', kapasitas='$kapasitas'
    WHERE id_venue=$id");

    header("Location: venue.php");
    exit;
}

/* =======================
   DATA EDIT (ambil data)
======================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM venue WHERE id_venue=$id"));
}

/* =======================
   AMBIL DATA LIST
======================= */
$data = mysqli_query($conn, "SELECT * FROM venue");
?>

<h2>Data Venue</h2>

<!-- =======================
     FORM TAMBAH / EDIT
======================= -->
<h3><?= $edit ? "Edit Venue" : "Tambah Venue" ?></h3>

<form method="POST">
    <?php if ($edit) { ?>
        <input type="hidden" name="id" value="<?= $edit['id_venue'] ?>">
    <?php } ?>

    Nama Venue: <input type="text" name="nama_venue" value="<?= $edit['nama_venue'] ?? '' ?>"><br>
    Alamat: <textarea name="alamat"><?= $edit['alamat'] ?? '' ?></textarea><br>
    Kapasitas: <input type="number" name="kapasitas" value="<?= $edit['kapasitas'] ?? '' ?>"><br>

    <?php if ($edit) { ?>
        <button type="submit" name="update">Update</button>
        <a href="venue.php">Batal</a>
    <?php } else { ?>
        <button type="submit" name="simpan">Simpan</button>
    <?php } ?>
</form>

<hr>

<!-- =======================
     TABEL DATA
======================= -->
<table border="1" cellpadding="5">
<tr>
    <th>No</th>
    <th>Nama Venue</th>
    <th>Alamat</th>
    <th>Kapasitas</th>
    <th>Aksi</th>
</tr>

<?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_venue'] ?></td>
    <td><?= $row['alamat'] ?></td>
    <td><?= $row['kapasitas'] ?></td>
    <td>
        <a href="venue.php?edit=<?= $row['id_venue'] ?>">Edit</a>
        <a href="venue.php?hapus=<?= $row['id_venue'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
    </td>
</tr>
<?php } ?>

</table>