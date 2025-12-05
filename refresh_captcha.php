<?php
session_start();

header('Content-Type: application/json');

// Generate new CAPTCHA
function generateCaptcha() {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}

// Generate and store new CAPTCHA in session
$newCaptcha = generateCaptcha();
$_SESSION['captcha'] = $newCaptcha;
$_SESSION['captcha_time'] = time();

// Return response
echo json_encode([
    'success' => true,
    'captcha' => $newCaptcha
]);
?>