<?php
$html = file_get_contents('c:/xampp/htdocs/index.html');
preg_match_all('/<img[^>]+>/i', $html, $matches);
foreach ($matches[0] as $match) {
    echo $match . "\n";
}
?>