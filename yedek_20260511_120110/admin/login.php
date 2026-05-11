<?php
session_start();
require_once '../db.php';

// Eğer zaten giriş yapmışsa yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Hatalı şifre!";
            }
        } else {
            $error = "Kullanıcı bulunamadı!";
        }
        $stmt->close();
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
    <title>Admin Girişi - Yönetim Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen font-sans" style="background-image: url('https://wallpapercave.com/wp/wp16024174.jpg'); background-size: cover; background-position: center;">
    <div class="bg-white/80 p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide">Yönetici Girişi</h2>
            <p class="text-gray-500 text-sm mt-2">Sisteme erişmek için bilgilerinizi giriniz</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-bold">Hata</p>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="username" name="username" required class="focus:ring-red-500 focus:border-red-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md p-3 border bg-gray-50 outline-none transition-colors" placeholder="Kullanıcı adınız">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" required class="focus:ring-red-500 focus:border-red-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md p-3 border bg-gray-50 outline-none transition-colors" placeholder="Şifreniz">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    Giriş Yap
                </button>
            </div>
        </form>
    </div>
</body>
</html>
