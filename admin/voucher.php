<?php
session_start();
include '../../config/koneksi.php';

/* =======================
   DELETE
======================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM voucher WHERE id_voucher=$id");
    header("Location: voucher.php");
    exit;
}

/* =======================
   INSERT
======================= */
if (isset($_POST['simpan'])) {
    $kode = $_POST['kode_voucher'];
    $potongan = $_POST['potongan'];
    $kuota = $_POST['kuota'];
    $status = $_POST['status'];

    mysqli_query($conn, "
        INSERT INTO voucher (kode_voucher, potongan, kuota, status)
        VALUES ('$kode','$potongan','$kuota','$status')
    ");

    header("Location: voucher.php");
    exit;
}

/* =======================
   UPDATE
======================= */
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kode = $_POST['kode_voucher'];
    $potongan = $_POST['potongan'];
    $kuota = $_POST['kuota'];
    $status = $_POST['status'];

    mysqli_query($conn, "
        UPDATE voucher 
        SET kode_voucher='$kode',
            potongan='$potongan',
            kuota='$kuota',
            status='$status'
        WHERE id_voucher=$id
    ");

    header("Location: voucher.php");
    exit;
}

/* =======================
   DATA EDIT
======================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voucher WHERE id_voucher=$id"));
}

/* =======================
   AMBIL DATA
======================= */
$data = mysqli_query($conn, "SELECT * FROM voucher");
?>

<h2>Data Voucher</h2>

<!-- =======================
     FORM TAMBAH / EDIT
======================= -->
<h3><?= $edit ? "Edit Voucher" : "Tambah Voucher" ?></h3>

<form method="POST">
    <?php if ($edit) { ?>
        <input type="hidden" name="id" value="<?= $edit['id_voucher'] ?>">
    <?php } ?>

    Kode:
    <input type="text" name="kode_voucher" value="<?= $edit['kode_voucher'] ?? '' ?>"><br>

    Potongan:
    <input type="number" name="potongan" value="<?= $edit['potongan'] ?? '' ?>"><br>

    Kuota:
    <input type="number" name="kuota" value="<?= $edit['kuota'] ?? '' ?>"><br>

    Status:
    <select name="status">
        <option value="aktif" <?= (isset($edit) && $edit['status']=='aktif') ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= (isset($edit) && $edit['status']=='nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
    </select><br>

    <?php if ($edit) { ?>
        <button type="submit" name="update">Update</button>
        <a href="voucher.php">Batal</a>
    <?php } else { ?>
        <button type="submit" name="simpan">Simpan</button>
    <?php } ?>
</form>

<hr>

<!-- =======================
     TABLE DATA
======================= -->
<table border="1" cellpadding="5">
<tr>
    <th>No</th>
    <th>Kode</th>
    <th>Potongan</th>
    <th>Kuota</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['kode_voucher'] ?></td>
    <td><?= $row['potongan'] ?></td>
    <td><?= $row['kuota'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <a href="voucher.php?edit=<?= $row['id_voucher'] ?>">Edit</a>
        <a href="voucher.php?hapus=<?= $row['id_voucher'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
    </td>
</tr>
<?php } ?>

</table>