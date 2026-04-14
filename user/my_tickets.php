<?php
session_start();
include '../config/koneksi.php';

// Proteksi login
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Set default session jika belum ada
if(!isset($_SESSION['nama'])) {
    $_SESSION['nama'] = 'Pengguna';
}

$id_user = $_SESSION['id_user'] ?? 1;

// Ambil semua tiket yang sudah dibeli user
$query_tickets = mysqli_query($conn, "
    SELECT 
        a.id_attendee,
        a.kode_tiket,
        a.status_checkin,
        a.created_at as tiket_created_at,
        od.qty,
        od.subtotal,
        od.nama_tiket,
        od.harga,
        o.no_order,
        o.tanggal_order,
        o.total as total_order,
        e.nama_event,
        e.tanggal as event_tanggal,
        e.foto as event_foto,
        v.nama_venue,
        v.alamat as venue_alamat
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN event e ON o.id_event = e.id_event
    JOIN venue v ON e.id_venue = v.id_venue
    WHERE o.id_user = $id_user
    ORDER BY o.tanggal_order DESC, a.id_attendee ASC
");

// Kelompokkan berdasarkan order
$orders = [];
while($row = mysqli_fetch_assoc($query_tickets)) {
    $order_no = $row['no_order'];
    if(!isset($orders[$order_no])) {
        $orders[$order_no] = [
            'no_order' => $row['no_order'],
            'tanggal_order' => $row['tanggal_order'],
            'total_order' => $row['total_order'],
            'nama_event' => $row['nama_event'],
            'event_tanggal' => $row['event_tanggal'],
            'event_foto' => $row['event_foto'],
            'nama_venue' => $row['nama_venue'],
            'venue_alamat' => $row['venue_alamat'],
            'tickets' => []
        ];
    }
    $orders[$order_no]['tickets'][] = $row;
}

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
    <title>Tiket Saya | EventTicket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .ticket-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -12px rgba(0,102,204,0.2);
        }
        .status-checkin {
            transition: all 0.2s ease;
        }
        @media print {
            .no-print {
                display: none;
            }
            .ticket-card {
                page-break-inside: avoid;
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        .qr-placeholder {
            background: repeating-linear-gradient(45deg, #333, #333 10px, #555 10px, #555 20px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-soft-blue to-white min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50 no-print">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-ticket-alt text-accent-blue text-2xl"></i>
                <span class="font-bold text-xl bg-gradient-to-r from-navy to-accent-blue bg-clip-text text-transparent">EventTicket</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-circle text-accent-blue text-xl"></i>
                    <span class="hidden md:inline text-gray-600"><?= safe($_SESSION['nama']) ?></span>
                </div>
                <a href="dashboard.php" class="text-gray-600 hover:text-accent-blue transition">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="../auth/logout.php" class="text-gray-600 hover:text-red-500 transition">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-ticket-alt text-accent-blue"></i>
                Tiket Saya
            </h1>
            <p class="text-gray-500 mt-2">Semua tiket yang sudah Anda pesan</p>
        </div>
        
        <?php if(empty($orders)): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-md p-12 text-center">
            <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Tiket</h3>
            <p class="text-gray-500 mb-6">Anda belum memiliki tiket. Yuk, pesan tiket event favorit Anda!</p>
            <a href="dashboard.php" class="bg-accent-blue text-white px-6 py-3 rounded-xl font-semibold hover:bg-accent-hover transition inline-flex items-center gap-2">
                <i class="fas fa-search"></i> Cari Event
            </a>
        </div>
        <?php else: ?>
        
        <!-- Tickets List -->
        <div class="space-y-8">
            <?php foreach($orders as $order): ?>
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <!-- Order Header -->
                <div class="bg-gradient-to-r from-navy to-accent-blue px-6 py-4">
                    <div class="flex flex-wrap justify-between items-center">
                        <div>
                            <p class="text-blue-100 text-sm">Nomor Pesanan</p>
                            <p class="text-white font-semibold"><?= safe($order['no_order']) ?></p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Tanggal Pesan</p>
                            <p class="text-white"><?= date('d F Y', strtotime($order['tanggal_order'])) ?></p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Total</p>
                            <p class="text-white font-semibold">Rp <?= number_format($order['total_order'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Event Info -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            <?php 
                            $foto = !empty($order['event_foto']) ? "../uploads/event/" . $order['event_foto'] : "https://placehold.co/400x400?text=Event";
                            ?>
                            <img src="<?= $foto ?>" class="w-full h-full object-cover" alt="<?= safe($order['nama_event']) ?>">
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-xl text-gray-800"><?= safe($order['nama_event']) ?></h3>
                            <div class="flex flex-wrap gap-4 mt-1 text-sm text-gray-500">
                                <span><i class="fas fa-calendar-alt text-accent-blue mr-1"></i> <?= date('l, d F Y', strtotime($order['event_tanggal'])) ?></span>
                                <span><i class="fas fa-map-marker-alt text-accent-blue mr-1"></i> <?= safe($order['nama_venue']) ?></span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1"><?= safe($order['venue_alamat']) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Tickets -->
                <div class="p-6">
                    <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-qrcode text-accent-blue"></i>
                        Daftar Tiket
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($order['tickets'] as $ticket): ?>
                        <div class="ticket-card border border-gray-200 rounded-xl p-4 hover:shadow-lg transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-soft-blue text-accent-blue rounded-lg text-xs font-semibold">
                                            <?= safe($ticket['nama_tiket']) ?>
                                        </span>
                                        <?php if($ticket['status_checkin'] == 'sudah'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-semibold">
                                            <i class="fas fa-check-circle"></i> Sudah Check-in
                                        </span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-semibold">
                                            <i class="fas fa-clock"></i> Belum Check-in
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="font-mono text-sm text-gray-600 mt-2">
                                        <i class="fas fa-barcode text-accent-blue mr-1"></i>
                                        <?= safe($ticket['kode_tiket']) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Harga Tiket</p>
                                    <p class="font-semibold text-gray-800">Rp <?= number_format($ticket['harga'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                            
                            <!-- QR Code Placeholder (simulasi) -->
                            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-gray-800 rounded flex items-center justify-center">
                                        <i class="fas fa-qrcode text-white text-xl"></i>
                                    </div>
                                    <span class="text-xs text-gray-400">Scan untuk check-in</span>
                                </div>
                                <button onclick="showTicketDetail('<?= $ticket['kode_tiket'] ?>', '<?= safe($ticket['nama_tiket']) ?>', '<?= safe($order['nama_event']) ?>', '<?= $order['event_tanggal'] ?>', '<?= safe($order['nama_venue']) ?>')" 
                                        class="text-accent-blue text-sm hover:underline flex items-center gap-1 no-print">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 flex flex-wrap gap-3 no-print">
                    <button onclick="printTickets('<?= $order['no_order'] ?>')" class="text-gray-600 hover:text-accent-blue transition text-sm flex items-center gap-1">
                        <i class="fas fa-print"></i> Cetak Semua Tiket
                    </button>
                    <a href="detail_event.php?id=<?= $order['id_event'] ?? '' ?>" class="text-accent-blue hover:underline text-sm flex items-center gap-1">
                        <i class="fas fa-info-circle"></i> Lihat Detail Event
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Detail Tiket -->
    <div id="ticketModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 no-print" onclick="closeModal()">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 p-6" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Detail Tiket</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div id="modalContent">
                <!-- Content will be filled by JavaScript -->
            </div>
            <button onclick="printSingleTicket()" class="mt-4 w-full bg-accent-blue text-white py-2 rounded-lg font-semibold hover:bg-accent-hover transition flex items-center justify-center gap-2">
                <i class="fas fa-print"></i> Cetak Tiket
            </button>
        </div>
    </div>
    
    <script>
        let currentTicket = null;
        
        function showTicketDetail(kode, namaTiket, namaEvent, tanggal, venue) {
            currentTicket = { kode, namaTiket, namaEvent, tanggal, venue };
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="space-y-3">
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-sm">Kode Tiket</p>
                        <p class="font-mono font-semibold text-gray-800">${kode}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-sm">Jenis Tiket</p>
                        <p class="font-semibold text-gray-800">${namaTiket}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-sm">Event</p>
                        <p class="font-semibold text-gray-800">${namaEvent}</p>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-sm">Tanggal Event</p>
                        <p class="font-semibold text-gray-800">${new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Lokasi Venue</p>
                        <p class="font-semibold text-gray-800">${venue}</p>
                    </div>
                </div>
                <div class="mt-4 p-4 bg-gray-100 rounded-lg text-center">
                    <div class="w-32 h-32 bg-white mx-auto rounded flex items-center justify-center">
                        <i class="fas fa-qrcode text-6xl text-gray-800"></i>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Scan QR Code untuk check-in</p>
                </div>
            `;
            document.getElementById('ticketModal').classList.remove('hidden');
            document.getElementById('ticketModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('ticketModal').classList.add('hidden');
            document.getElementById('ticketModal').classList.remove('flex');
            currentTicket = null;
        }
        
        function printSingleTicket() {
            if (currentTicket) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Cetak Tiket - ${currentTicket.kode}</title>
                        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                    </head>
                    <body class="p-8">
                        <div class="max-w-md mx-auto border rounded-lg p-6">
                            <div class="text-center mb-4">
                                <h2 class="text-xl font-bold">EventTicket</h2>
                                <p class="text-sm text-gray-600">Tiket Event</p>
                            </div>
                            <div class="border-t pt-4">
                                <p><strong>Kode Tiket:</strong> ${currentTicket.kode}</p>
                                <p><strong>Jenis Tiket:</strong> ${currentTicket.namaTiket}</p>
                                <p><strong>Event:</strong> ${currentTicket.namaEvent}</p>
                                <p><strong>Tanggal:</strong> ${currentTicket.tanggal}</p>
                                <p><strong>Venue:</strong> ${currentTicket.venue}</p>
                            </div>
                            <div class="text-center mt-6">
                                <div class="inline-block p-4 bg-gray-100 rounded">
                                    <i class="fas fa-qrcode text-4xl"></i>
                                </div>
                            </div>
                            <p class="text-center text-xs text-gray-500 mt-4">Scan QR Code untuk check-in</p>
                        </div>
                        <script>window.print();<\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            }
        }
        
        function printTickets(orderNo) {
            window.print();
        }
    </script>
</body>
</html>