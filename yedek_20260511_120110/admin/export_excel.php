<?php
session_start();
require_once '../db.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Dosya adı ve başlıklar
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=kullanici_kayitlari_' . date('Y-m-d_H-i') . '.csv');

// Çıktı tamponunu aç
$output = fopen('php://output', 'w');

// Türkçe karakter sorunu için BOM ekle
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Sütun başlıkları
fputcsv($output, array('ID', 'IP Adresi', 'Giris Tipi', 'T.C. Kimlik', 'Kurumsal ID', 'Bireysel ID', 'Sifre', 'Durum', 'Tarih'), ';');

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['id'],
            $row['ip_address'],
            ucfirst($row['giris_tipi']),
            $row['tc_kimlik'],
            $row['kurumsal_id'],
            $row['bireysel_id'],
            $row['sifre'],
            $row['status'],
            date('d.m.Y H:i:s', strtotime($row['created_at']))
        ), ';');
    }
}

fclose($output);
exit();
?>