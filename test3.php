<?php

$gif = '/var/www/html/bot/radar/1460964295.gif';

$background_dir = "/var/www/html/bot/radar/IDR702-template.png";
$radar_dir = "/var/www/html/bot/radar/IDR702.T.201604180624.png";
$out_dir = "/var/www/html/bot/radar/test.gif";

$radar = imagecreatefrompng($radar_dir);
$background = imagecreatefrompng($background_dir);

imagecopymerge($background, $radar, 0, 0, 0, 0, imagesx($radar), imagesy($radar), 100);

imagegif($background, $out_dir);