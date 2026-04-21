<?php
session_start();
include 'config/koneksi.php';

// Ambil 6 event terbaru dari database (yang akan datang)
$query_events = mysqli_query($conn, "
    SELECT event.*, venue.nama_venue, venue.alamat,
           (SELECT MIN(harga) FROM tiket WHERE tiket.id_event = event.id_event) as harga_termurah
    FROM event 
    JOIN venue ON event.id_venue = venue.id_venue 
    WHERE event.tanggal >= CURDATE()
    ORDER BY event.tanggal ASC 
    LIMIT 6
");

// Ambil total event untuk statistik
$query_total_events = mysqli_query($conn, "SELECT COUNT(*) as total FROM event WHERE tanggal >= CURDATE()");
$total_events = mysqli_fetch_assoc($query_total_events)['total'] ?? 0;

// Ambil total pengguna
$query_users = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = mysqli_fetch_assoc($query_users)['total'] ?? 0;

// Ambil total venue
$query_venues = mysqli_query($conn, "SELECT COUNT(*) as total FROM venue");
$total_venues = mysqli_fetch_assoc($query_venues)['total'] ?? 0;

function safe($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y', $t);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TiketMoo - Platform Tiket Event Online</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'navy': '#0a2540',
                        'accent-blue': '#0066cc',
                        'soft-blue': '#e6f0fa',
                        'dark-blue': '#1e3a5f',
                        'light-blue': '#f0f7ff',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1)',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'gradient': 'gradientShift 8s ease infinite',
                        'spin-slow': 'spin 20s linear infinite',
                        'bounce-slow': 'bounce 3s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite',
                        'shake': 'shake 0.5s ease-in-out',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-20px) rotate(2deg)' },
                        },
                        gradientShift: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        },
                        glow: {
                            '0%, 100%': { boxShadow: '0 0 5px rgba(0,102,204,0.3)' },
                            '50%': { boxShadow: '0 0 20px rgba(0,102,204,0.6)' },
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '25%': { transform: 'translateX(-5px)' },
                            '75%': { transform: 'translateX(5px)' },
                        },
                    },
                    backgroundSize: {
                        '400': '400%',
                    },
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            overflow-x: hidden;
        }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #e6f0fa;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #0066cc, #0a2540);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #0a2540, #0066cc);
        }
        
        /* Glass morphism effect */
        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,102,204,0.1);
            transition: all 0.3s ease;
        }
        
        /* Card hover effects */
        .event-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .event-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 40px -12px rgba(0,102,204,0.25);
        }
        
        /* Button gradient animation */
        .btn-gradient {
            background: linear-gradient(135deg, #0066cc, #0a2540);
            background-size: 200% 200%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient:hover {
            background-position: 100% 100%;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0,102,204,0.4);
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-gradient:hover::before {
            width: 300px;
            height: 300px;
        }
        
        /* Image overlay effect */
        .image-overlay {
            position: relative;
            overflow: hidden;
        }
        
        .image-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,102,204,0.3), rgba(10,37,64,0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .image-overlay:hover::after {
            opacity: 1;
        }
        
        .image-overlay img {
            transition: transform 0.5s ease;
        }
        
        .image-overlay:hover img {
            transform: scale(1.1);
        }
        
        /* Text truncate */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Feature card hover */
        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -12px rgba(0,102,204,0.15);
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-icon {
            transition: all 0.3s ease;
        }
        
        /* Testimonial card */
        .testimonial-card {
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -12px rgba(0,102,204,0.2);
        }
        
        /* Nav link hover */
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #0066cc, #0a2540);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Animated background shapes */
        .animated-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            animation: float 10s ease-in-out infinite;
        }
        
        /* Stats counter animation */
        .stat-number {
            transition: all 0.3s ease;
        }
        
        .stat-item {
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
        }
        
        .stat-item:hover .stat-number {
            transform: scale(1.05);
        }
        
        /* Pulse animation for badges */
        @keyframes pulse-blue {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(0,102,204,0.4);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(0,102,204,0);
            }
        }
        
        .pulse-badge {
            animation: pulse-blue 2s infinite;
        }
    </style>
</head>
<body class="antialiased">

<!-- Animated Background Shapes -->
<div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
    <div class="animated-shape w-96 h-96 bg-accent-blue/20 top-20 -left-48"></div>
    <div class="animated-shape w-96 h-96 bg-navy/20 bottom-20 -right-48" style="animation-delay: 3s;"></div>
    <div class="animated-shape w-64 h-64 bg-accent-blue/30 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" style="animation-delay: 6s;"></div>
