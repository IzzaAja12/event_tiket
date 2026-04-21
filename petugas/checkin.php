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
            $message = "Kode tiket tidak ditemukan! Tiket mungkin telah dibatalkan oleh pengguna.";
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
            $response['message'] = "Kode tiket tidak ditemukan! Tiket mungkin telah dibatalkan oleh pengguna.";
        }
    } else {
        $response['message'] = "Kode tiket tidak valid!";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Proses Approve Cancel Request (Setujui pembatalan tiket)
if (isset($_POST['approve_cancel'])) {
    $kode_tiket = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    
    // Ambil id_order dari tiket
    $query = mysqli_query($conn, "
        SELECT a.id_detail, o.id_order 
        FROM attendee a
        JOIN order_detail od ON a.id_detail = od.id_detail
        JOIN orders o ON od.id_order = o.id_order
        WHERE a.kode_tiket = '$kode_tiket'
    ");
    $data = mysqli_fetch_assoc($query);
    
    if ($data) {
        // Update status order menjadi 'cancel'
        mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = " . $data['id_order']);
        
        // Hapus tiket dari attendee
        mysqli_query($conn, "DELETE FROM attendee WHERE kode_tiket = '$kode_tiket'");
        
        $_SESSION['success_message'] = "Tiket berhasil dibatalkan!";
    } else {
        $_SESSION['error_message'] = "Gagal membatalkan tiket!";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Proses Reject Cancel Request (Tolak pembatalan tiket)
if (isset($_POST['reject_cancel'])) {
    $kode_tiket = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $alasan_tolak = mysqli_real_escape_string($conn, $_POST['alasan_tolak']);
    
    // Update status cancel request menjadi rejected
    mysqli_query($conn, "
        UPDATE attendee 
        SET cancel_request = 'rejected', 
            cancel_reject_reason = '$alasan_tolak',
            cancel_reject_date = NOW()
        WHERE kode_tiket = '$kode_tiket'
    ");
    
    $_SESSION['info_message'] = "Request pembatalan ditolak!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil statistik check-in (hanya tiket yang masih ada/tidak dibatalkan)
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
// DATA UNTUK REQUEST CANCEL (Menunggu Konfirmasi)
// ========================
$limit_request = 10;
$page_request = isset($_GET['page_request']) ? (int)$_GET['page_request'] : 1;
$offset_request = ($page_request - 1) * $limit_request;
$search_request = isset($_GET['search_request']) ? mysqli_real_escape_string($conn, $_GET['search_request']) : '';

$search_condition_request = "";
if (!empty($search_request)) {
    $search_condition_request = "AND (a.kode_tiket LIKE '%$search_request%' 
                                   OR e.nama_event LIKE '%$search_request%' 
                                   OR u.nama LIKE '%$search_request%'
                                   OR o.no_order LIKE '%$search_request%')";
}

$total_request_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.cancel_request = 'pending' $search_condition_request
");
$total_request_data = mysqli_fetch_assoc($total_request_query)['total'];
$total_pages_request = ceil($total_request_data / $limit_request);

$cancel_requests = mysqli_query($conn, "
    SELECT a.*, 
           e.nama_event,
           u.nama as nama_pembeli,
           o.no_order,
           od.nama_tiket,
           od.harga,
           e.tanggal as event_tanggal
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.cancel_request = 'pending' $search_condition_request
    ORDER BY a.cancel_request_date ASC
    LIMIT $offset_request, $limit_request
");

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

$total_belum_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'belum' AND (a.cancel_request IS NULL OR a.cancel_request != 'pending') $search_condition_belum
");
$total_belum_data = mysqli_fetch_assoc($total_belum_query)['total'];
$total_pages_belum = ceil($total_belum_data / $limit_belum);

$belum_checkins = mysqli_query($conn, "
    SELECT a.*, 
           e.nama_event,
           u.nama as nama_pembeli,
           o.no_order,
           od.nama_tiket,
           od.harga,
           e.tanggal as event_tanggal
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'belum' AND (a.cancel_request IS NULL OR a.cancel_request != 'pending') $search_condition_belum
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

// ========================
// DATA UNTUK RIWAYAT CANCEL (Tiket yang sudah dibatalkan)
// ========================
$limit_cancel = 10;
$page_cancel = isset($_GET['page_cancel']) ? (int)$_GET['page_cancel'] : 1;
$offset_cancel = ($page_cancel - 1) * $limit_cancel;
$search_cancel = isset($_GET['search_cancel']) ? mysqli_real_escape_string($conn, $_GET['search_cancel']) : '';

$search_condition_cancel = "";
if (!empty($search_cancel)) {
    $search_condition_cancel = "AND (o.no_order LIKE '%$search_cancel%' 
                                   OR e.nama_event LIKE '%$search_cancel%' 
                                   OR u.nama LIKE '%$search_cancel%')";
}

$total_cancel_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM orders o
    JOIN event e ON o.id_event = e.id_event
    JOIN users u ON o.id_user = u.id_user
    WHERE o.status = 'cancel' $search_condition_cancel
");
$total_cancel_data = mysqli_fetch_assoc($total_cancel_query)['total'];
$total_pages_cancel = ceil($total_cancel_data / $limit_cancel);

$cancel_orders = mysqli_query($conn, "
    SELECT o.*, e.nama_event, e.tanggal as event_tanggal, u.nama as nama_pembeli, v.nama_venue,
           (SELECT SUM(qty) FROM order_detail WHERE id_order = o.id_order) as total_tiket
    FROM orders o
    JOIN event e ON o.id_event = e.id_event
    JOIN venue v ON e.id_venue = v.id_venue
    JOIN users u ON o.id_user = u.id_user
    WHERE o.status = 'cancel' $search_condition_cancel
    ORDER BY o.tanggal_order DESC
    LIMIT $offset_cancel, $limit_cancel
");

// Fungsi untuk aman
function safe($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Tiket | Petugas | TiketMoo</title>
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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
        .guide-card {
            transition: all 0.3s ease;
        }
        .guide-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0,102,204,0.15);
        }
        .event-expired {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
        }
        .status-cancel {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        .request-pending {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-soft-blue to-white min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-ticket-alt text-accent-blue text-2xl animate-pulse-slow"></i>
                <span class="font-bold text-xl bg-gradient-to-r from-navy to-accent-blue bg-clip-text text-transparent">TiketMoo</span>
                <span class="ml-2 text-xs bg-accent-blue text-white px-2 py-1 rounded-full">Petugas</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-shield text-accent-blue text-xl"></i>
                    <span class="hidden md:inline text-gray-600"><?= safe($_SESSION['nama']) ?></span>
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
            <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm text-blue-700 flex items-start gap-2">
                <i class="fas fa-info-circle mt-0.5"></i>
                <span>Tiket yang <strong>dibatalkan oleh pengguna</strong> akan tercatat di tab <strong>Riwayat Cancel</strong>.</span>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($_SESSION['success_message'])): ?>
        <script>Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= $_SESSION['success_message'] ?>', timer: 3000, showConfirmButton: false });</script>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if(isset($_SESSION['error_message'])): ?>
        <script>Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= $_SESSION['error_message'] ?>', timer: 3000, showConfirmButton: false });</script>
        <?php unset($_SESSION['error_message']); endif; ?>
        
        <?php if(isset($_SESSION['info_message'])): ?>
        <script>Swal.fire({ icon: 'info', title: 'Informasi', text: '<?= $_SESSION['info_message'] ?>', timer: 3000, showConfirmButton: false });</script>
        <?php unset($_SESSION['info_message']); endif; ?>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 animate-[slideIn_0.4s_ease-out]">
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
            <div class="bg-gradient-to-br from-orange-50 to-red-100 rounded-xl p-4 shadow-md cursor-pointer hover:shadow-lg transition" onclick="document.getElementById('tab-request').click()">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-700 text-sm font-medium">Request Cancel</p>
                        <p class="font-bold text-2xl text-orange-800"><?= number_format($total_request_data, 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-clock text-4xl text-orange-600 opacity-50"></i>
                </div>
                <p class="text-orange-600 text-xs mt-1">Menunggu Konfirmasi</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-100 rounded-xl p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-700 text-sm font-medium">Total Tiket Aktif</p>
                        <p class="font-bold text-2xl text-purple-800"><?= number_format($stats_total['total_tiket'], 0, ',', '.') ?></p>
                    </div>
                    <i class="fas fa-ticket-alt text-4xl text-purple-600 opacity-50"></i>
                </div>
                <p class="text-purple-600 text-xs mt-1">Tiket yang belum dibatalkan</p>
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

        <!-- Tabel Data Tiket (Tab) -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden animate-[slideIn_0.7s_ease-out]">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <div class="flex overflow-x-auto">
                    <button id="tab-belum" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (!isset($_GET['tab']) || $_GET['tab'] == 'belum') ? 'tab-active' : 'tab-inactive' ?>" data-tab="belum">
                        <i class="fas fa-clock mr-2"></i> Belum Check-in 
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-yellow-200 text-yellow-800"><?= number_format($stats_total['belum_checkin'], 0, ',', '.') ?></span>
                    </button>
                    <button id="tab-request" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (isset($_GET['tab']) && $_GET['tab'] == 'request') ? 'tab-active' : 'tab-inactive' ?>" data-tab="request">
                        <i class="fas fa-hourglass-half mr-2"></i> Request Cancel
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-orange-200 text-orange-800"><?= number_format($total_request_data, 0, ',', '.') ?></span>
                    </button>
                    <button id="tab-sudah" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (isset($_GET['tab']) && $_GET['tab'] == 'sudah') ? 'tab-active' : 'tab-inactive' ?>" data-tab="sudah">
                        <i class="fas fa-check-circle mr-2"></i> Sudah Check-in
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-green-200 text-green-800"><?= number_format($stats_total['sudah_checkin'], 0, ',', '.') ?></span>
                    </button>
                    <button id="tab-cancel" class="tab-button px-6 py-3 text-sm font-medium transition-all <?= (isset($_GET['tab']) && $_GET['tab'] == 'cancel') ? 'tab-active' : 'tab-inactive' ?>" data-tab="cancel">
                        <i class="fas fa-times-circle mr-2"></i> Riwayat Cancel
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-orange-200 text-orange-800"><?= number_format($total_cancel_data, 0, ',', '.') ?></span>
                    </button>
                </div>
            </div>

            <!-- Tab Content: Belum Check-in -->
            <div id="content-belum" class="tab-content <?= (!isset($_GET['tab']) || $_GET['tab'] == 'belum') ? '' : 'hidden' ?>">
                <div class="p-6">
                    <div class="flex justify-end mb-4">
                        <form method="GET" class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="hidden" name="tab" value="belum">
                            <input type="text" name="search_belum" value="<?= safe($search_belum) ?>" placeholder="Cari kode, event, pembeli..." class="pl-8 pr-3 py-1.5 text-sm border rounded-lg w-56">
                        </form>
                    </div>
                    <?php if (mysqli_num_rows($belum_checkins) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-yellow-50">
                                <tr><th class="px-4 py-2 text-left">No</th><th class="px-4 py-2 text-left">Kode Tiket</th><th class="px-4 py-2 text-left">No. Pesanan</th><th class="px-4 py-2 text-left">Event</th><th class="px-4 py-2 text-left">Tiket</th><th class="px-4 py-2 text-left">Pembeli</th><th class="px-4 py-2 text-center">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset_belum + 1; $today_date = date('Y-m-d'); while($row = mysqli_fetch_assoc($belum_checkins)): $is_expired = strtotime($row['event_tanggal']) < strtotime($today_date); ?>
                                <tr class="border-t hover:bg-yellow-50 <?= $is_expired ? 'event-expired' : '' ?>">
                                    <td class="px-4 py-2"><?= $no++ ?></td>
                                    <td class="px-4 py-2 font-mono text-xs"><?= safe($row['kode_tiket']) ?></td>
                                    <td class="px-4 py-2"><?= safe($row['no_order']) ?></td>
                                    <td class="px-4 py-2 font-medium"><?= safe($row['nama_event']) ?><?= $is_expired ? '<span class="ml-2 text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full"><i class="fas fa-exclamation-circle"></i> Event Lewat</span>' : '' ?></td>
                                    <td class="px-4 py-2"><?= safe($row['nama_tiket']) ?></td>
                                    <td class="px-4 py-2"><?= safe($row['nama_pembeli']) ?></td>
                                    <td class="px-4 py-2 text-center"><?= !$is_expired ? '<button onclick="quickCheckin(\''.safe($row['kode_tiket']).'\', \''.safe($row['nama_event']).'\', \''.safe($row['nama_pembeli']).'\', \''.safe($row['nama_tiket']).'\')" class="bg-green-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-600 transition">Check-in</button>' : '<span class="text-gray-400 text-xs"><i class="fas fa-calendar-times"></i> Event Lewat</span>' ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages_belum > 1): ?>
                    <div class="flex justify-between items-center mt-4 text-sm"><span class="text-gray-500"><?= $offset_belum+1 ?> - <?= min($offset_belum+$limit_belum, $total_belum_data) ?> dari <?= number_format($total_belum_data) ?></span><div class="flex gap-1"><?php if ($page_belum > 1): ?><a href="?tab=belum&page_belum=<?= $page_belum-1 ?>&search_belum=<?= urlencode($search_belum) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">←</a><?php endif; ?><span class="px-3 py-1 bg-yellow-500 text-white rounded-lg"><?= $page_belum ?></span><?php if ($page_belum < $total_pages_belum): ?><a href="?tab=belum&page_belum=<?= $page_belum+1 ?>&search_belum=<?= urlencode($search_belum) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">→</a><?php endif; ?></div></div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-10 text-gray-400">Tidak ada tiket yang belum check-in</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Content: Request Cancel (Menunggu Konfirmasi) -->
            <div id="content-request" class="tab-content <?= (isset($_GET['tab']) && $_GET['tab'] == 'request') ? '' : 'hidden' ?>">
                <div class="p-6">
                    <div class="flex justify-end mb-4">
                        <form method="GET" class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="hidden" name="tab" value="request">
                            <input type="text" name="search_request" value="<?= safe($search_request) ?>" placeholder="Cari kode, event, pembeli..." class="pl-8 pr-3 py-1.5 text-sm border rounded-lg w-56">
                        </form>
                    </div>
                    <?php if (mysqli_num_rows($cancel_requests) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">No</th>
                                    <th class="px-4 py-2 text-left">Kode Tiket</th>
                                    <th class="px-4 py-2 text-left">No. Pesanan</th>
                                    <th class="px-4 py-2 text-left">Event</th>
                                    <th class="px-4 py-2 text-left">Tiket</th>
                                    <th class="px-4 py-2 text-left">Pembeli</th>
                                    <th class="px-4 py-2 text-left">Alasan</th>
                                    <th class="px-4 py-2 text-left">Tgl Request</th>
                                    <th class="px-4 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset_request + 1; while($row = mysqli_fetch_assoc($cancel_requests)): ?>
                                <tr class="border-t hover:bg-orange-50 request-pending">
                                    <td class="px-4 py-2"><?= $no++ ?></td>
                                    <td class="px-4 py-2 font-mono text-xs"><?= safe($row['kode_tiket']) ?></td>
                                    <td class="px-4 py-2"><?= safe($row['no_order']) ?></td>
                                    <td class="px-4 py-2 font-medium"><?= safe($row['nama_event']) ?></td>
                                    <td class="px-4 py-2"><?= safe($row['nama_tiket']) ?></td>
                                    <td class="px-4 py-2"><?= safe($row['nama_pembeli']) ?></td>
                                    <td class="px-4 py-2 max-w-xs">
                                        <p class="text-xs text-gray-600 line-clamp-2"><?= safe($row['cancel_reason']) ?></p>
                                    </td>
                                    <td class="px-4 py-2 text-xs"><?= date('d/m/Y H:i', strtotime($row['cancel_request_date'])) ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="flex gap-2 justify-center">
                                            <button onclick="approveCancel('<?= safe($row['kode_tiket']) ?>')" class="bg-green-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-600 transition">
                                                <i class="fas fa-check"></i> Setuju
                                            </button>
                                            <button onclick="rejectCancel('<?= safe($row['kode_tiket']) ?>')" class="bg-red-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-red-600 transition">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages_request > 1): ?>
                    <div class="flex justify-between items-center mt-4 text-sm"><span class="text-gray-500"><?= $offset_request+1 ?> - <?= min($offset_request+$limit_request, $total_request_data) ?> dari <?= number_format($total_request_data) ?></span><div class="flex gap-1"><?php if ($page_request > 1): ?><a href="?tab=request&page_request=<?= $page_request-1 ?>&search_request=<?= urlencode($search_request) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">←</a><?php endif; ?><span class="px-3 py-1 bg-orange-500 text-white rounded-lg"><?= $page_request ?></span><?php if ($page_request < $total_pages_request): ?><a href="?tab=request&page_request=<?= $page_request+1 ?>&search_request=<?= urlencode($search_request) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">→</a><?php endif; ?></div></div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-10 text-gray-400">Tidak ada request pembatalan tiket</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Content: Sudah Check-in -->
            <div id="content-sudah" class="tab-content p-5 <?= (isset($_GET['tab']) && $_GET['tab'] == 'sudah') ? '' : 'hidden' ?>">
                <div class="flex justify-end mb-4">
                    <form method="GET" class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="hidden" name="tab" value="sudah">
                        <input type="text" name="search_sudah" value="<?= safe($search_sudah) ?>" placeholder="Cari kode, event, pembeli..." class="pl-8 pr-3 py-1.5 text-sm border rounded-lg w-56">
                    </form>
                </div>
                <?php if (mysqli_num_rows($sudah_checkins) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-green-50"><tr><th class="px-4 py-2 text-left">No</th><th class="px-4 py-2 text-left">Waktu</th><th class="px-4 py-2 text-left">Kode Tiket</th><th class="px-4 py-2 text-left">No. Pesanan</th><th class="px-4 py-2 text-left">Event</th><th class="px-4 py-2 text-left">Pembeli</th></tr></thead>
                        <tbody>
                            <?php $no = $offset_sudah + 1; while($row = mysqli_fetch_assoc($sudah_checkins)): ?>
                            <tr class="border-t hover:bg-green-50"><td class="px-4 py-2"><?= $no++ ?></td><td class="px-4 py-2 whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($row['waktu_checkin'])) ?></td><td class="px-4 py-2 font-mono text-xs"><?= safe($row['kode_tiket']) ?></td><td class="px-4 py-2"><?= safe($row['no_order']) ?></td><td class="px-4 py-2 font-medium"><?= safe($row['nama_event']) ?></td><td class="px-4 py-2"><?= safe($row['nama_pembeli']) ?></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages_sudah > 1): ?>
                <div class="flex justify-between items-center mt-4 text-sm"><span class="text-gray-500"><?= $offset_sudah+1 ?> - <?= min($offset_sudah+$limit_sudah, $total_sudah_data) ?> dari <?= number_format($total_sudah_data) ?></span><div class="flex gap-1"><?php if ($page_sudah > 1): ?><a href="?tab=sudah&page_sudah=<?= $page_sudah-1 ?>&search_sudah=<?= urlencode($search_sudah) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">←</a><?php endif; ?><span class="px-3 py-1 bg-green-500 text-white rounded-lg"><?= $page_sudah ?></span><?php if ($page_sudah < $total_pages_sudah): ?><a href="?tab=sudah&page_sudah=<?= $page_sudah+1 ?>&search_sudah=<?= urlencode($search_sudah) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">→</a><?php endif; ?></div></div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-10 text-gray-400">Belum ada data check-in</div>
                <?php endif; ?>
            </div>

            <!-- Tab Content: Riwayat Cancel -->
            <div id="content-cancel" class="tab-content p-5 <?= (isset($_GET['tab']) && $_GET['tab'] == 'cancel') ? '' : 'hidden' ?>">
                <div class="flex justify-end mb-4">
                    <form method="GET" class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="hidden" name="tab" value="cancel">
                        <input type="text" name="search_cancel" value="<?= safe($search_cancel) ?>" placeholder="Cari no. pesanan, event, pembeli..." class="pl-8 pr-3 py-1.5 text-sm border rounded-lg w-56">
                    </form>
                </div>
                <?php if (mysqli_num_rows($cancel_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-orange-50">
                            <tr><th class="px-4 py-2 text-left">No</th><th class="px-4 py-2 text-left">No. Pesanan</th><th class="px-4 py-2 text-left">Tanggal Pesan</th><th class="px-4 py-2 text-left">Event</th><th class="px-4 py-2 text-left">Venue</th><th class="px-4 py-2 text-left">Pembeli</th><th class="px-4 py-2 text-left">Jml Tiket</th><th class="px-4 py-2 text-left">Total</th><th class="px-4 py-2 text-left">Tanggal Event</th></tr>
                        </thead>
                        <tbody>
                            <?php $no = $offset_cancel + 1; while($row = mysqli_fetch_assoc($cancel_orders)): ?>
                            <tr class="border-t hover:bg-orange-50 status-cancel">
                                <td class="px-4 py-2"><?= $no++ ?></td>
                                <td class="px-4 py-2 font-mono text-xs"><?= safe($row['no_order']) ?></td>
                                <td class="px-4 py-2 text-xs"><?= date('d/m/Y H:i', strtotime($row['tanggal_order'])) ?></td>
                                <td class="px-4 py-2 font-medium"><?= safe($row['nama_event']) ?></td>
                                <td class="px-4 py-2 text-xs"><?= safe($row['nama_venue']) ?></td>
                                <td class="px-4 py-2"><?= safe($row['nama_pembeli']) ?></td>
                                <td class="px-4 py-2 text-center"><?= $row['total_tiket'] ?> tiket</td>
                                <td class="px-4 py-2 font-semibold text-red-600">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="px-4 py-2 text-xs"><?= date('d/m/Y', strtotime($row['event_tanggal'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages_cancel > 1): ?>
                <div class="flex justify-between items-center mt-4 text-sm"><span class="text-gray-500"><?= $offset_cancel+1 ?> - <?= min($offset_cancel+$limit_cancel, $total_cancel_data) ?> dari <?= number_format($total_cancel_data) ?></span><div class="flex gap-1"><?php if ($page_cancel > 1): ?><a href="?tab=cancel&page_cancel=<?= $page_cancel-1 ?>&search_cancel=<?= urlencode($search_cancel) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">←</a><?php endif; ?><span class="px-3 py-1 bg-orange-500 text-white rounded-lg"><?= $page_cancel ?></span><?php if ($page_cancel < $total_pages_cancel): ?><a href="?tab=cancel&page_cancel=<?= $page_cancel+1 ?>&search_cancel=<?= urlencode($search_cancel) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-50">→</a><?php endif; ?></div></div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-10 text-gray-400">Belum ada riwayat pembatalan tiket</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Approve/Reject Cancel -->
        <form id="approveForm" method="POST" action="" class="hidden">
            <input type="hidden" name="approve_cancel" value="1">
            <input type="hidden" name="kode_tiket" id="approve_kode">
        </form>

        <form id="rejectForm" method="POST" action="" class="hidden">
            <input type="hidden" name="reject_cancel" value="1">
            <input type="hidden" name="kode_tiket" id="reject_kode">
            <input type="hidden" name="alasan_tolak" id="reject_alasan">
        </form>

        <!-- Panduan Penggunaan -->
        <div class="mt-10 animate-[slideIn_0.8s_ease-out]">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1 h-8 bg-gradient-to-b from-accent-blue to-navy rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-info-circle text-accent-blue"></i>
                    Panduan Penggunaan
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="guide-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100">
                    <div class="flex items-center gap-3 mb-3"><div class="w-12 h-12 bg-gradient-to-r from-accent-blue to-navy rounded-xl flex items-center justify-center"><i class="fas fa-camera text-white text-xl"></i></div><div><h4 class="font-bold text-gray-800">Scan QR Code</h4><p class="text-xs text-gray-500">Metode Cepat</p></div></div>
                    <p class="text-sm text-gray-600">Arahkan kamera ke QR Code tiket untuk check-in instan.</p>
                </div>
                <div class="guide-card bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-5 border border-yellow-100">
                    <div class="flex items-center gap-3 mb-3"><div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center"><i class="fas fa-bolt text-white text-xl"></i></div><div><h4 class="font-bold text-gray-800">Check-in Cepat</h4><p class="text-xs text-gray-500">Tombol Aksi</p></div></div>
                    <p class="text-sm text-gray-600">Klik tombol Check-in di tabel untuk proses cepat.</p>
                </div>
                <div class="guide-card bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl p-5 border border-orange-100">
                    <div class="flex items-center gap-3 mb-3"><div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center"><i class="fas fa-hourglass-half text-white text-xl"></i></div><div><h4 class="font-bold text-gray-800">Request Cancel</h4><p class="text-xs text-gray-500">Konfirmasi Pembatalan</p></div></div>
                    <p class="text-sm text-gray-600">Setujui atau tolak request pembatalan tiket dari pengguna.</p>
                </div>
                <div class="guide-card bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl p-5 border border-red-100">
                    <div class="flex items-center gap-3 mb-3"><div class="w-12 h-12 bg-gradient-to-r from-red-500 to-rose-500 rounded-xl flex items-center justify-center"><i class="fas fa-trash-alt text-white text-xl"></i></div><div><h4 class="font-bold text-gray-800">Riwayat Cancel</h4><p class="text-xs text-gray-500">Data Pembatalan</p></div></div>
                    <p class="text-sm text-gray-600">Lihat semua pesanan yang telah dibatalkan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Custom untuk Notifikasi -->
    <div id="notificationModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 transform transition-all duration-300" id="modalContent"></div>
    </div>

    <script>
        let html5QrCode = null, isScanning = false;
        
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', function() {
                let tabId = this.dataset.tab;
                document.querySelectorAll('.tab-button').forEach(b => { b.classList.remove('tab-active'); b.classList.add('tab-inactive'); });
                this.classList.remove('tab-inactive'); this.classList.add('tab-active');
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById(`content-${tabId}`).classList.remove('hidden');
                let url = new URL(window.location.href);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
            });
        });
        
        // Search debounce
        let searchTimeoutBelum, searchTimeoutSudah, searchTimeoutCancel, searchTimeoutRequest;
        const searchBelum = document.getElementById('searchBelum');
        const searchSudah = document.getElementById('searchSudah');
        const searchCancel = document.getElementById('searchCancel');
        const searchRequest = document.getElementById('searchRequest');
        
        if (searchBelum) searchBelum.addEventListener('keyup', () => { clearTimeout(searchTimeoutBelum); searchTimeoutBelum = setTimeout(() => document.getElementById('searchFormBelum').submit(), 500); });
        if (searchSudah) searchSudah.addEventListener('keyup', () => { clearTimeout(searchTimeoutSudah); searchTimeoutSudah = setTimeout(() => document.getElementById('searchFormSudah').submit(), 500); });
        if (searchCancel) searchCancel.addEventListener('keyup', () => { clearTimeout(searchTimeoutCancel); searchTimeoutCancel = setTimeout(() => document.getElementById('searchFormCancel').submit(), 500); });
        if (searchRequest) searchRequest.addEventListener('keyup', () => { clearTimeout(searchTimeoutRequest); searchTimeoutRequest = setTimeout(() => document.getElementById('searchFormRequest').submit(), 500); });
        
        function showModal(type, title, message, data = null) {
            let modal = document.getElementById('notificationModal'), modalContent = document.getElementById('modalContent');
            let iconHtml = '', bgGradient = '', btnColor = '';
            if (type === 'success') { iconHtml = '<div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-check-circle text-4xl text-green-500"></i></div>'; bgGradient = 'from-green-500 to-emerald-600'; btnColor = 'bg-green-500 hover:bg-green-600'; }
            else { iconHtml = '<div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center"><i class="fas fa-times-circle text-4xl text-red-500"></i></div>'; bgGradient = 'from-red-500 to-red-600'; btnColor = 'bg-red-500 hover:bg-red-600'; }
            let detailHtml = data ? `<div class="mt-3 p-3 bg-gray-50 rounded-lg text-left text-sm"><p><strong>Kode:</strong> ${data.kode_tiket}</p><p><strong>Event:</strong> ${data.nama_event}</p><p><strong>Pembeli:</strong> ${data.nama_pembeli}</p></div>` : '';
            modalContent.innerHTML = `<div class="rounded-2xl overflow-hidden"><div class="bg-gradient-to-r ${bgGradient} px-5 py-3"><h3 class="text-white font-bold text-center">${title}</h3></div><div class="p-5 text-center">${iconHtml}<p class="text-gray-700 mb-3">${message}</p>${detailHtml}<button onclick="closeModal()" class="${btnColor} text-white px-5 py-2 rounded-lg font-semibold mt-3">OK</button></div></div>`;
            modal.classList.remove('hidden'); modal.classList.add('flex');
        }
        function closeModal() { document.getElementById('notificationModal').classList.add('hidden'); }
        
        function quickCheckin(kode, eventName, pembeli, tiketType) {
            Swal.fire({
                title: 'Konfirmasi Check-in',
                html: `<div class="text-left"><p>Yakin check-in tiket ini?</p><div class="mt-2 p-2 bg-gray-50 rounded"><p><strong>Kode:</strong> ${kode}</p><p><strong>Event:</strong> ${eventName}</p><p><strong>Pembeli:</strong> ${pembeli}</p></div></div>`,
                icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#ef4444', confirmButtonText: 'Ya, Check-in!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(window.location.href, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'checkin_qr=1&kode_qr=' + encodeURIComponent(kode) })
                    .then(res => res.json()).then(data => { showModal(data.success ? 'success' : 'error', data.success ? 'Check-in Berhasil!' : 'Check-in Gagal', data.message, data.data); if(data.success) setTimeout(() => location.reload(), 2000); });
                }
            });
        }
        
        function approveCancel(kodeTiket) {
            Swal.fire({
                title: 'Setujui Pembatalan?',
                html: `Apakah Anda yakin ingin menyetujui pembatalan tiket dengan kode:<br><strong>${kodeTiket}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approve_kode').value = kodeTiket;
                    document.getElementById('approveForm').submit();
                }
            });
        }
        
        function rejectCancel(kodeTiket) {
            Swal.fire({
                title: 'Tolak Pembatalan?',
                html: `<div class="text-left"><p class="mb-2">Apakah Anda yakin ingin menolak pembatalan tiket:</p><div class="bg-gray-50 p-2 rounded mb-2"><p class="text-sm"><strong>Kode:</strong> ${kodeTiket}</p></div><label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan:</label><textarea id="alasanTolak" class="w-full border border-gray-300 rounded-lg p-2 text-sm" rows="3" placeholder="Masukkan alasan penolakan..."></textarea></div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const alasan = document.getElementById('alasanTolak').value;
                    if (!alasan.trim()) {
                        Swal.showValidationMessage('Alasan penolakan harus diisi!');
                        return false;
                    }
                    return { alasan: alasan };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reject_kode').value = kodeTiket;
                    document.getElementById('reject_alasan').value = result.value.alasan;
                    document.getElementById('rejectForm').submit();
                }
            });
        }
        
        function startScanner() {
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, onScanFailure).then(() => { isScanning = true; document.getElementById('stop-scan')?.classList.remove('hidden'); }).catch(err => document.getElementById('qr-message').innerHTML = '<span class="text-red-500 text-sm">Gagal akses kamera</span>');
        }
        function stopScanner() { if (html5QrCode && isScanning) html5QrCode.stop().then(() => { isScanning = false; document.getElementById('stop-scan')?.classList.add('hidden'); }); }
        function onScanSuccess(decodedText) {
            stopScanner();
            fetch(window.location.href, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'checkin_qr=1&kode_qr=' + encodeURIComponent(decodedText) })
            .then(res => res.json()).then(data => { showModal(data.success ? 'success' : 'error', data.success ? 'Check-in Berhasil!' : 'Check-in Gagal', data.message, data.data); setTimeout(() => location.reload(), 2000); });
        }
        function onScanFailure(error) {}
        function showTodayCheckin() { Swal.fire({ title: 'Check-in Hari Ini', html: `<div class="text-5xl font-bold text-green-600"><?= number_format($stats_today['total_checkin']) ?></div><p>pengunjung</p><div class="mt-2 text-gray-500"><?= date('d F Y') ?></div>`, icon: 'success', confirmButtonColor: '#0066cc' }); }
        
        document.getElementById('stop-scan')?.addEventListener('click', stopScanner);
        document.addEventListener('DOMContentLoaded', startScanner);
        document.getElementById('notificationModal')?.addEventListener('click', e => { if(e.target === e.currentTarget) closeModal(); });
        <?php if ($message && $messageType): ?>showModal('<?= $messageType ?>', '<?= $messageType == "success" ? "Check-in Berhasil!" : "Check-in Gagal" ?>', '<?= addslashes($message) ?>', <?= $lastScan ? json_encode($lastScan) : 'null' ?>);<?php endif; ?>
    </script>
</body>
</html>