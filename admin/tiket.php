<?php
session_start();
include '../../config/koneksi.php';

// =======================
// TAMBAH TIKET
// =======================
if (isset($_POST['tambah'])) {
    $id_event = $_POST['id_event'];
    $nama = $_POST['nama_tiket'];
    $harga = $_POST['harga'];
    $kuota = $_POST['kuota'];

    mysqli_query($conn, "
        INSERT INTO tiket (id_event, nama_tiket, harga, kuota)
        VALUES ('$id_event','$nama','$harga','$kuota')
    ");

    header("Location: tiket.php");
}

// =======================
// EDIT TIKET
// =======================
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $id_event = $_POST['id_event'];
    $nama = $_POST['nama_tiket'];
    $harga = $_POST['harga'];
    $kuota = $_POST['kuota'];

    mysqli_query($conn, "
        UPDATE tiket 
        SET id_event='$id_event',
            nama_tiket='$nama',
            harga='$harga',
            kuota='$kuota'
        WHERE id_tiket=$id
    ");

    header("Location: tiket.php");
}

// =======================
// HAPUS TIKET
// =======================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket=$id");

    header("Location: tiket.php");
}

// =======================
// AMBIL DATA TIKET
// =======================
$data = mysqli_query($conn, "
    SELECT tiket.*, event.nama_event 
    FROM tiket
    JOIN event ON tiket.id_event = event.id_event
");

// event list
$event = mysqli_query($conn, "SELECT * FROM event");

// =======================
// MODE EDIT
// =======================
$editData = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $editData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM tiket WHERE id_tiket=$id
    "));
}
?>

<h2>Data Tiket</h2>

<!-- ======================= -->
<!-- FORM TAMBAH / EDIT -->
<!-- ======================= -->

<h3><?= $editData ? 'Edit Tiket' : 'Tambah Tiket' ?></h3>

<form method="POST">

    <input type="hidden" name="id" value="<?= $editData['id_tiket'] ?? '' ?>">

    <!-- EVENT -->
    <label>Event</label>
    <select name="id_event" required>
        <option value="">-- Pilih Event --</option>

        <?php 
        mysqli_data_seek($event, 0); // reset pointer
        while($e = mysqli_fetch_assoc($event)) { ?>
            <option value="<?= $e['id_event'] ?>"
                <?= (isset($editData) && $editData['id_event'] == $e['id_event']) ? 'selected' : '' ?>>
                <?= $e['nama_event'] ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <!-- NAMA TIKET -->
    <label>Nama Tiket</label>
    <input type="text" name="nama_tiket"
           value="<?= $editData['nama_tiket'] ?? '' ?>" required>
    <br><br>

    <!-- HARGA -->
    <label>Harga</label>
    <input type="number" name="harga"
           value="<?= $editData['harga'] ?? '' ?>" required>
    <br><br>

    <!-- KUOTA -->
    <label>Kuota</label>
    <input type="number" name="kuota"
           value="<?= $editData['kuota'] ?? '' ?>" required>
    <br><br>

    <?php if ($editData) { ?>
        <button type="submit" name="edit">Update</button>
    <?php } else { ?>
        <button type="submit" name="tambah">Simpan</button>
    <?php } ?>

</form>

<hr>

<!-- ======================= -->
<!-- TABEL DATA -->
<!-- ======================= -->

<table border="1" cellpadding="5">
<tr>
    <th>No</th>
    <th>Event</th>
    <th>Nama Tiket</th>
    <th>Harga</th>
    <th>Kuota</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_event'] ?></td>
    <td><?= $row['nama_tiket'] ?></td>
    <td><?= $row['harga'] ?></td>
    <td><?= $row['kuota'] ?></td>
    <td>
        <a href="?edit=<?= $row['id_tiket'] ?>">Edit</a> |
        <a href="?hapus=<?= $row['id_tiket'] ?>"
           onclick="return confirm('Yakin hapus?')">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>