<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login | Event Ticket System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons (opsional untuk estetika) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom config untuk nuansa biru elegan -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#0a2540',
                        'soft-blue': '#e6f0fa',
                        'accent-blue': '#0066cc',
                        'light-sky': '#f0f7ff',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0fa 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,102,204,0.15);
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(0,102,204,0.2);
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Container dengan animasi fade-in-up -->
    <div class="w-full max-w-md animate-[fadeInUp_0.6s_ease-out]">
        
        <!-- Card Login Elegan -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover">
            
            <!-- Header Biru Gradien -->
            <div class="bg-gradient-to-r from-navy to-accent-blue px-8 py-6 text-center">
                <div class="animate-[float_3s_ease-in-out_infinite]">
                    <i class="fas fa-ticket-alt text-white text-4xl mb-2"></i>
                </div>
                <h2 class="text-white text-2xl font-bold tracking-tight">Event Ticket System</h2>
                <p class="text-blue-100 text-sm mt-1">Akses ke dashboard Anda</p>
            </div>
            
            <!-- Form Login -->
            <div class="p-8">
                <form action="proses_login.php" method="POST" class="space-y-5">
                    <!-- Field Email -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="fas fa-envelope mr-2 text-accent-blue"></i>Alamat Email
                        </label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-accent-blue transition-all duration-200 bg-light-sky"
                               placeholder="contoh@email.com">
                    </div>
                    
                    <!-- Field Password -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-accent-blue"></i>Password
                        </label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-accent-blue transition-all duration-200 bg-light-sky"
                               placeholder="Masukkan password">
                    </div>
                    
                    <!-- Tombol Login -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-accent-blue to-blue-700 text-white font-bold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </form>
                
                <!-- Footer Info -->
                <div class="mt-6 text-center text-sm text-gray-500 border-t border-gray-100 pt-4">
                    <i class="fas fa-shield-alt text-accent-blue mr-1"></i>
                    Sistem Aman & Terpercaya
                </div>
            </div>
        </div>
        
        <!-- Pesan Demo (opsional) -->
        <p class="text-center text-gray-500 text-xs mt-6">
            © 2024 Event Ticket System | Elegant Blue Edition
        </p>
    </div>

</body>
</html>