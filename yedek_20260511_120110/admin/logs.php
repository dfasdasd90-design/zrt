<?php
session_start();
require_once '../db.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Durum güncelleme işlemi
if (isset($_GET['set_status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $status = $_GET['set_status'];
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    exit(); // Fetch ile çağrıldığı için HTML döndürmeye gerek yok
}

// Hata/Mesaj gösterimi
$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'status_updated') $msg = 'Kullanıcı durumu başarıyla güncellendi.';
    if ($_GET['msg'] == 'deleted') $msg = 'Kayıt başarıyla silindi.';
    if ($_GET['msg'] == 'deleted_all') $msg = 'Tüm loglar başarıyla temizlendi.';
    if ($_GET['msg'] == 'banned') $msg = 'IP Adresi başarıyla banlandı.';
}

// Toplu silme işlemi
if (isset($_GET['delete_all']) && $_GET['delete_all'] == 1) {
    $conn->query("TRUNCATE TABLE users");
    header("Location: logs.php?msg=deleted_all");
    exit();
}

// Kayıt silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: logs.php?msg=deleted");
    exit();
}

// Ban işlemi
if (isset($_GET['ban_ip'])) {
    $ban_ip = $_GET['ban_ip'];
    if (!empty($ban_ip)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO banned_ips (ip_address) VALUES (?)");
        $stmt->bind_param("s", $ban_ip);
        $stmt->execute();
        $stmt->close();
        header("Location: logs.php?msg=banned");
        exit();
    }
}

// Veritabanında last_seen sütunu yoksa oluştur (Eski tablolara uyumluluk)
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen INT DEFAULT 0");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Logları - Yönetim Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        let lastHTML = '';
        
        function loadLogs() {
            let timestamp = new Date().getTime();
            fetch('get_logs.php?t=' + timestamp)
                .then(response => response.text())
                .then(html => {
                    if (lastHTML !== html) {
                        lastHTML = html;
                        let tbody = document.getElementById('log-tbody');
                        tbody.innerHTML = html;
                        
                        // Toplam sayıyı çekmek için gizli div eklenecek
                        let countMatch = html.match(/<!-- TOTAL_COUNT:(\d+) -->/);
                        if (countMatch && countMatch[1]) {
                            document.getElementById('total-logs-count').innerText = countMatch[1];
                        }
                    }
                })
                .catch(err => console.error('Log yükleme hatası:', err));
        }
        
        function updateStatus(id, status) {
            fetch('logs.php?set_status=' + status + '&id=' + id)
                .then(() => loadLogs());
        }

        // Her saniyede bir logları arka planda yenile (titreşimsiz)
        setInterval(loadLogs, 1000);
    </script>
</head>
<body class="bg-gray-50 font-sans leading-normal tracking-normal flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-900 text-white shadow-lg flex flex-col">
        <div class="p-6 border-b border-gray-800">
            <h1 class="text-2xl font-bold uppercase tracking-wider"><i class="fas fa-shield-alt mr-2"></i>Admin Panel</h1>
        </div>
        <nav class="flex-1 px-2 py-4 space-y-2">
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                <i class="fas fa-home w-6"></i> Ana Sayfa
            </a>
            <a href="logs.php" class="flex items-center px-4 py-3 bg-red-600 text-white rounded-lg shadow-md">
                <i class="fas fa-list w-6"></i> Kayıt Logları
            </a>
            <a href="settings.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                <i class="fas fa-cog w-6"></i> Ayarlar
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="mb-2 px-2 text-sm text-gray-400">Giriş yapan: <span class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span></div>
            <a href="logout.php" class="flex items-center justify-center w-full px-4 py-2 bg-gray-800 hover:bg-red-700 text-white rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10">
            <h2 class="text-xl font-semibold text-gray-800">Veritabanı Kayıtları</h2>
            <div class="flex items-center space-x-4">
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                    Toplam Kayıt: <span id="total-logs-count" class="font-bold">Yükleniyor...</span>
                </span>
            </div>
        </header>

        <!-- Content Body -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <?php if(!empty($msg)): ?>
            <div class="mb-4 px-4 py-3 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?php echo $msg; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="bg-white rounded-t-lg shadow-sm border-b border-gray-200 p-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-700"><i class="fas fa-database mr-2 text-gray-400"></i>Canlı Log Takibi</h3>
                <div class="flex space-x-3">
                    <a href="export_excel.php" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                        <i class="fas fa-file-excel mr-2"></i> Excel İndir
                    </a>
                    <a href="logs.php?delete_all=1" onclick="return confirm('Tüm kayıtları silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> Toplu Sil
                    </a>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-b-lg shadow-sm overflow-hidden border border-t-0 border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktiflik / IP</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">T.C. / Bireysel ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurumsal ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Şifre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="log-tbody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="9" class="px-6 py-10 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Loglar yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // İlk yüklemede logları çek
        loadLogs();
    </script>
</body>
</html>