</div>

<!-- Navbar -->
<nav class="glass-nav fixed w-full z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-5 py-3 flex justify-between items-center">
        <div class="flex items-center gap-2 group cursor-pointer">
            <div class="w-9 h-9 bg-gradient-to-r from-accent-blue to-navy rounded-xl flex items-center justify-center shadow-md transition-transform group-hover:scale-110 group-hover:rotate-6">
                <i class="fas fa-ticket-alt text-white text-sm"></i>
            </div>
            <span class="font-bold text-xl bg-gradient-to-r from-accent-blue to-navy bg-clip-text text-transparent">TiketMoo</span>
        </div>
        <div class="hidden md:flex items-center gap-6">
            <a href="#home" class="nav-link text-gray-600 hover:text-accent-blue transition text-sm font-medium">Beranda</a>
            <a href="#features" class="nav-link text-gray-600 hover:text-accent-blue transition text-sm font-medium">Fitur</a>
            <a href="#events" class="nav-link text-gray-600 hover:text-accent-blue transition text-sm font-medium">Event</a>
            <a href="#testimonials" class="nav-link text-gray-600 hover:text-accent-blue transition text-sm font-medium">Testimoni</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="auth/login.php" class="text-accent-blue hover:text-navy transition text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-50 relative overflow-hidden group">
                <span class="relative z-10">Masuk</span>
                <span class="absolute inset-0 bg-blue-50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
            </a>
            <a href="auth/register.php" class="btn-gradient text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md">
                <i class="fas fa-user-plus mr-2"></i> Daftar
            </a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="home" class="relative min-h-screen flex items-center overflow-hidden pt-16">
    <div class="max-w-7xl mx-auto px-5 py-16 md:py-24 relative z-10">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-up" data-aos-duration="1000">
                <div class="inline-flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-full mb-5 border border-blue-100 pulse-badge">
                    <i class="fas fa-ticket-alt text-accent-blue text-xs"></i>
                    <span class="text-xs text-accent-blue font-medium">Platform Tiket No.1</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 leading-tight mb-4">
                    Temukan & Pesan<br>
                    <span class="bg-gradient-to-r from-accent-blue to-navy bg-clip-text text-transparent">Tiket Event</span> Favoritmu
                </h1>
                <p class="text-gray-500 text-lg mb-6 leading-relaxed">
                    Nikmati kemudahan memesan tiket berbagai event menarik. 
                    Konser, festival, workshop, dan masih banyak lagi.
                </p>
                <div class="flex gap-4">
                    <a href="auth/register.php" class="btn-gradient text-white px-6 py-3 rounded-xl font-semibold shadow-lg inline-flex items-center gap-2 group">
                        <i class="fas fa-ticket-alt group-hover:rotate-12 transition-transform"></i>
                        Pesan Sekarang
                    </a>
                    <a href="#events" class="border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-accent-blue hover:text-accent-blue transition-all duration-300 hover:shadow-lg inline-flex items-center gap-2 group">
                        <i class="fas fa-play-circle group-hover:scale-110 transition-transform"></i>
                        Lihat Event
                    </a>
                </div>
                <div class="flex items-center gap-6 mt-8 pt-4 border-t border-gray-100">
                    <div class="stat-item cursor-pointer">
                        <p class="text-2xl font-bold bg-gradient-to-r from-accent-blue to-navy bg-clip-text text-transparent stat-number">
                            <?= number_format($total_events) ?>+
                        </p>
                        <p class="text-xs text-gray-500 font-medium mt-1">Event Tersedia</p>
                    </div>
                    <div class="stat-item cursor-pointer">
                        <p class="text-2xl font-bold bg-gradient-to-r from-accent-blue to-navy bg-clip-text text-transparent stat-number">
                            <?= number_format($total_users) ?>+
                        </p>
                        <p class="text-xs text-gray-500 font-medium mt-1">Pengguna Aktif</p>
                    </div>
                    <div class="stat-item cursor-pointer">
                        <p class="text-2xl font-bold bg-gradient-to-r from-accent-blue to-navy bg-clip-text text-transparent stat-number">
                            <?= number_format($total_venues) ?>+
                        </p>
                        <p class="text-xs text-gray-500 font-medium mt-1">Partner Venue</p>
                    </div>
                </div>
            </div>
            <div class="relative animate-float" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <div class="bg-gradient-to-br from-soft-blue to-white rounded-3xl p-6 shadow-xl">
                    <img src="assets/1.jpg" alt="Hero" class="rounded-2xl w-full shadow-lg" onerror="this.src='https://picsum.photos/600/500?random=1'">
                </div>
                <div class="absolute -top-4 -right-4 bg-white rounded-2xl p-3 shadow-lg animate-bounce-slow">
                    <i class="fas fa-check-circle text-accent-blue text-2xl"></i>
                </div>
                <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-3 shadow-lg animate-pulse-slow">
                    <i class="fas fa-shield-alt text-accent-blue text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-16 bg-white relative">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="text-accent-blue text-sm font-semibold uppercase tracking-wide">Keunggulan</span>
            <h2 class="text-3xl font-bold text-gray-800 mt-2">Kenapa Memilih TiketMoo?</h2>
            <p class="text-gray-500 mt-3 max-w-2xl mx-auto">Nikmati pengalaman terbaik dalam memesan tiket event</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="feature-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-bolt text-accent-blue text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Proses Cepat</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Pemesanan tiket hanya dalam hitungan menit. Konfirmasi instan melalui email.</p>
            </div>
            <div class="feature-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-accent-blue text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Aman & Terpercaya</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Sistem keamanan terenkripsi untuk melindungi data dan transaksi Anda.</p>
            </div>
            <div class="feature-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-headset text-accent-blue text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Dukungan 24/7</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Tim customer service siap membantu Anda kapan saja melalui live chat.</p>
            </div>
        </div>
    </div>
