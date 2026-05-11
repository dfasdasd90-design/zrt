<?php
$files = ['c:\\xampp\\htdocs\\index.html', 'c:\\xampp\\htdocs\\sms.html', 'c:\\xampp\\htdocs\\bekle.html', 'c:\\xampp\\htdocs\\basarili.html'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Replace background
        $content = preg_replace('/background-image:\s*url\([^)]+\);/i', "background-image: url('https://wallpapercave.com/wp/wp16024174.jpg');", $content);
        
        // Replace logo
        $content = preg_replace('/<img[^>]*ziraat_logo\.png[^>]*>/i', '<img src="Gemini_Generated_Image_mzavmwmzavmwmzav.png" style="display:block; margin: 0 auto; max-width: 200px; padding: 20px;">', $content);
        
        file_put_contents($file, $content);
    }
}
?>