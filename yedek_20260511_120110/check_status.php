<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$time = time();
$ip = $_SERVER['REMOTE_ADDR'];

// 1. Session varsa ID'yi al
if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $conn->query("UPDATE users SET last_seen = $time WHERE id = $id");
    
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    // 2. Session kaybolmuşsa (örn. yeni sekme) IP'ye göre en son aktif olan kaydı bul
    $stmt = $conn->prepare("SELECT id, status FROM users WHERE ip_address = ? ORDER BY last_seen DESC LIMIT 1");
    $stmt->bind_param("s", $ip);
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Eğer IP'den bulduysak, last_seen'i de güncelleyelim
    if (!isset($_SESSION['user_id'])) {
        $found_id = $row['id'];
        $conn->query("UPDATE users SET last_seen = $time WHERE id = $found_id");
    }
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'unknown']);
}
$stmt->close();
?>