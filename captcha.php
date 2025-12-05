<?php
session_start();

// Generate random captcha code
$captcha_code = '';
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
for ($i = 0; $i < 6; $i++) {
    $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
}

// Store in session
$_SESSION['captcha_answer'] = $captcha_code;

// Create image
$width = 200;
$height = 70;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 15, 15, 35);
$text_color = imagecolorallocate($image, 255, 255, 255);
$line_color = imagecolorallocate($image, 102, 126, 234);
$dot_color = imagecolorallocate($image, 118, 75, 162);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add random lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Add random dots
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $dot_color);
}

// Add text with distortion
$font_size = 24;
$angle = 0;
$x = 20;
$y = 45;

for ($i = 0; $i < strlen($captcha_code); $i++) {
    $char = $captcha_code[$i];
    $char_angle = rand(-15, 15);
    $char_y = $y + rand(-5, 5);
    
    imagestring($image, 5, $x, $char_y - 20, $char, $text_color);
    $x += 28;
}

// Output
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>