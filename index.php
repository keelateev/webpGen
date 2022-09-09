<?php
require_once('ImageToWebpConverter.php');

$image = 'templates/Tomato.png';
$converter = new ImageToWebpConverter();
$webpBrowserAccepted = empty($_SERVER['HTTP_ACCEPT']);
$newImage = $converter->gen($image, $webpBrowserAccepted);
echo $newImage;