<?php
$html = file_get_contents('c:/xampp/htdocs/index.html');
preg_match_all('/<img[^>]+>/i', $html, $matches);
file_put_contents('c:/xampp/htdocs/images.txt', print_r($matches[0], true));
?>