<?php
session_start();
// Simulasi data jika session belum diset (untuk demo)
if(!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'User';
    $_SESSION['nama'] = 'Pengguna';
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
        .event-card {
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0,102,204,0.2);
        }
        .sidebar-item {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover {
            background-color: #e6f0fa;
            transform: translateX(5px);
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
                <span class="hidden md:inline text-gray-600"><i class="fas fa-user-circle mr-1 text-accent-blue"></i> <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                <a href="#" class="text-gray-600 hover:text-accent-blue transition"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-navy to-accent-blue rounded-2xl p-6 text-white mb-8 animate-[slideIn_0.5s_ease-out]">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">Selamat datang, <?php echo htmlspecialchars($_SESSION['role']); ?>! 👋</h1>
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
                <a href="#" class="text-accent-blue hover:underline text-sm">Lihat semua <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card Event 1 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md event-card">
                    <div class="h-40 bg-gradient-to-r from-blue-400 to-blue-600 relative">
                        <div class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 text-xs font-bold text-accent-blue shadow">
                            <i class="fas fa-tag mr-1"></i> Rp 150K
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800">Konser Jazz Malam</h3>
                        <div class="flex items-center text-gray-500 text-sm mt-2">
                            <i class="fas fa-calendar mr-2 text-accent-blue"></i> 15 Des 2024
                            <i class="fas fa-map-marker-alt ml-3 mr-2 text-accent-blue"></i> Jakarta
                        </div>
                        <p class="text-gray-600 text-sm mt-3">Nikmati alunan jazz terbaik dari musisi ternama.</p>
                        <button class="mt-4 w-full bg-accent-blue text-white py-2 rounded-lg hover:bg-blue-700 transition transform hover:scale-[1.02]">
                            <i class="fas fa-ticket-alt mr-1"></i> Pesan Tiket
                        </button>
                    </div>
                </div>
                
                <!-- Card Event 2 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md event-card">
                    <div class="h-40 bg-gradient-to-r from-green-400 to-teal-500 relative">
                        <div class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 text-xs font-bold text-accent-blue shadow">
                            <i class="fas fa-tag mr-1"></i> Rp 75K
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800">Seminar Teknologi 2024</h3>
                        <div class="flex items-center text-gray-500 text-sm mt-2">
                            <i class="fas fa-calendar mr-2 text-accent-blue"></i> 20 Des 2024
                            <i class="fas fa-map-marker-alt ml-3 mr-2 text-accent-blue"></i> Bandung
                        </div>
                        <p class="text-gray-600 text-sm mt-3">Tingkatkan skill IT bersama para expert.</p>
                        <button class="mt-4 w-full bg-accent-blue text-white py-2 rounded-lg hover:bg-blue-700 transition transform hover:scale-[1.02]">
                            <i class="fas fa-ticket-alt mr-1"></i> Pesan Tiket
                        </button>
                    </div>
                </div>
                
                <!-- Card Event 3 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md event-card">
                    <div class="h-40 bg-gradient-to-r from-purple-400 to-pink-500 relative">
                        <div class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 text-xs font-bold text-accent-blue shadow">
                            <i class="fas fa-tag mr-1"></i> Gratis
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800">Pameran Seni Digital</h3>
                        <div class="flex items-center text-gray-500 text-sm mt-2">
                            <i class="fas fa-calendar mr-2 text-accent-blue"></i> 25 Des 2024
                            <i class="fas fa-map-marker-alt ml-3 mr-2 text-accent-blue"></i> Surabaya
                        </div>
                        <p class="text-gray-600 text-sm mt-3">Koleksi seni digital dari 50+ seniman.</p>
                        <button class="mt-4 w-full bg-accent-blue text-white py-2 rounded-lg hover:bg-blue-700 transition transform hover:scale-[1.02]">
                            <i class="fas fa-ticket-alt mr-1"></i> Pesan Tiket
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistik Singkat -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3">
                <i class="fas fa-calendar-check text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Event Tersedia</p>
                    <p class="font-bold text-xl">12 Event</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3">
                <i class="fas fa-users text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Total Peserta</p>
                    <p class="font-bold text-xl">1,234</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md flex items-center space-x-3">
                <i class="fas fa-ticket-alt text-3xl text-accent-blue"></i>
                <div>
                    <p class="text-gray-500 text-sm">Tiket Terjual</p>
                    <p class="font-bold text-xl">856</p>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>