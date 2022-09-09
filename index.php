<?php
require_once('ImageToWebpConverter.php');

$image = '/templates/Tomato.png';

$converter = new ImageToWebpConverter();
$newImage = $converter->gen($image);