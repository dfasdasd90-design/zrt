<?php
$content = file_get_contents('c:\\xampp\\htdocs\\index.html');
preg_match_all('/<img[^>]*>/i', $content, $matches);
foreach ($matches[0] as $match) {
    echo htmlspecialchars($match) . "\n";
}
?>