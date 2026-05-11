<?php
session_start();

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans leading-normal tracking-normal flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-900 text-white shadow-lg flex flex-col">
        <div class="p-6 border-b border-gray-800 text-center">
            <div class="mx-auto h-16 w-16 mb-4 flex items-center justify-center rounded-full bg-red-600 text-white shadow-md text-2xl">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="text-xl font-bold uppercase tracking-wider">Admin Panel</h1>
        </div>
        <nav class="flex-1 px-2 py-4 space-y-2">
            <a href="index.php" class="flex items-center px-4 py-3 bg-red-600 text-white rounded-lg shadow-md">
                <i class="fas fa-home w-6"></i> Ana Sayfa
            </a>
            <a href="logs.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
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
            <h2 class="text-xl font-semibold text-gray-800">Ana Sayfa</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-500"><i class="fas fa-clock mr-1"></i> <span id="current-time"></span></span>
            </div>
        </header>

        <!-- Content Body -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 relative" style="background-image: url('../Gemini_Generated_Image_mzavmwmzavmwmzav.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <!-- Görselin net görünmesi için içerik ve şeffaf katman kaldırıldı -->
            
        </main>
    </div>

    <script>
        // Saat güncelleme
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('tr-TR');
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>
