<?php
session_start();
include '../config/koneksi.php';

// Set default session jika belum ada
if(!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'Administrator';
    $_SESSION['nama'] = 'Admin';
}

// =======================
// AMBIL DATA STATISTIK REAL DARI DATABASE
// =======================

// Total Event
$query_event = mysqli_query($conn, "SELECT COUNT(*) as total FROM event");
$total_event = mysqli_fetch_assoc($query_event)['total'];

// Total Tiket (jumlah semua tiket dari semua event)
$query_tiket = mysqli_query($conn, "SELECT COUNT(*) as total FROM tiket");
$total_tiket = mysqli_fetch_assoc($query_tiket)['total'];

// Total Kuota Tiket (kapasitas total tiket yang tersedia)
$query_kuota_tiket = mysqli_query($conn, "SELECT SUM(kuota) as total_kuota FROM tiket");
$total_kuota_tiket = mysqli_fetch_assoc($query_kuota_tiket)['total_kuota'] ?? 0;

// Total Venue
$query_venue = mysqli_query($conn, "SELECT COUNT(*) as total FROM venue");
$total_venue = mysqli_fetch_assoc($query_venue)['total'];

// Total Voucher Aktif
$query_voucher_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM voucher WHERE status = 'aktif'");
$total_voucher_aktif = mysqli_fetch_assoc($query_voucher_aktif)['total'];

// Total Voucher (semua)
$query_voucher = mysqli_query($conn, "SELECT COUNT(*) as total FROM voucher");
$total_voucher = mysqli_fetch_assoc($query_voucher)['total'];

// Total Kapasitas Venue (akumulasi semua venue)
$query_kapasitas_venue = mysqli_query($conn, "SELECT SUM(kapasitas) as total_kapasitas FROM venue");
$total_kapasitas_venue = mysqli_fetch_assoc($query_kapasitas_venue)['total_kapasitas'] ?? 0;

