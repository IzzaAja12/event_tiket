<?php
session_start();
include '../config/koneksi.php';

// Set default session jika belum ada atau tidak lengkap
if(!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'User';
}

if(!isset($_SESSION['nama'])) {
    $_SESSION['nama'] = 'Pengguna';
}

if(!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1; // Default user ID, sesuaikan dengan sistem login Anda
}

// =======================
// AMBIL DATA EVENT TERBARU DARI DATABASE
// =======================
$query_event = mysqli_query($conn, "
    SELECT event.*, venue.nama_venue, venue.alamat, venue.kapasitas,
           (SELECT COUNT(*) FROM tiket WHERE tiket.id_event = event.id_event) as total_tiket
    FROM event 
    JOIN venue ON event.id_venue = venue.id_venue 
    WHERE event.tanggal >= CURDATE()
    ORDER BY event.tanggal ASC 
    LIMIT 6
");

// Ambil semua event untuk statistik
$query_all_event = mysqli_query($conn, "SELECT COUNT(*) as total FROM event WHERE tanggal >= CURDATE()");
$total_event_available = mysqli_fetch_assoc($query_all_event)['total'] ?? 0;

// =======================
// AMBIL DATA VENUE
// =======================
$query_venue = mysqli_query($conn, "SELECT COUNT(*) as total FROM venue");
$total_venue = mysqli_fetch_assoc($query_venue)['total'] ?? 0;

// =======================
// AMBIL DATA TIKET
$query_total_tiket = mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket");
$total_tiket = mysqli_fetch_assoc($query_total_tiket)['total'] ?? 0;

// =======================
// AMBIL DATA TIKET TERJUAL (simulasi - jika ada tabel pemesanan)
$query_tiket_terjual = mysqli_query($conn, "
    SELECT SUM(kuota) as total_terjual FROM tiket
");
$total_tiket_terjual = mysqli_fetch_assoc($query_tiket_terjual)['total_terjual'] ?? 0;

// =======================
// FUNGSI UNTUK AMAN
function safe($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Event Ticket</title>
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
        .event-card {
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0,102,204,0.2);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-circle text-accent-blue text-xl"></i>
                    <span class="hidden md:inline text-gray-600"><?php echo safe($_SESSION['nama']); ?></span>
                   
                </div>
                <a href="../auth/logout.php" class="text-gray-600 hover:text-accent-blue transition"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-navy to-accent-blue rounded-2xl p-6 text-white mb-8 animate-[slideIn_0.5s_ease-out]">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">Selamat datang, <?php echo safe($_SESSION['nama']); ?>! 👋</h1>
                    <p class="text-blue-100 mt-1">Temukan dan pesan tiket event favorit Anda</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <i class="fas fa-calendar-alt text-5xl opacity-50"></i>
                </div>
            </div>
        </div>
        
        <!-- Grid Event (Fitur Event) -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 border-l-4 border-accent-blue pl-3">
                    <i class="fas fa-music mr-2 text-accent-blue"></i>Event Terbaru
                </h2>
                <a href="events.php" class="text-accent-blue hover:underline text-sm">Lihat semua <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                if($query_event && mysqli_num_rows($query_event) > 0) {
                    while($event = mysqli_fetch_assoc($query_event)) {
                        // Tentukan harga termurah dari tiket event ini
                        $query_harga_termurah = mysqli_query($conn, "
                            SELECT MIN(harga) as harga_termurah FROM tiket WHERE id_event = " . intval($event['id_event'])
                        );
                        $harga_data = mysqli_fetch_assoc($query_harga_termurah);
                        $harga_termurah = $harga_data['harga_termurah'] ?? 0;
                        
                        // Tentukan warna gradasi berdasarkan kategori (random tapi konsisten)
                        $gradients = [
                            'from-blue-400 to-blue-600',
                            'from-green-400 to-teal-500',
                            'from-purple-400 to-pink-500',
                            'from-orange-400 to-red-500',
                            'from-indigo-400 to-purple-600',
                            'from-teal-400 to-cyan-500'
                        ];
                        $gradient_index = ($event['id_event'] ?? 1) % count($gradients);
                        $gradient = $gradients[$gradient_index];
                        
                        // Format harga
                        $harga_text = ($harga_termurah > 0) ? 'Rp ' . number_format($harga_termurah, 0, ',', '.') : 'Gratis';
                        
                        // Foto event
                        $foto_event = (!empty($event['foto'])) ? "../uploads/event/" . $event['foto'] : "https://placehold.co/400x200?text=" . urlencode($event['nama_event'] ?? 'Event');
                        
                        // Deskripsi
                        $deskripsi = $event['deskripsi'] ?? '';
                        $deskripsi_short = strlen($deskripsi) > 100 ? substr($deskripsi, 0, 100) . '...' : $deskripsi;
                        if(empty($deskripsi_short)) {
                            $deskripsi_short = 'Event menarik untuk Anda!';
                        }
                ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-md event-card">
                    <div class="h-40 bg-gradient-to-r <?= $gradient ?> relative">
                        <img src="<?= $foto_event ?>" class="w-full h-full object-cover" alt="<?php echo safe($event['nama_event'] ?? 'Event'); ?>"
                             onerror="this.src='https://placehold.co/400x200?text=' + encodeURIComponent('<?php echo safe($event['nama_event'] ?? 'Event'); ?>')">
                        <div class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 text-xs font-bold text-accent-blue shadow">
                            <i class="fas fa-tag mr-1"></i> <?= $harga_text ?>
                        </div>
                        <?php if(($event['total_tiket'] ?? 0) > 0): ?>
                        <div class="absolute bottom-3 left-3 bg-black/50 rounded-full px-2 py-1 text-xs text-white">
                            <i class="fas fa-ticket-alt mr-1"></i> <?= $event['total_tiket'] ?> tiket
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800"><?php echo safe($event['nama_event'] ?? 'Event'); ?></h3>
                        <div class="flex items-center text-gray-500 text-sm mt-2">
                            <i class="fas fa-calendar mr-2 text-accent-blue"></i> <?= date('d M Y', strtotime($event['tanggal'] ?? 'now')) ?>
                            <i class="fas fa-map-marker-alt ml-3 mr-2 text-accent-blue"></i> <?php echo safe($event['nama_venue'] ?? 'Venue'); ?>
                        </div>
                        <p class="text-gray-600 text-sm mt-3 line-clamp-2">
                            <?php echo safe($deskripsi_short); ?>
                        </p>
                        <a href="detail.php?id=<?= $event['id_event'] ?>" class="mt-4 w-full bg-accent-blue text-white py-2 rounded-lg hover:bg-blue-700 transition transform hover:scale-[1.02] flex items-center justify-center">
                            <i class="fas fa-ticket-alt mr-1"></i> Pesan Tiket
                        </a>
                    </div>
                </div>
                <?php 
                    }
                } else { 
                ?>
                <div class="col-span-3 text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Belum ada event yang tersedia saat ini.</p>
                    <p class="text-gray-400 text-sm mt-2">Silakan cek kembali nanti untuk event terbaru.</p>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Statistik Singkat -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3 hover:shadow-lg transition">
                <i class="fas fa-calendar-check text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Event Tersedia</p>
                    <p class="font-bold text-xl"><?= number_format($total_event_available, 0, ',', '.') ?> Event</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3 hover:shadow-lg transition">
                <i class="fas fa-map-marker-alt text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Total Venue</p>
                    <p class="font-bold text-xl"><?= number_format($total_venue, 0, ',', '.') ?> Venue</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3 hover:shadow-lg transition">
                <i class="fas fa-ticket-alt text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Total Tiket</p>
                    <p class="font-bold text-xl"><?= number_format($total_tiket, 0, ',', '.') ?> Tiket</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-gray-400 text-xs mt-8 pt-8 border-t border-gray-200">
            <i class="fas fa-ticket-alt text-accent-blue"></i> EventTicket System • Temukan event terbaik untuk Anda
            <br>
            <span class="text-gray-300">© <?= date('Y') ?> EventTicket. All rights reserved.</span>
        </div>
    </div>
    
</body>
</html>