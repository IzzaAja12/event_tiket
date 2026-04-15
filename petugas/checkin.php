<?php
session_start();
include '../config/koneksi.php';

// proteksi role petugas
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

// Set default session
if(!isset($_SESSION['nama'])) {
    $_SESSION['nama'] = 'Petugas';
}

// Proses Check-in Manual
$message = '';
$messageType = '';
$lastScan = null;

if (isset($_POST['checkin_manual'])) {
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode_manual']));
    
    if (!empty($kode)) {
        // Ambil data tiket lengkap dengan informasi event
        $query = mysqli_query($conn, "
            SELECT a.*, 
                   od.nama_tiket, 
                   od.harga,
                   o.no_order,
                   e.nama_event, 
                   e.tanggal as event_tanggal,
                   v.nama_venue,
                   u.nama as nama_pembeli
            FROM attendee a
            JOIN order_detail od ON a.id_detail = od.id_detail
            JOIN orders o ON od.id_order = o.id_order
            JOIN event e ON o.id_event = e.id_event
            JOIN venue v ON e.id_venue = v.id_venue
            JOIN users u ON o.id_user = u.id_user
            WHERE a.kode_tiket = '$kode'
        ");
        
        $data = mysqli_fetch_assoc($query);
        
        if ($data) {
            $lastScan = $data;
            
            if ($data['status_checkin'] == 'belum') {
                mysqli_query($conn, "
                    UPDATE attendee 
                    SET status_checkin = 'sudah', 
                        waktu_checkin = NOW()
                    WHERE kode_tiket = '$kode'
                ");
                
                $message = "Check-in berhasil! Selamat datang di " . $data['nama_event'];
                $messageType = "success";
                
                // Refresh data setelah update
                $query = mysqli_query($conn, "
                    SELECT a.*, 
                           od.nama_tiket, 
                           od.harga,
                           o.no_order,
                           e.nama_event, 
                           e.tanggal as event_tanggal,
                           v.nama_venue,
                           u.nama as nama_pembeli
                    FROM attendee a
                    JOIN order_detail od ON a.id_detail = od.id_detail
                    JOIN orders o ON od.id_order = o.id_order
                    JOIN event e ON o.id_event = e.id_event
                    JOIN venue v ON e.id_venue = v.id_venue
                    JOIN users u ON o.id_user = u.id_user
                    WHERE a.kode_tiket = '$kode'
                ");
                $lastScan = mysqli_fetch_assoc($query);
                
            } else {
                $waktu_checkin = date('d/m/Y H:i:s', strtotime($data['waktu_checkin']));
                $message = "Tiket sudah digunakan! Check-in sebelumnya pada: " . $waktu_checkin;
                $messageType = "error";
            }
            
        } else {
            $message = "Kode tiket tidak ditemukan! Pastikan kode yang dimasukkan benar.";
            $messageType = "error";
        }
    } else {
        $message = "Silakan masukkan kode tiket terlebih dahulu!";
        $messageType = "error";
    }
}

// Proses Check-in via QR Code (untuk AJAX)
if (isset($_POST['checkin_qr'])) {
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode_qr']));
    $response = ['success' => false, 'message' => '', 'data' => null];
    
    if (!empty($kode)) {
        $query = mysqli_query($conn, "
            SELECT a.*, 
                   od.nama_tiket, 
                   od.harga,
                   o.no_order,
                   e.nama_event, 
                   e.tanggal as event_tanggal,
                   v.nama_venue,
                   u.nama as nama_pembeli
            FROM attendee a
            JOIN order_detail od ON a.id_detail = od.id_detail
            JOIN orders o ON od.id_order = o.id_order
            JOIN event e ON o.id_event = e.id_event
            JOIN venue v ON e.id_venue = v.id_venue
            JOIN users u ON o.id_user = u.id_user
            WHERE a.kode_tiket = '$kode'
        ");
        
        $data = mysqli_fetch_assoc($query);
        
        if ($data) {
            if ($data['status_checkin'] == 'belum') {
                mysqli_query($conn, "
                    UPDATE attendee 
                    SET status_checkin = 'sudah', 
                        waktu_checkin = NOW()
                    WHERE kode_tiket = '$kode'
                ");
                
                $response['success'] = true;
                $response['message'] = "Check-in berhasil! Selamat datang di " . $data['nama_event'];
                $response['data'] = $data;
                
            } else {
                $waktu_checkin = date('d/m/Y H:i:s', strtotime($data['waktu_checkin']));
                $response['message'] = "Tiket sudah digunakan! Check-in sebelumnya pada: " . $waktu_checkin;
                $response['data'] = $data;
            }
        } else {
            $response['message'] = "Kode tiket tidak ditemukan!";
        }
    } else {
        $response['message'] = "Kode tiket tidak valid!";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Ambil statistik check-in
$today = date('Y-m-d');
$stats_today = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total_checkin 
    FROM attendee 
    WHERE status_checkin = 'sudah' 
    AND DATE(waktu_checkin) = '$today'
"));

$stats_total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_tiket,
        SUM(CASE WHEN status_checkin = 'sudah' THEN 1 ELSE 0 END) as sudah_checkin,
        SUM(CASE WHEN status_checkin = 'belum' THEN 1 ELSE 0 END) as belum_checkin
    FROM attendee
"));

// ========================
// PAGINATION UNTUK BELUM CHECK-IN
// ========================
$limit_belum = 10;
$page_belum = isset($_GET['page_belum']) ? (int)$_GET['page_belum'] : 1;
$offset_belum = ($page_belum - 1) * $limit_belum;
$search_belum = isset($_GET['search_belum']) ? mysqli_real_escape_string($conn, $_GET['search_belum']) : '';

$search_condition_belum = "";
if (!empty($search_belum)) {
    $search_condition_belum = "AND (a.kode_tiket LIKE '%$search_belum%' 
                                   OR e.nama_event LIKE '%$search_belum%' 
                                   OR u.nama LIKE '%$search_belum%'
                                   OR o.no_order LIKE '%$search_belum%')";
}

// Total data belum check-in
$total_belum_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'belum' $search_condition_belum
");
$total_belum_data = mysqli_fetch_assoc($total_belum_query)['total'];
$total_pages_belum = ceil($total_belum_data / $limit_belum);

// Ambil data belum check-in dengan pagination
$belum_checkins = mysqli_query($conn, "
    SELECT a.*, 
           e.nama_event,
           u.nama as nama_pembeli,
           o.no_order,
           od.nama_tiket,
           od.harga
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'belum' $search_condition_belum
    ORDER BY a.created_at ASC
    LIMIT $offset_belum, $limit_belum
");

// ========================
// PAGINATION UNTUK SUDAH CHECK-IN
// ========================
$limit_sudah = 10;
$page_sudah = isset($_GET['page_sudah']) ? (int)$_GET['page_sudah'] : 1;
$offset_sudah = ($page_sudah - 1) * $limit_sudah;
$search_sudah = isset($_GET['search_sudah']) ? mysqli_real_escape_string($conn, $_GET['search_sudah']) : '';

$search_condition_sudah = "";
if (!empty($search_sudah)) {
    $search_condition_sudah = "AND (a.kode_tiket LIKE '%$search_sudah%' 
                                   OR e.nama_event LIKE '%$search_sudah%' 
                                   OR u.nama LIKE '%$search_sudah%'
                                   OR o.no_order LIKE '%$search_sudah%')";
}

// Total data sudah check-in
$total_sudah_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'sudah' $search_condition_sudah
");
$total_sudah_data = mysqli_fetch_assoc($total_sudah_query)['total'];
$total_pages_sudah = ceil($total_sudah_data / $limit_sudah);

// Ambil data sudah check-in dengan pagination
$sudah_checkins = mysqli_query($conn, "
    SELECT a.*, 
           e.nama_event,
           u.nama as nama_pembeli,
           a.waktu_checkin,
           o.no_order
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'sudah' $search_condition_sudah
    ORDER BY a.waktu_checkin DESC
    LIMIT $offset_sudah, $limit_sudah
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Tiket | Petugas | EventTicket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- HTML5 QR Code Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#0a2540',
                        'accent-blue': '#0066cc',
                        'accent-hover': '#005bb5',
                        'soft-blue': '#e6f0fa',
                        'success': '#10b981',
                        'warning': '#f59e0b',
                        'danger': '#ef4444',
                    },
                    animation: {
                        'slide-in': 'slideIn 0.5s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes confetti {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        #reader video {
            border-radius: 1rem;
            border: 2px solid #0066cc;
        }
        .tab-active {
            background-color: #0066cc;
            color: white;
        }
        .tab-inactive {
            background-color: #e2e8f0;
            color: #64748b;
        }
        .modal-success {
            animation: bounce 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-soft-blue to-white min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-ticket-alt text-accent-blue text-2xl animate-pulse-slow"></i>
                <span class="font-bold text-xl bg-gradient-to-r from-navy to-accent-blue bg-clip-text text-transparent">EventTicket</span>
                <span class="text-xs bg-accent-blue text-white px-2 py-1 rounded-full ml-2">Petugas</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-shield text-accent-blue text-xl"></i>
                    <span class="hidden md:inline text-gray-600"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                </div>
                <a href="../auth/logout.php" class="text-gray-600 hover:text-red-500 transition">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Header -->
        <div class="mb-8 animate-[slideIn_0.3s_ease-out]">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-qrcode text-accent-blue"></i>
                Check-in Tiket
            </h1>
            <p class="text-gray-500 mt-2">Scan QR Code atau masukkan kode tiket untuk check-in pengunjung</p>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 animate-[slideIn_0.4s_ease-out]">
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-4 shadow-md hover:shadow-lg transition cursor-pointer" onclick="showTodayCheckin()">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-700 text-sm font-medium">Check-in Hari Ini</p>
                        <p class="font-bold text-2xl text-green-800"><?= number_format($stats_today['total_checkin'], 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-calendar-check text-4xl text-green-600 opacity-50"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-700 text-sm font-medium">Sudah Check-in</p>
                        <p class="font-bold text-2xl text-blue-800"><?= number_format($stats_total['sudah_checkin'], 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-blue-600 opacity-50"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-orange-100 rounded-xl p-4 shadow-md cursor-pointer hover:shadow-lg transition" onclick="document.getElementById('tab-belum').click()">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-700 text-sm font-medium">Belum Check-in</p>
                        <p class="font-bold text-2xl text-yellow-800"><?= number_format($stats_total['belum_checkin'], 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-clock text-4xl text-yellow-600 opacity-50"></i>
                </div>
                <p class="text-yellow-600 text-xs mt-1">Klik untuk lihat daftar</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-100 rounded-xl p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-700 text-sm font-medium">Total Tiket</p>
                        <p class="font-bold text-2xl text-purple-800"><?= number_format($stats_total['total_tiket'], 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-ticket-alt text-4xl text-purple-600 opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Scan QR Code Section -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 animate-[slideIn_0.5s_ease-out]">
            <div class="bg-gradient-to-r from-navy to-accent-blue px-6 py-4">
                <h2 class="text-white font-semibold">
                    <i class="fas fa-camera mr-2"></i> Scan QR Code Tiket
                </h2>
                <p class="text-blue-100 text-sm mt-1">Arahkan kamera ke QR Code tiket</p>
            </div>
            <div class="p-6">
                <div id="reader"></div>
                <div id="qr-result" class="mt-4 hidden"></div>
                <div id="qr-message" class="mt-4 text-center"></div>
                <button id="stop-scan" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition hidden">
                    <i class="fas fa-stop"></i> Hentikan Scan
                </button>
            </div>
        </div>

        <!-- Input Manual Section -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 animate-[slideIn_0.6s_ease-out]">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                <h2 class="text-white font-semibold">
                    <i class="fas fa-keyboard mr-2"></i> Input Kode Tiket Manual
                </h2>
                <p class="text-gray-300 text-sm mt-1">Masukkan kode tiket secara manual jika scan tidak berfungsi</p>
            </div>
            <div class="p-6">
                <form method="POST" id="manualForm" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-qrcode absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="kode_manual" 
                                id="kodeManual"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-blue focus:border-transparent text-lg font-mono"
                                placeholder="Contoh: TKT-20260415-0265B8-01"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <button 
                        type="submit" 
                        name="checkin_manual"
                        class="bg-accent-blue text-white px-6 py-3 rounded-lg font-semibold hover:bg-accent-hover transition transform hover:scale-[1.02] flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-check-circle"></i> Check-in
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Data Tiket (Tab) -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden animate-[slideIn_0.7s_ease-out]">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <div class="flex">
                    <button id="tab-belum" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (!isset($_GET['tab']) || $_GET['tab'] == 'belum') ? 'tab-active' : 'tab-inactive' ?>" data-tab="belum">
                        <i class="fas fa-clock mr-2"></i> Belum Check-in 
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-yellow-200 text-yellow-800"><?= number_format($stats_total['belum_checkin'], 0, ',', '.') ?></span>
                    </button>
                    <button id="tab-sudah" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (isset($_GET['tab']) && $_GET['tab'] == 'sudah') ? 'tab-active' : 'tab-inactive' ?>" data-tab="sudah">
                        <i class="fas fa-check-circle mr-2"></i> Sudah Check-in
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-green-200 text-green-800"><?= number_format($stats_total['sudah_checkin'], 0, ',', '.') ?></span>
                    </button>
                </div>
            </div>

            <!-- Tab Content: Belum Check-in -->
            <div id="content-belum" class="tab-content <?= (!isset($_GET['tab']) || $_GET['tab'] == 'belum') ? '' : 'hidden' ?>">
                <div class="p-6">
                    <!-- Search untuk belum check-in -->
                    <div class="mb-4 flex justify-end">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <form method="GET" action="" id="searchFormBelum">
                                <input type="hidden" name="tab" value="belum">
                                <input 
                                    type="text" 
                                    name="search_belum" 
                                    id="searchBelum"
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-blue focus:border-transparent w-64"
                                    placeholder="Cari kode, event, pembeli..."
                                    value="<?= htmlspecialchars($search_belum) ?>"
                                >
                            </form>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($belum_checkins) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-yellow-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Kode Tiket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">No. Pesanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Jenis Tiket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Pembeli</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php 
                                $no_belum = $offset_belum + 1;
                                while($row = mysqli_fetch_assoc($belum_checkins)): 
                                ?>
                                <tr class="hover:bg-yellow-50 transition">
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= $no_belum++ ?></td>
                                    <td class="px-6 py-4">
                                        <code class="text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($row['kode_tiket']) ?></code>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600"><?= htmlspecialchars($row['no_order']) ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($row['nama_event']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['nama_tiket']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                                    <td class="px-6 py-4">
                                        <button onclick="quickCheckin('<?= htmlspecialchars($row['kode_tiket']) ?>', '<?= htmlspecialchars($row['nama_event']) ?>', '<?= htmlspecialchars($row['nama_pembeli']) ?>', '<?= htmlspecialchars($row['nama_tiket']) ?>')" 
                                                class="bg-green-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-green-600 transition">
                                            <i class="fas fa-check-circle"></i> Check-in
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination untuk belum check-in -->
                    <?php if ($total_pages_belum > 1): ?>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?= $offset_belum + 1 ?> - <?= min($offset_belum + $limit_belum, $total_belum_data) ?> dari <?= number_format($total_belum_data, 0, ',', '.') ?> data
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page_belum > 1): ?>
                            <a href="?tab=belum&page_belum=<?= $page_belum - 1 ?>&search_belum=<?= urlencode($search_belum) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                            <?php endif; ?>
                            
                            <span class="px-3 py-1 bg-yellow-500 text-white rounded-lg">
                                Halaman <?= $page_belum ?> dari <?= $total_pages_belum ?>
                            </span>
                            
                            <?php if ($page_belum < $total_pages_belum): ?>
                            <a href="?tab=belum&page_belum=<?= $page_belum + 1 ?>&search_belum=<?= urlencode($search_belum) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-check-circle text-5xl text-green-300 mb-3"></i>
                        <p class="text-gray-500">Tidak ada tiket yang belum check-in</p>
                        <p class="text-gray-400 text-sm mt-1">Semua tiket sudah di-check-in!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Content: Sudah Check-in -->
            <div id="content-sudah" class="tab-content <?= (isset($_GET['tab']) && $_GET['tab'] == 'sudah') ? '' : 'hidden' ?>">
                <div class="p-6">
                    <!-- Search untuk sudah check-in -->
                    <div class="mb-4 flex justify-end">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <form method="GET" action="" id="searchFormSudah">
                                <input type="hidden" name="tab" value="sudah">
                                <input 
                                    type="text" 
                                    name="search_sudah" 
                                    id="searchSudah"
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-blue focus:border-transparent w-64"
                                    placeholder="Cari kode, event, pembeli..."
                                    value="<?= htmlspecialchars($search_sudah) ?>"
                                >
                            </form>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($sudah_checkins) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Waktu Check-in</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Kode Tiket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">No. Pesanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Pembeli</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php 
                                $no_sudah = $offset_sudah + 1;
                                while($row = mysqli_fetch_assoc($sudah_checkins)): 
                                ?>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= $no_sudah++ ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($row['waktu_checkin'])) ?></td>
                                    <td class="px-6 py-4">
                                        <code class="text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($row['kode_tiket']) ?></code>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600"><?= htmlspecialchars($row['no_order']) ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($row['nama_event']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination untuk sudah check-in -->
                    <?php if ($total_pages_sudah > 1): ?>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?= $offset_sudah + 1 ?> - <?= min($offset_sudah + $limit_sudah, $total_sudah_data) ?> dari <?= number_format($total_sudah_data, 0, ',', '.') ?> data
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page_sudah > 1): ?>
                            <a href="?tab=sudah&page_sudah=<?= $page_sudah - 1 ?>&search_sudah=<?= urlencode($search_sudah) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                            <?php endif; ?>
                            
                            <span class="px-3 py-1 bg-green-500 text-white rounded-lg">
                                Halaman <?= $page_sudah ?> dari <?= $total_pages_sudah ?>
                            </span>
                            
                            <?php if ($page_sudah < $total_pages_sudah): ?>
                            <a href="?tab=sudah&page_sudah=<?= $page_sudah + 1 ?>&search_sudah=<?= urlencode($search_sudah) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada data check-in</p>
                        <p class="text-gray-400 text-sm mt-1">Lakukan check-in tiket untuk melihat riwayat</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panduan Penggunaan -->
        <div class="mt-8 bg-blue-50 rounded-xl p-4 animate-[slideIn_0.8s_ease-out]">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-accent-blue text-xl mt-0.5"></i>
                <div>
                    <p class="font-semibold text-gray-700">Panduan Penggunaan:</p>
                    <ul class="text-sm text-gray-600 mt-1 space-y-1">
                        <li>• <i class="fas fa-camera"></i> <strong>Scan QR Code:</strong> Arahkan kamera ke QR Code tiket untuk check-in cepat</li>
                        <li>• <i class="fas fa-keyboard"></i> <strong>Input Manual:</strong> Masukkan kode tiket jika scan tidak berfungsi</li>
                        <li>• <i class="fas fa-clock"></i> <strong>Belum Check-in:</strong> Lihat semua tiket yang belum di-check-in di tab ini</li>
                        <li>• <i class="fas fa-check-circle"></i> <strong>Sudah Check-in:</strong> Lihat riwayat check-in yang sudah dilakukan</li>
                        <li>• <i class="fas fa-search"></i> Gunakan fitur search untuk mencari tiket tertentu</li>
                        <li>• <i class="fas fa-bolt"></i> Klik tombol <strong>Check-in</strong> di tabel untuk check-in cepat tanpa scan ulang</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Custom untuk Notifikasi -->
    <div id="notificationModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <!-- Content akan diisi JavaScript -->
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isScanning = false;
        
        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
                
                tabButtons.forEach(btn => {
                    btn.classList.remove('tab-active');
                    btn.classList.add('tab-inactive');
                });
                button.classList.remove('tab-inactive');
                button.classList.add('tab-active');
                
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(`content-${tabId}`).classList.remove('hidden');
            });
        });
        
        // Fungsi show modal modern
        function showModal(type, title, message, data = null) {
            const modal = document.getElementById('notificationModal');
            const modalContent = document.getElementById('modalContent');
            
            let iconHtml = '';
            let bgGradient = '';
            let buttonColor = '';
            
            if (type === 'success') {
                iconHtml = `
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center animate-bounce">
                        <i class="fas fa-check-circle text-5xl text-green-500"></i>
                    </div>
                `;
                bgGradient = 'from-green-500 to-emerald-600';
                buttonColor = 'bg-green-500 hover:bg-green-600';
            } else if (type === 'error') {
                iconHtml = `
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center animate-pulse">
                        <i class="fas fa-times-circle text-5xl text-red-500"></i>
                    </div>
                `;
                bgGradient = 'from-red-500 to-red-600';
                buttonColor = 'bg-red-500 hover:bg-red-600';
            } else if (type === 'warning') {
                iconHtml = `
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-5xl text-yellow-500"></i>
                    </div>
                `;
                bgGradient = 'from-yellow-500 to-orange-600';
                buttonColor = 'bg-yellow-500 hover:bg-yellow-600';
            }
            
            let detailHtml = '';
            if (data) {
                detailHtml = `
                    <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs">Kode Tiket</p>
                                <p class="font-mono font-semibold text-gray-800 text-xs break-all">${data.kode_tiket || '-'}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">Event</p>
                                <p class="font-semibold text-gray-800 text-sm">${data.nama_event || '-'}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">Jenis Tiket</p>
                                <p class="font-semibold text-gray-800 text-sm">${data.nama_tiket || '-'}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">Pembeli</p>
                                <p class="font-semibold text-gray-800 text-sm">${data.nama_pembeli || '-'}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            modalContent.innerHTML = `
                <div class="rounded-2xl overflow-hidden shadow-2xl">
                    <div class="bg-gradient-to-r ${bgGradient} px-6 py-4">
                        <h3 class="text-white font-bold text-lg text-center">${title}</h3>
                    </div>
                    <div class="p-6 text-center">
                        ${iconHtml}
                        <p class="text-gray-700 mb-4">${message}</p>
                        ${detailHtml}
                        <button onclick="closeModal()" class="${buttonColor} text-white px-6 py-2 rounded-lg font-semibold transition transform hover:scale-105 mt-4">
                            <i class="fas fa-check mr-2"></i> OK
                        </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        function closeModal() {
            const modal = document.getElementById('notificationModal');
            const modalContent = document.getElementById('modalContent');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
        
        // Quick check-in dari tabel dengan modal modern
        function quickCheckin(kode, eventName, pembeli, tiketType) {
            Swal.fire({
                title: 'Konfirmasi Check-in',
                html: `
                    <div class="text-left">
                        <p>Apakah Anda yakin ingin melakukan check-in untuk tiket berikut?</p>
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm"><strong>🎫 Kode Tiket:</strong> <code class="bg-gray-200 px-1 rounded">${kode}</code></p>
                            <p class="text-sm mt-1"><strong>📅 Event:</strong> ${eventName}</p>
                            <p class="text-sm mt-1"><strong>👤 Pembeli:</strong> ${pembeli}</p>
                            <p class="text-sm mt-1"><strong>🏷️ Tiket:</strong> ${tiketType}</p>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                confirmButtonText: '<i class="fas fa-check-circle mr-2"></i> Ya, Check-in!',
                cancelButtonText: '<i class="fas fa-times mr-2"></i> Batal',
                background: '#ffffff',
                backdrop: `rgba(0,0,0,0.4)`,
                customClass: {
                    popup: 'rounded-2xl',
                    title: 'text-xl font-bold',
                    confirmButton: 'px-4 py-2 rounded-lg',
                    cancelButton: 'px-4 py-2 rounded-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'checkin_qr=1&kode_qr=' + encodeURIComponent(kode)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showModal('success', '✅ Check-in Berhasil!', data.message, data.data);
                            setTimeout(() => {
                                location.reload();
                            }, 3000);
                        } else {
                            showModal('error', '❌ Check-in Gagal', data.message, data.data);
                            if (data.data) {
                                setTimeout(() => {
                                    location.reload();
                                }, 3000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal('error', '❌ Error', 'Terjadi kesalahan saat memproses check-in');
                    });
                }
            });
        }
        
        // Inisialisasi QR Scanner
        function startScanner() {
            if (html5QrCode === null) {
                html5QrCode = new Html5Qrcode("reader");
            }
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                isScanning = true;
                document.getElementById('stop-scan').classList.remove('hidden');
            }).catch(err => {
                console.error("Gagal memulai scanner:", err);
                document.getElementById('qr-message').innerHTML = '<div class="text-red-600 p-3 rounded-lg bg-red-50"><i class="fas fa-exclamation-circle"></i> Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.</div>';
            });
        }
        
        function stopScanner() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    document.getElementById('stop-scan').classList.add('hidden');
                    document.getElementById('qr-message').innerHTML = '';
                }).catch(err => {
                    console.error("Gagal menghentikan scanner:", err);
                });
            }
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            stopScanner();
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'checkin_qr=1&kode_qr=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModal('success', '✅ Check-in Berhasil!', data.message, data.data);
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    showModal('error', '❌ Check-in Gagal', data.message, data.data);
                    if (data.data) {
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    } else {
                        setTimeout(() => {
                            startScanner();
                        }, 3000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('error', '❌ Error', 'Terjadi kesalahan saat memproses check-in');
                setTimeout(() => {
                    startScanner();
                }, 3000);
            });
        }
        
        function onScanFailure(error) {
            // Diam saja
        }
        
        function showTodayCheckin() {
            Swal.fire({
                title: '📊 Check-in Hari Ini',
                html: `
                    <div class="text-center">
                        <div class="text-6xl font-bold text-green-600 mb-2"><?= number_format($stats_today['total_checkin'], 0, ',', '.') ?></div>
                        <p class="text-gray-500">pengunjung sudah check-in hari ini</p>
                        <div class="mt-4 p-3 bg-green-50 rounded-lg">
                            <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                            <span class="text-gray-600"><?= date('d F Y') ?></span>
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#0066cc',
                confirmButtonText: 'Tutup',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        }
        
        document.getElementById('stop-scan')?.addEventListener('click', stopScanner);
        
        document.addEventListener('DOMContentLoaded', function() {
            startScanner();
            
            // Tampilkan notifikasi dari PHP jika ada
            <?php if ($message && $messageType): ?>
            showModal('<?= $messageType ?>', '<?= $messageType == "success" ? "✅ Check-in Berhasil!" : "❌ Check-in Gagal" ?>', '<?= addslashes($message) ?>', <?= $lastScan ? json_encode($lastScan) : 'null' ?>);
            <?php endif; ?>
        });
        
        // Search dengan debounce
        let searchTimeoutBelum, searchTimeoutSudah;
        const searchBelum = document.getElementById('searchBelum');
        const searchSudah = document.getElementById('searchSudah');
        
        if (searchBelum) {
            searchBelum.addEventListener('keyup', function() {
                clearTimeout(searchTimeoutBelum);
                searchTimeoutBelum = setTimeout(() => {
                    document.getElementById('searchFormBelum').submit();
                }, 500);
            });
        }
        
        if (searchSudah) {
            searchSudah.addEventListener('keyup', function() {
                clearTimeout(searchTimeoutSudah);
                searchTimeoutSudah = setTimeout(() => {
                    document.getElementById('searchFormSudah').submit();
                }, 500);
            });
        }
        
        // Clear manual input setelah submit
        const manualForm = document.getElementById('manualForm');
        if (manualForm) {
            manualForm.addEventListener('submit', function(e) {
                const kodeManual = document.getElementById('kodeManual').value;
                if (!kodeManual.trim()) {
                    e.preventDefault();
                    showModal('warning', '⚠️ Perhatian', 'Silakan masukkan kode tiket terlebih dahulu!');
                }
            });
        }
        
        // Tutup modal dengan klik di luar
        document.getElementById('notificationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>