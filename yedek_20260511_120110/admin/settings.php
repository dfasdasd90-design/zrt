<?php
session_start();
require_once '../db.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$msg = '';
$error = '';

// Şifre değiştirme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';
    $admin_id = $_SESSION['admin_id'];

    if (!empty($current_password) && !empty($new_password) && !empty($new_password_confirm)) {
        if ($new_password === $new_password_confirm) {
            // Mevcut şifreyi kontrol et
            $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (password_verify($current_password, $row['password'])) {
                // Şifreyi güncelle
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $admin_id);
                
                if ($update_stmt->execute()) {
                    $msg = "Şifreniz başarıyla güncellendi!";
                } else {
                    $error = "Şifre güncellenirken bir hata oluştu.";
                }
                $update_stmt->close();
            } else {
                $error = "Mevcut şifreniz yanlış!";
            }
            $stmt->close();
        } else {
            $error = "Yeni şifreler birbiriyle eşleşmiyor!";
        }
    } else {
        $error = "Lütfen tüm alanları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Yönetim Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            <a href="logs.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                <i class="fas fa-list w-6"></i> Kayıt Logları
            </a>
            <a href="settings.php" class="flex items-center px-4 py-3 bg-red-600 text-white rounded-lg shadow-md">
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
            <h2 class="text-xl font-semibold text-gray-800">Sistem Ayarları</h2>
        </header>

        <!-- Content Body -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-800"><i class="fas fa-key text-gray-500 mr-2"></i>Şifre Değiştir</h3>
                </div>
                <div class="p-6">
                    <?php if(!empty($msg)): ?>
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                            <p><?php echo $msg; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error)): ?>
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                            <p><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                            <input type="text" value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" disabled class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 text-gray-500 cursor-not-allowed sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                            <input type="password" id="current_password" name="current_password" required class="mt-1 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border outline-none">
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                            <input type="password" id="new_password" name="new_password" required class="mt-1 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border outline-none">
                        </div>
                        
                        <div>
                            <label for="new_password_confirm" class="block text-sm font-medium text-gray-700">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="new_password_confirm" name="new_password_confirm" required class="mt-1 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border outline-none">
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                Şifreyi Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
