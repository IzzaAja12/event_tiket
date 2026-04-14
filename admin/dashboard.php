<?php
session_start();
// Simulasi data untuk admin
if(!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'Administrator';
    $_SESSION['nama'] = 'Admin';
}
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
        .menu-card:hover i, .menu-card:hover h3 {
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
                <span class="text-gray-700"><i class="fas fa-user-shield mr-1 text-accent-blue"></i> <?php echo htmlspecialchars($_SESSION['role']); ?></span>
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
                    <p class="text-gray-500 mt-1">Kelola event, tiket, venue, dan voucher dengan mudah</p>
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
            <div class="mt-3 text-accent-blue text-sm">Event Aktif →</div>
        </div>
    </a>

    <!-- Tiket -->
    <a href="tiket.php" style="text-decoration:none;">
        <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
            <i class="fas fa-ticket-alt text-4xl text-accent-blue mb-3"></i>
            <h3 class="font-bold text-lg text-gray-800">Tiket</h3>
            <p class="text-gray-500 text-sm mt-1">Atur jenis tiket</p>
            <div class="mt-3 text-accent-blue text-sm">Tiket Terjual →</div>
        </div>
    </a>

    <!-- Venue -->
    <a href="venue.php" style="text-decoration:none;">
        <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
            <i class="fas fa-map-marker-alt text-4xl text-accent-blue mb-3"></i>
            <h3 class="font-bold text-lg text-gray-800">Venue</h3>
            <p class="text-gray-500 text-sm mt-1">Lokasi & kapasitas</p>
            <div class="mt-3 text-accent-blue text-sm">Venue Tersedia →</div>
        </div>
    </a>

    <!-- Voucher -->
    <a href="voucher.php" style="text-decoration:none;">
        <div class="bg-white rounded-xl shadow-md p-6 text-center cursor-pointer menu-card transition-all">
            <i class="fas fa-gift text-4xl text-accent-blue mb-3"></i>
            <h3 class="font-bold text-lg text-gray-800">Voucher</h3>
            <p class="text-gray-500 text-sm mt-1">Promo & diskon</p>
            <div class="mt-3 text-accent-blue text-sm">Voucher Aktif →</div>
        </div>
    </a>

</div>        
        <!-- Statistik Cepat (Preview Data) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Total Event</p>
                        <p class="text-2xl font-bold text-gray-800">24</p>
                    </div>
                    <i class="fas fa-calendar-alt text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Total Tiket</p>
                        <p class="text-2xl font-bold text-gray-800">1.450</p>
                    </div>
                    <i class="fas fa-ticket-alt text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Venue Tersedia</p>
                        <p class="text-2xl font-bold text-gray-800">8</p>
                    </div>
                    <i class="fas fa-building text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
            <div class="stat-card bg-gradient-to-br from-white to-blue-50 rounded-xl p-4 shadow-sm border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm">Voucher Aktif</p>
                        <p class="text-2xl font-bold text-gray-800">5</p>
                    </div>
                    <i class="fas fa-percent text-3xl text-accent-blue opacity-70"></i>
                </div>
            </div>
        </div>
        
        <!-- Tabel Preview Data Terbaru (Simulasi) -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-800"><i class="fas fa-list mr-2 text-accent-blue"></i> Event Terbaru</h3>
                <a href="#" class="text-accent-blue text-sm hover:underline">Kelola Semua <i class="fas fa-arrow-right ml-1"></i></a>
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
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4">Konser Rock 2024</td>
                            <td class="px-6 py-4">Stadium Utama</td>
                            <td class="px-6 py-4">10 Jan 2025</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Aktif</span></td>
                        </tr>
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4">Festival Kopi Nusantara</td>
                            <td class="px-6 py-4">Convention Hall</td>
                            <td class="px-6 py-4">25 Jan 2025</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Segera</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer Admin -->
        <div class="text-center text-gray-400 text-xs mt-8">
            <i class="fas fa-shield-alt text-accent-blue"></i> Admin Panel Secure • Event Ticket System v2.0
        </div>
    </div>
    
</body>
</html>