</section>

<!-- Events Preview Section -->
<section id="events" class="py-16">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex justify-between items-center mb-10" data-aos="fade-up">
            <div>
                <span class="text-accent-blue text-sm font-semibold uppercase tracking-wide">Event Populer</span>
                <h2 class="text-3xl font-bold text-gray-800 mt-2">Event yang Akan Datang</h2>
            </div>
            <a href="events.php" class="group text-accent-blue hover:text-navy text-sm font-medium inline-flex items-center gap-1">
                Lihat Semua 
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
        
        <?php if($query_events && mysqli_num_rows($query_events) > 0): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $gradients = ['from-blue-500 to-blue-700', 'from-blue-600 to-navy', 'from-cyan-500 to-blue-600', 'from-blue-400 to-blue-700', 'from-indigo-500 to-blue-700', 'from-sky-500 to-blue-700'];
            $i = 0;
            while($event = mysqli_fetch_assoc($query_events)): 
                $foto_event = !empty($event['foto']) ? "uploads/event/" . $event['foto'] : "https://picsum.photos/400/250?random=" . ($event['id_event'] ?? $i);
                $harga = $event['harga_termurah'] ?? 0;
                $harga_text = $harga > 0 ? 'Rp ' . number_format($harga, 0, ',', '.') : 'Gratis';
                $gradient = $gradients[$i % count($gradients)];
            ?>
            <div class="event-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="image-overlay h-48 bg-gradient-to-r <?= $gradient ?> relative overflow-hidden">
                    <img src="<?= $foto_event ?>" class="w-full h-full object-cover" alt="<?= safe($event['nama_event']) ?>" onerror="this.src='https://picsum.photos/400/250?random=<?= $i ?>'">
                    <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm rounded-full px-2.5 py-1 text-xs font-bold text-accent-blue shadow-sm">
                        <i class="fas fa-tag mr-1"></i> <?= $harga_text ?>
                    </div>
                    <div class="absolute bottom-3 left-3 bg-black/50 backdrop-blur-sm rounded-full px-2 py-0.5 text-xs text-white">
                        <i class="fas fa-map-marker-alt mr-1"></i> <?= safe($event['nama_venue']) ?>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 text-gray-500 text-xs mb-2">
                        <i class="fas fa-calendar-alt text-accent-blue"></i>
                        <span><?= formatTanggal($event['tanggal']) ?></span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1 line-clamp-1"><?= safe($event['nama_event']) ?></h3>
                    <p class="text-gray-500 text-sm line-clamp-2"><?= safe($event['deskripsi'] ?: 'Event menarik yang tidak boleh Anda lewatkan!') ?></p>
                    <a href="auth/login.php" class="mt-4 block text-center bg-accent-blue text-white py-2 rounded-lg text-sm font-semibold hover:bg-navy transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                        Pesan Tiket
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
            <?php 
            $i++;
            endwhile; 
            ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12 bg-white rounded-2xl shadow-sm" data-aos="fade-up">
            <i class="fas fa-calendar-times text-5xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Belum ada event yang akan datang.</p>
            <p class="text-gray-400 text-sm mt-1">Silakan cek kembali nanti.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-16 bg-light-blue">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="text-accent-blue text-sm font-semibold uppercase tracking-wide">Testimoni</span>
            <h2 class="text-3xl font-bold text-gray-800 mt-2">Apa Kata Mereka?</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="testimonial-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center gap-1 mb-3">
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed">"Proses pemesanan sangat mudah dan cepat. Tiket langsung masuk ke email. Recomended!"</p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-accent-blue to-navy rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Andi Wijaya</p>
                        <p class="text-xs text-gray-400">Pengguna TiketMoo</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center gap-1 mb-3">
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed">"Customer service responsif. Saya mendapat bantuan dengan cepat saat ada kendala."</p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-accent-blue to-navy rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Siti Rahma</p>
                        <p class="text-xs text-gray-400">Pengguna TiketMoo</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center gap-1 mb-3">
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed">"Banyak pilihan event menarik. Harga terjangkau dan promo berlimpah!"</p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-accent-blue to-navy rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Budi Santoso</p>
                        <p class="text-xs text-gray-400">Pengguna TiketMoo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-accent-blue via-blue-700 to-navy animate-gradient bg-400"></div>
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="max-w-4xl mx-auto px-5 text-center relative z-10">
        <div data-aos="fade-up">
            <h2 class="text-3xl font-bold text-white mb-4">Siap untuk Pengalaman Tak Terlupakan?</h2>
            <p class="text-blue-100 mb-8">Daftar sekarang dan dapatkan akses ke ribuan event menarik</p>
            <div class="flex gap-4 justify-center">
                <a href="auth/register.php" class="bg-white text-accent-blue px-8 py-3 rounded-xl font-semibold hover:shadow-xl transition-all duration-300 hover:-translate-y-1 inline-flex items-center gap-2 group">
                    <i class="fas fa-user-plus group-hover:scale-110 transition-transform"></i>
                    Daftar Sekarang
                </a>
                <a href="#events" class="border-2 border-white text-white px-8 py-3 rounded-xl font-semibold hover:bg-white/10 transition-all duration-300 hover:-translate-y-1 inline-flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    Lihat Event
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-navy text-white py-12">
    <div class="max-w-7xl mx-auto px-5">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4 group">
                    <div class="w-8 h-8 bg-accent-blue rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                        <i class="fas fa-ticket-alt text-white text-sm"></i>
                    </div>
                    <span class="font-bold text-lg">TiketMoo</span>
                </div>
                <p class="text-gray-300 text-sm">Platform tiket event online terpercaya di Indonesia.</p>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Perusahaan</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">Tentang Kami</a></li>
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">Karir</a></li>
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">Blog</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Bantuan</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-white transition hover:translate-x-1 inline-block">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Ikuti Kami</h4>
                <div class="flex gap-4">
                    <a href="#" class="w-8 h-8 bg-blue-800 rounded-full flex items-center justify-center hover:bg-accent-blue transition-all duration-300 hover:scale-110 hover:rotate-12">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                    <a href="#" class="w-8 h-8 bg-blue-800 rounded-full flex items-center justify-center hover:bg-accent-blue transition-all duration-300 hover:scale-110 hover:rotate-12">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                    <a href="#" class="w-8 h-8 bg-blue-800 rounded-full flex items-center justify-center hover:bg-accent-blue transition-all duration-300 hover:scale-110 hover:rotate-12">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-blue-800 mt-8 pt-6 text-center text-gray-400 text-xs">
            <i class="fas fa-shield-alt mr-1"></i> © <?= date('Y') ?> TiketMoo. All rights reserved.
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        once: true,
        offset: 100,
        duration: 800,
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('nav');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.05)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = 'none';
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Animate stats on scroll with counter effect
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.stat-item').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'all 0.6s ease-out';
        observer.observe(item);
    });
    
    // Add hover effect to cards
    document.querySelectorAll('.event-card, .feature-card, .testimonial-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
</script>
</body>
</html>