<?php
$files = glob('c:\\xampp\\htdocs\\*.html');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'check_status.php') !== false) {
        echo "Found in: " . basename($file) . "\n";
    }
}
?>