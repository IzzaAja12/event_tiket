<?php
session_start();
include '../config/koneksi.php';

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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event | Event Ticket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a2540;
            --accent-blue: #0066cc;
            --accent-hover: #005bb5;
            --soft-blue: #e6f0fa;
            --admin-bg: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--admin-bg);
            margin: 0;
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
        }

        .navbar {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .navbar-brand i {
            color: var(--accent-blue);
            font-size: 1.5rem;
        }

        .navbar-brand span {
            background: linear-gradient(to right, var(--navy), var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .navbar-menu a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
            font-weight: 500;
        }
        
        .navbar-menu a:hover {
            color: var(--accent-blue);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            color: var(--text-dark);
        }

        .page-subtitle {
            color: var(--text-muted);
            margin: 0;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            font-weight: 600;
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--navy);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background-color: #f8fafc;
            color: var(--text-dark);
            transition: all 0.2s ease;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
            background-color: var(--white);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--accent-blue);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 102, 204, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 102, 204, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--soft-blue);
            color: var(--accent-blue);
        }
        
        .btn-secondary:hover {
            background-color: #d1e3f8;
        }

        .btn-danger {
            background-color: #fee2e2;
            color: var(--danger);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
        }

        .btn-danger:hover {
            background-color: #fecaca;
        }
        
        .btn-edit {
            background-color: var(--soft-blue);
            color: var(--accent-blue);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
        }
        
        .btn-edit:hover {
            background-color: #d1e3f8;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 600px;
        }

        .table th {
            background-color: #f1f5f9;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: var(--soft-blue);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .action-cell {
            display: flex;
            gap: 0.5rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #e0e7ff;
            color: #4338ca;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-crown"></i>
            <span>Admin Panel</span>
        </div>
        <div class="navbar-menu">
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Manajemen Event</h1>
                <p class="page-subtitle">Kelola dan atur jadwal acara serta lokasi venue</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas <?= $editData ? 'fa-edit' : 'fa-plus-circle' ?>"></i> 
                    <?= $editData ? 'Edit Data Event' : 'Tambah Event Baru' ?>
                </h3>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?= $editData['id_event'] ?? '' ?>">

                <div class="form-group">
                    <label class="form-label" for="nama_event">Nama Event</label>
                    <input type="text" id="nama_event" name="nama_event" class="form-control" placeholder="Masukkan nama event..." required
                           value="<?= $editData['nama_event'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="tanggal">Tanggal Pelaksanaan</label>
                    <input type="date" id="tanggal" name="tanggal" class="form-control" required
                           value="<?= $editData['tanggal'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_venue">Venue Lokasi</label>
                    <div style="position: relative;">
                        <select id="id_venue" name="id_venue" class="form-control" required style="appearance: none;">
                            <option value="" disabled <?= !$editData ? 'selected' : '' ?>>Pilih Venue...</option>
                            <?php while($v = mysqli_fetch_assoc($venue)) { ?>
                                <option value="<?= $v['id_venue'] ?>"
                                    <?= (isset($editData) && $editData['id_venue'] == $v['id_venue']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v['nama_venue']) ?>
                                </option>
                            <?php } ?>
                        </select>
                        <i class="fas fa-chevron-down" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;"></i>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <?php if ($editData) { ?>
                        <button type="submit" name="edit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="event.php" class="btn btn-secondary">Batal</a>
                    <?php } else { ?>
                        <button type="submit" name="tambah" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Event
                        </button>
                    <?php } ?>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-list-ul"></i> Daftar Event
                </h3>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Event</th>
                            <th width="20%">Tanggal</th>
                            <th width="25%">Venue</th>
                            <th width="15%" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no=1; 
                        if(mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)) { 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td style="font-weight: 500; color: var(--navy);"><?= htmlspecialchars($row['nama_event']) ?></td>
                            <td>
                                <div><i class="far fa-calendar-alt" style="color: var(--accent-blue); margin-right: 0.5rem;"></i> <?= htmlspecialchars($row['tanggal']) ?></div>
                            </td>
                            <td>
                                <span class="badge"><i class="fas fa-map-marker-alt" style="margin-right: 0.25rem;"></i> <?= htmlspecialchars($row['nama_venue']) ?></span>
                            </td>
                            <td>
                                <div class="action-cell" style="justify-content: center;">
                                    <a href="?edit=<?= $row['id_event'] ?>" class="btn btn-edit" title="Edit Event">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?hapus=<?= $row['id_event'] ?>" class="btn btn-danger" title="Hapus Event"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus event ini? Data yang dihapus tidak dapat dikembalikan.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                                Belum ada data event.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>