// =======================
// AMBIL DATA EVENT TERBARU (5 event terakhir)
// =======================
$query_event_terbaru = mysqli_query($conn, "
    SELECT event.*, venue.nama_venue 
    FROM event 
    JOIN venue ON event.id_venue = venue.id_venue 
    ORDER BY event.tanggal DESC 
    LIMIT 5
");

// =======================
// AMBIL DATA TIKET TERBARU
// =======================
$query_tiket_terbaru = mysqli_query($conn, "
    SELECT tiket.*, event.nama_event 
    FROM tiket 
    JOIN event ON tiket.id_event = event.id_event 
    ORDER BY tiket.id_tiket DESC 
    LIMIT 5
");

// =======================
// HITUNG PENDAPATAN POTENSIAL (jika semua tiket terjual)
// =======================
$query_pendapatan = mysqli_query($conn, "
    SELECT SUM(tiket.harga * tiket.kuota) as total_pendapatan 
    FROM tiket
");
$total_pendapatan = mysqli_fetch_assoc($query_pendapatan)['total_pendapatan'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Event Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#0a2540',
                        'accent-blue': '#0066cc',
                        'soft-blue': '#e6f0fa',
                        'admin-bg': '#f8fafc',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-in',
                        'slide-up': 'slideUp 0.3s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,102,204,0.1);
        }
        .menu-card {
            transition: all 0.2s ease;
        }
        .menu-card:hover {
            transform: scale(1.02);
            background: linear-gradient(135deg, #0066cc 0%, #0a2540 100%);
            color: white;
        }
        .menu-card:hover i, .menu-card:hover h3, .menu-card:hover p, .menu-card:hover div {
            color: white;
        }
    </style>
</head>
<body class="bg-admin-bg">
    
    <!-- Top Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-crown text-accent-blue text-2xl"></i>
                <span class="font-bold text-xl bg-gradient-to-r from-navy to-accent-blue bg-clip-text text-transparent">Admin Panel</span>
                <span class="hidden md:inline text-xs bg-soft-blue text-accent-blue px-2 py-1 rounded-full">Event Ticket System</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-circle text-accent-blue text-xl"></i>
                    <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                    <span class="text-xs bg-soft-blue text-accent-blue px-2 py-1 rounded-full"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                </div>
                <a href="../auth/logout.php" class="text-gray-600 hover:text-red-500 transition"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-6">
        <!-- Welcome Section -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 animate-[fadeIn_0.5s_ease-in]">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard Administrator</h1>
                    <p class="text-gray-500 mt-1">Selamat datang kembali! Kelola event, tiket, venue, dan voucher dengan mudah</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-soft-blue rounded-lg px-4 py-2">
                        <i class="fas fa-clock text-accent-blue mr-1"></i>
                        <span class="text-sm text-gray-600"><?php echo date('l, d F Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4 Menu Utama: Event, Tiket, Venue, Voucher -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Event -->
            <a href="event.php" style="text-decoration:none;">
                <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
                    <i class="fas fa-calendar-alt text-4xl text-accent-blue mb-3"></i>
                    <h3 class="font-bold text-lg text-gray-800">Event</h3>
                    <p class="text-gray-500 text-sm mt-1">Kelola daftar event</p>
                    <div class="mt-3 text-accent-blue text-sm font-semibold">
                        <?= $total_event ?> Event →
                    </div>
                </div>
            </a>

            <!-- Tiket -->
            <a href="tiket.php" style="text-decoration:none;">
                <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
                    <i class="fas fa-ticket-alt text-4xl text-accent-blue mb-3"></i>
                    <h3 class="font-bold text-lg text-gray-800">Tiket</h3>
                    <p class="text-gray-500 text-sm mt-1">Atur jenis tiket</p>
                    <div class="mt-3 text-accent-blue text-sm font-semibold">
                        <?= $total_tiket ?> Tiket →
                    </div>
                </div>
            </a>

            <!-- Venue -->
            <a href="venue.php" style="text-decoration:none;">
                <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
                    <i class="fas fa-map-marker-alt text-4xl text-accent-blue mb-3"></i>
                    <h3 class="font-bold text-lg text-gray-800">Venue</h3>
                    <p class="text-gray-500 text-sm mt-1">Lokasi & kapasitas</p>
                    <div class="mt-3 text-accent-blue text-sm font-semibold">
                        <?= $total_venue ?> Venue →
                    </div>
                </div>
            </a>

            <!-- Voucher -->
            <a href="voucher.php" style="text-decoration:none;">
                <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
                    <i class="fas fa-gift text-4xl text-accent-blue mb-3"></i>
                    <h3 class="font-bold text-lg text-gray-800">Voucher</h3>
                    <p class="text-gray-500 text-sm mt-1">Promo & diskon</p>
                    <div class="mt-3 text-accent-blue text-sm font-semibold">
                        <?= $total_voucher_aktif ?> Aktif →
                    </div>
                </div>
            </a>
        </div>        
        
        <!-- Statistik Cepat (Data Real) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Total Event</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_event, 0, ',', '.') ?></p>
                        <p class="text-xs text-green-600 mt-1">
                            <i class="fas fa-calendar-check"></i> Event tersedia
                        </p>
                    </div>
                    <i class="fas fa-calendar-alt text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Total Jenis Tiket</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_tiket, 0, ',', '.') ?></p>
                        <p class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-ticket-alt"></i> Kuota: <?= number_format($total_kuota_tiket, 0, ',', '.') ?>
                        </p>
                    </div>
                    <i class="fas fa-ticket-alt text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Total Venue</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_venue, 0, ',', '.') ?></p>
                        <p class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-users"></i> Kapasitas: <?= number_format($total_kapasitas_venue, 0, ',', '.') ?>
                        </p>
                    </div>
                    <i class="fas fa-building text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Voucher</p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($total_voucher_aktif, 0, ',', '.') ?></p>
                        <p class="text-xs text-orange-600 mt-1">
                            <i class="fas fa-percent"></i> Total: <?= $total_voucher ?> voucher
                        </p>
                    </div>
                    <i class="fas fa-percent text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
        </div>
        
        <!-- Pendapatan Potensial Card -->
        <div class="bg-gradient-to-r from-navy to-accent-blue rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-white/80 text-sm">Pendapatan Potensial</p>
                    <p class="text-3xl font-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
                    <p class="text-white/60 text-xs mt-1">Jika semua tiket terjual habis</p>
                </div>
                <i class="fas fa-chart-line text-5xl text-white/30"></i>
            </div>
        </div>
        
        <!-- Tabel Preview Data Terbaru - Event -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-800"><i class="fas fa-calendar-alt mr-2 text-accent-blue"></i> Event Terbaru</h3>
                <a href="event.php" class="text-accent-blue text-sm hover:underline">Kelola Semua <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Venue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        if(mysqli_num_rows($query_event_terbaru) > 0) {
                            while($event = mysqli_fetch_assoc($query_event_terbaru)) {
                                $today = date('Y-m-d');
                                $event_date = $event['tanggal'];
                                if($event_date > $today) {
                                    $status = '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs"><i class="fas fa-check-circle mr-1"></i>Akan Datang</span>';
                                } elseif($event_date == $today) {
                                    $status = '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs"><i class="fas fa-calendar-day mr-1"></i>Hari Ini</span>';
                                } else {
                                    $status = '<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs"><i class="fas fa-check-double mr-1"></i>Selesai</span>';
                                }
                        ?>
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($event['nama_event']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($event['nama_venue']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= date('d M Y', strtotime($event['tanggal'])) ?></td>
                            <td class="px-6 py-4"><?= $status ?></td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                Belum ada data event
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Tiket Terbaru -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-800"><i class="fas fa-ticket-alt mr-2 text-accent-blue"></i> Tiket Terbaru</h3>
                <a href="tiket.php" class="text-accent-blue text-sm hover:underline">Kelola Semua <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kuota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        if(mysqli_num_rows($query_tiket_terbaru) > 0) {
                            while($tiket = mysqli_fetch_assoc($query_tiket_terbaru)) {
                        ?>
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 text-gray-900"><?= htmlspecialchars($tiket['nama_event']) ?></td>
                            <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($tiket['nama_tiket']) ?></td>
                            <td class="px-6 py-4 text-gray-600">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= number_format($tiket['kuota'], 0, ',', '.') ?></td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                Belum ada data tiket
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer Admin -->
        <div class="text-center text-gray-400 text-xs mt-8 py-4">
            <i class="fas fa-shield-alt text-accent-blue"></i> Admin Panel Secure • Event Ticket System v2.0
            <br>
            <span class="text-gray-300">© <?= date('Y') ?> Event Ticket System. All rights reserved.</span>
        </div>
    </div>
    
</body>
</html>