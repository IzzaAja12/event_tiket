<?php
session_start();
include '../../config/koneksi.php';

// =======================
// PROSES TAMBAH
// =======================
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_event'];
    $tanggal = $_POST['tanggal'];
    $id_venue = $_POST['id_venue'];

    mysqli_query($conn, "
        INSERT INTO event (nama_event, tanggal, id_venue)
        VALUES ('$nama','$tanggal','$id_venue')
    ");
    header("Location: event.php");
}

// =======================
// PROSES EDIT
// =======================
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_event'];
    $tanggal = $_POST['tanggal'];
    $id_venue = $_POST['id_venue'];

    mysqli_query($conn, "
        UPDATE event 
        SET nama_event='$nama', tanggal='$tanggal', id_venue='$id_venue'
        WHERE id_event=$id
    ");
    header("Location: event.php");
}

// =======================
// PROSES HAPUS
// =======================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM event WHERE id_event=$id");
    header("Location: event.php");
}

// =======================
// AMBIL DATA
// =======================
$data = mysqli_query($conn, "
    SELECT event.*, venue.nama_venue 
    FROM event
    JOIN venue ON event.id_venue = venue.id_venue
");

$venue = mysqli_query($conn, "SELECT * FROM venue");

// =======================
// MODE EDIT
// =======================
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $editData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM event WHERE id_event=$id
    "));
}
?>

<h2>Data Event</h2>

<!-- FORM TAMBAH / EDIT -->
<h3><?= $editData ? 'Edit Event' : 'Tambah Event' ?></h3>

<form method="POST">
    <input type="hidden" name="id" value="<?= $editData['id_event'] ?? '' ?>">

    Nama:
    <input type="text" name="nama_event"
           value="<?= $editData['nama_event'] ?? '' ?>"><br>

    Tanggal:
    <input type="date" name="tanggal"
           value="<?= $editData['tanggal'] ?? '' ?>"><br>

    Venue:
    <select name="id_venue">
        <?php while($v = mysqli_fetch_assoc($venue)) { ?>
            <option value="<?= $v['id_venue'] ?>"
                <?= (isset($editData) && $editData['id_venue'] == $v['id_venue']) ? 'selected' : '' ?>>
                <?= $v['nama_venue'] ?>
            </option>
        <?php } ?>
    </select><br>

    <?php if ($editData) { ?>
        <button type="submit" name="edit">Update</button>
    <?php } else { ?>
        <button type="submit" name="tambah">Simpan</button>
    <?php } ?>
</form>

<hr>

<!-- TABEL DATA -->
<table border="1">
<tr>
    <th>No</th>
    <th>Nama Event</th>
    <th>Tanggal</th>
    <th>Venue</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_event'] ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= $row['nama_venue'] ?></td>
    <td>
        <a href="?edit=<?= $row['id_event'] ?>">Edit</a>
        <a href="?hapus=<?= $row['id_event'] ?>" 
           onclick="return confirm('Yakin hapus?')">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>