<?php
session_start();
require_once 'db.php';

// Banned IP kontrolü
$ip_address = $_SERVER['REMOTE_ADDR'];

// IP banlı mı kontrol et
$stmt = $conn->prepare("SELECT id FROM banned_ips WHERE ip_address = ?");
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'banned']);
    exit();
}

// Kullanıcıyı veritabanına ekle veya güncelle
if (!isset($_SESSION['user_id'])) {
    $time = time();
    $stmt = $conn->prepare("INSERT INTO users (ip_address, last_seen, status) VALUES (?, ?, 'bekle')");
    $stmt->bind_param("si", $ip_address, $time);
    $stmt->execute();
    $_SESSION['user_id'] = $conn->insert_id;
    $stmt->close();
}

echo json_encode(['status' => 'ok']);
?>