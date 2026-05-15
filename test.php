<?php
require 'vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
$opt = new QROptions([
    'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
    'eccLevel' => \chillerlan\QRCode\Common\EccLevel::H,
    'outputBase64' => false
]);
$qr = new QRCode($opt);
$raw = $qr->render('test');
echo base64_encode(substr($raw, 0, 10)) . "\n";
if (imagecreatefromstring($raw) !== false) echo "GD load successful\n";
