<?php
$files = ['c:\\xampp\\htdocs\\index.html', 'c:\\xampp\\htdocs\\sms.html', 'c:\\xampp\\htdocs\\bekle.html', 'c:\\xampp\\htdocs\\basarili.html'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Revert charset to ISO-8859-9 to fix broken characters!
        $content = str_replace('charset=utf-8', 'charset=ISO-8859-9', $content);
        
        file_put_contents($file, $content);
    }
}
?>