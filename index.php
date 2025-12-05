<?php
require_once 'db.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: $role/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 NIT AMMS - Secure Login</title>
    <link rel="icon" href="Nit_logo.png" type="image/svg+xml" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login-styles.css">

    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    overflow-x: hidden;
}

body {
    min-height: 100vh;
    font-family: 'Poppins', sans-serif;
    background: #0a0a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

/* Background Elements */
.mesh-gradient {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background: 
        radial-gradient(ellipse at 10% 20%, rgba(120, 0, 255, 0.4) 0%, transparent 50%),
        radial-gradient(ellipse at 90% 80%, rgba(255, 0, 128, 0.4) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 50%, rgba(0, 212, 255, 0.3) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(255, 165, 0, 0.3) 0%, transparent 40%);
    animation: meshMove 15s ease-in-out infinite;
}

@keyframes meshMove {
    0%, 100% { filter: hue-rotate(0deg); transform: scale(1); }
    50% { filter: hue-rotate(30deg); transform: scale(1.1); }
}

.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    animation: orbFloat 20s ease-in-out infinite;
    z-index: 0;
}

.orb-1 {
    width: 600px;
    height: 600px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    top: -200px;
    left: -200px;
}

.orb-2 {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    bottom: -150px;
    right: -150px;
    animation-delay: -5s;
}

.orb-3 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: -10s;
}

@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.6; }
    25% { transform: translate(50px, -50px) scale(1.1); opacity: 0.8; }
    50% { transform: translate(-30px, 30px) scale(0.9); opacity: 0.5; }
    75% { transform: translate(40px, 40px) scale(1.05); opacity: 0.7; }
}

.stars {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
}

.star {
    position: absolute;
    background: white;
    border-radius: 50%;
    animation: twinkle 3s ease-in-out infinite;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
}

.grid-lines {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    z-index: 0;
    animation: gridMove 20s linear infinite;
}

@keyframes gridMove {
    0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
    100% { transform: perspective(500px) rotateX(60deg) translateY(50px); }
}

/* Security Badge */
.security-badge {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 100;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.2));
    backdrop-filter: blur(10px);
    border: 1px solid rgba(34, 197, 94, 0.4);
    border-radius: 50px;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #86efac;
    animation: securityPulse 2s ease-in-out infinite;
}

@keyframes securityPulse {
    0%, 100% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.3); }
    50% { box-shadow: 0 0 30px rgba(34, 197, 94, 0.5); }
}

/* Main Container */
.main-wrapper {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 60px;
    padding: 40px 20px;
    z-index: 10;
    position: relative;
    max-width: 1400px;
    width: 100%;
    min-height: 100vh;
}

/* Login Container */
.login-container {
    flex: 1;
    max-width: 500px;
    order: 2;
    animation: slideInRight 1s ease-out;
    display: flex;
    justify-content: center;
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(100px); }
    to { opacity: 1; transform: translateX(0); }
}

.login-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(40px);
    border-radius: 32px;
    padding: 50px 45px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
    width: 100%;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
    background-size: 300% 100%;
    animation: borderGradient 4s linear infinite;
}

@keyframes borderGradient {
    0% { background-position: 0% 50%; }
    100% { background-position: 300% 50%; }
}

.login-header {
    text-align: center;
    margin-bottom: 35px;
    position: relative;
    z-index: 1;
}

.login-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: white;
    margin-bottom: 8px;
}

.login-header p {
    color: rgba(255,255,255,0.5);
    font-size: 14px;
}

/* Live Clock */
.live-clock {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.clock-segment {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 12px 18px;
    text-align: center;
    min-width: 70px;
}

.clock-segment .value {
    font-size: 28px;
    font-weight: 700;
    color: white;
    font-family: 'Courier New', monospace;
    background: linear-gradient(135deg, #667eea, #f093fb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.clock-segment .label {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 4px;
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
    animation: alertSlide 0.5s ease-out;
    position: relative;
    z-index: 1;
}

@keyframes alertSlide {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
}

/* Form Styling */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 22px;
    position: relative;
    z-index: 1;
}

.form-group {
    position: relative;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    text-align: left;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    z-index: 2;
    pointer-events: none;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 18px 20px 18px 55px;
    background: rgba(255,255,255,0.05);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    font-size: 15px;
    color: white;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    outline: none;
}

.form-group input::placeholder {
    color: rgba(255,255,255,0.3);
}

.form-group input:focus,
.form-group select:focus {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
}

.form-group select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 20px center;
    padding-right: 45px;
}

.form-group select option {
    background: #1a1a2e;
    color: white;
    padding: 15px;
}

.password-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255,255,255,0.4);
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s;
    padding: 5px;
    z-index: 3;
}

.password-toggle:hover {
    color: #667eea;
}

/* CAPTCHA Styling */
.captcha-group {
    background: rgba(102, 126, 234, 0.05);
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: 20px;
    padding: 20px;
}

.captcha-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    justify-content: center;
}

.captcha-image {
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    height: 70px;
}

.captcha-refresh {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 12px;
    width: 50px;
    height: 50px;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.captcha-refresh:hover {
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
}

.captcha-help {
    display: block;
    text-align: center;
    color: rgba(255,255,255,0.4);
    font-size: 11px;
    margin-top: 10px;
}

/* Submit Button */
.btn-submit {
    width: 100%;
    padding: 20px;
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    margin-top: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
    letter-spacing: 0.5px;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.btn-submit:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.5);
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit .btn-text {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-submit.loading {
    pointer-events: none;
}

.btn-submit.loading .btn-text {
    opacity: 0;
}

.btn-submit.loading::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Footer */
.login-footer {
    margin-top: 30px;
    text-align: center;
    position: relative;
    z-index: 1;
}

.forgot-password {
    color: rgba(255,255,255,0.5);
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}

.forgot-password:hover {
    color: #667eea;
}

/* Mobile Developer Section */
.mobile-dev-section {
    display: none;
}

/* Brand Section */
.brand-section {
    flex: 1;
    max-width: 600px;
    order: 1;
    color: white;
    animation: slideInLeft 1s ease-out;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-100px); }
    to { opacity: 1; transform: translateX(0); }
}

.brand-logo {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    margin: 0 auto 30px;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.5);
    animation: logoFloat 6s ease-in-out infinite;
    position: relative;
    overflow: hidden;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(2deg); }
}

.brand-title {
    font-size: 56px;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #fff, #a8edea, #fed6e3);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.brand-subtitle {
    font-size: 18px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 40px;
    line-height: 1.6;
}

.feature-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    width: 100%;
    max-width: 600px;
}

.feature-card {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 18px 22px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    backdrop-filter: blur(10px);
    transition: all 0.4s ease;
}

.feature-card:hover {
    background: rgba(255,255,255,0.1);
    transform: translateY(-5px);
    border-color: rgba(102, 126, 234, 0.5);
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.feature-icon.purple { background: linear-gradient(135deg, #667eea, #764ba2); }
.feature-icon.pink { background: linear-gradient(135deg, #f093fb, #f5576c); }
.feature-icon.blue { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.feature-icon.orange { background: linear-gradient(135deg, #fa709a, #fee140); }

.feature-text {
    text-align: left;
    flex: 1;
}

.feature-text h4 {
    color: white;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 3px;
}

.feature-text p {
    color: rgba(255,255,255,0.5);
    font-size: 12px;
}

.feature-text a {
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    transition: color 0.3s;
}

.feature-text a:hover {
    color: #667eea;
}

.divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin: 30px 0 25px;
    color: rgba(255,255,255,0.3);
    font-size: 12px;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
}

.dev-section {
    padding: 25px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    width: 100%;
}

.dev-title {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 18px;
}

.dev-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.dev-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 22px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.dev-link:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
    border-color: rgba(102, 126, 234, 0.4);
    color: white;
    transform: translateY(-4px);
}

.dev-icon {
    font-size: 18px;
}

/* Audio Control */
.audio-indicator {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 20;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    border: 2px solid rgba(255,255,255,0.2);
}

.audio-indicator:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 50px rgba(102, 126, 234, 0.6);
}

.audio-indicator.playing {
    animation: audioPulse 0.8s ease-in-out infinite;
}

@keyframes audioPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.audio-icon {
    font-size: 28px;
}

/* MOBILE RESPONSIVE */
@media (max-width: 768px) {
    .main-wrapper {
        flex-direction: column;
        gap: 25px;
        padding: 20px;
    }

    .security-badge {
        top: 10px;
        left: 10px;
        font-size: 11px;
        padding: 8px 16px;
    }

    .brand-section {
        order: 1;
        max-width: 100%;
        padding: 0;
    }

    .login-container {
        order: 2;
        max-width: 100%;
    }

    .brand-logo {
        width: 70px;
        height: 70px;
        font-size: 35px;
        margin-bottom: 12px;
    }

    .brand-title {
        font-size: 28px;
        margin-bottom: 8px;
    }

    .brand-subtitle {
        font-size: 13px;
        margin-bottom: 0;
    }

    .feature-cards,
    .brand-section .divider,
    .brand-section .dev-section {
        display: none;
    }

    .mobile-dev-section {
        display: block;
        margin-top: 25px;
    }

    .login-card {
        padding: 35px 28px;
    }

    .clock-segment {
        padding: 10px 14px;
        min-width: 60px;
    }

    .clock-segment .value {
        font-size: 20px;
    }

    .captcha-image {
        max-width: 180px;
        height: auto;
    }

    .captcha-refresh {
        width: 45px;
        height: 45px;
        font-size: 20px;
    }

    .audio-indicator {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
    }

    .audio-icon {
        font-size: 24px;
    }
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 4px;
}
    </style>
</head>
<body>
    <!-- Background Elements -->
    <div class="mesh-gradient"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-lines"></div>
    <div class="stars" id="stars"></div>

    <div class="audio-indicator" id="audioIndicator" title="Click to replay welcome message">
        <span class="audio-icon">🔊</span>
    </div>

    <!-- Security Badge -->
    <div class="security-badge" title="This site is protected with multiple security layers">
        🛡️ <span>Secured</span>
    </div>

    <div class="main-wrapper">
        <!-- Brand Section -->
        <div class="brand-section">
            <div class="brand-logo">🎓</div>
            <h1 class="brand-title">NIT AMMS</h1>
            <p class="brand-subtitle">
                Nagpur Institute Of Technology<br>
                Asset and Maintenance Management System
            </p>
            
            <div class="feature-cards">
                <div class="feature-card">
                    <div class="feature-icon purple">📊</div>
                    <div class="feature-text">
                        <h4>Real-time Analytics</h4>
                        <p>Track attendance instantly</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon pink">🔔</div>
                    <div class="feature-text">
                        <h4>Smart Notifications</h4>
                        <p>Automated alerts & reminders</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon blue">📱</div>
                    <div class="feature-text">
                        <h4>Mobile Compatible</h4>
                        <p>Access from any device</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon orange">🔒</div>
                    <div class="feature-text">
                        <h4>Secure Access</h4>
                        <a href="superadmin/index.php"><p>Super Admin</p></a>
                    </div>
                </div>
            </div>

            <div class="divider">Powered By</div>

            <div class="dev-section">
                <p class="dev-title">💻 Intellectual properties owned by</p>
                <div class="dev-links">
                    <a href="https://himanshufullstackdeveloper.github.io/hp3/" target="_blank" class="dev-link">
                        <span class="dev-icon">✨</span>
                        <span class="dev-info">
                            <span class="dev-name">HP3 Technologies</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2>🔐 Secure Login</h2>
                    <p>Multi-layer security authentication</p>
                </div>

                <!-- Live Clock -->
                <div class="live-clock">
                    <div class="clock-segment">
                        <div class="value" id="hours">00</div>
                        <div class="label">Hours</div>
                    </div>
                    <div class="clock-segment">
                        <div class="value" id="minutes">00</div>
                        <div class="label">Minutes</div>
                    </div>
                    <div class="clock-segment">
                        <div class="value" id="seconds">00</div>
                        <div class="label">Seconds</div>
                    </div>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <span>❌</span>
                        <?php 
                            switch($_GET['error']) {
                                case 'invalid':
                                    echo "Invalid username or password!";
                                    break;
                                case 'csrf':
                                    echo "Security token mismatch. Please try again.";
                                    break;
                                case 'captcha':
                                    echo "Incorrect CAPTCHA. Please try again.";
                                    break;
                                case 'rate_limit':
                                    $wait = isset($_GET['wait']) ? $_GET['wait'] : 15;
                                    echo "Too many failed attempts! Please wait $wait minutes.";
                                    break;
                                case 'unauthorized':
                                    echo "Unauthorized access!";
                                    break;
                                default:
                                    echo "An error occurred. Please try again.";
                            }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] === 'logout'): ?>
                    <div class="alert alert-success">
                        <span>✅</span>
                        Logged out successfully!
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST" class="login-form" id="loginForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label>Login As</label>
                        <div class="input-wrapper">
                            <select name="role" id="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin">👨‍💼 Admin</option>
                                <option value="hod">👔 HOD</option>
                                <option value="teacher">👨‍🏫 Teacher</option>
                                <option value="student">👨‍🎓 Student</option>
                                <option value="parent">👨‍👩‍👦 Parent</option>
                            </select>
                            <span class="input-icon">👤</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Username / Roll Number / Email</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" placeholder="Enter your username" required autocomplete="username">
                            <span class="input-icon">📧</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
                            <span class="input-icon">🔑</span>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <span id="eyeIcon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <!-- CAPTCHA -->
                    <div class="form-group captcha-group">
                        <label>🤖 Security Verification (CAPTCHA)</label>
                        <div class="captcha-container">
                            <img src="captcha.php" alt="CAPTCHA" id="captchaImage" class="captcha-image">
                            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()" title="Refresh CAPTCHA">
                                🔄
                            </button>
                        </div>
                        <div class="input-wrapper">
                            <input type="text" name="captcha" id="captcha" placeholder="Enter characters shown above" required autocomplete="off" maxlength="6">
                            <span class="input-icon">🔐</span>
                        </div>
                        <small class="captcha-help">Enter the 6 characters displayed in the image above</small>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">🔐 Secure Sign In</span>
                    </button>
                </form>

                <div class="login-footer">
                    <a href="#" class="forgot-password">
                        <span>📧</span> Forgot password? Contact administrator
                    </a>
                </div>

                <!-- Mobile Developer Section -->
                <div class="mobile-dev-section">
                    <div class="divider">Powered By</div>
                    <div class="dev-section">
                        <p class="dev-title"> 💻 Intellectual property owned by</p>
                        <div class="dev-links">
                            <a href="https://himanshufullstackdeveloper.github.io/hp3/" target="_blank" class="dev-link">
                                <span class="dev-icon">✨</span>
                                <span class="dev-info">
                                    <span class="dev-name">HP3 Technologies</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="login-script.js"></script>
    <script>
        // Create Stars
function createStars() {
    const starsContainer = document.getElementById('stars');
    const starCount = 80;

    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        
        const size = Math.random() * 2 + 0.5;
        star.style.width = size + 'px';
        star.style.height = size + 'px';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDelay = Math.random() * 3 + 's';
        
        starsContainer.appendChild(star);
    }
}

// Live Clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    document.getElementById('hours').textContent = hours;
    document.getElementById('minutes').textContent = minutes;
    document.getElementById('seconds').textContent = seconds;
}

// Password Toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        eyeIcon.textContent = '👁️';
    }
}

// Refresh CAPTCHA
function refreshCaptcha() {
    const captchaImage = document.getElementById('captchaImage');
    captchaImage.src = 'captcha.php?' + Date.now();
    
    // Clear captcha input
    document.getElementById('captcha').value = '';
    
    // Add rotation animation
    const refreshBtn = event.target.closest('.captcha-refresh');
    refreshBtn.style.transform = 'rotate(360deg) scale(1.1)';
    setTimeout(() => {
        refreshBtn.style.transform = '';
    }, 300);
}

// Form Submit Loading
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    const captchaInput = document.getElementById('captcha').value.trim();
    
    // Validate CAPTCHA before submit
    if (captchaInput.length !== 6) {
        e.preventDefault();
        alert('Please enter the 6-character CAPTCHA code');
        return false;
    }
    
    btn.classList.add('loading');
});

// Welcome Message with Multiple Variations
function playWelcomeMessage() {
    const messages = [
        "Welcome to NIT AMMS - Nagpur Institute Of Technology Asset and Maintenance Management System. Please sign in to continue.",
        "Hello! Welcome to our secure attendance portal. Your login is protected with multiple security layers.",
        "Greetings! This is the Nagpur Institute of Technology Attendance Management System. Sign in now with secure authentication.",
        "Welcome! Access your attendance records through our multi-layer security protected dashboard.",
        "NIT AMMS - A secure and reliable system developed by HP3 Technologies. Please login to continue."
    ];

    const randomMessage = messages[Math.floor(Math.random() * messages.length)];
    
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(randomMessage);
        utterance.rate = 1;
        utterance.pitch = 1;
        utterance.volume = 1;

        const voices = window.speechSynthesis.getVoices();
        if (voices.length > 0) {
            const englishVoice = voices.find(v => v.lang.includes('en'));
            utterance.voice = englishVoice || voices[0];
        }

        const audioIndicator = document.getElementById('audioIndicator');
        audioIndicator.classList.add('playing');

        utterance.onend = () => audioIndicator.classList.remove('playing');

        speechSynthesis.speak(utterance);
    }
}

// Audio indicator click handler
document.getElementById('audioIndicator').addEventListener('click', function(e) {
    e.preventDefault();
    playWelcomeMessage();
});

// Parallax Effect on Mouse Move (Desktop only)
if (window.innerWidth > 768) {
    document.addEventListener('mousemove', function(e) {
        const orbs = document.querySelectorAll('.orb');
        const moveX = (e.clientX - window.innerWidth / 2) * 0.02;
        const moveY = (e.clientY - window.innerHeight / 2) * 0.02;

        orbs.forEach((orb, index) => {
            const factor = (index + 1) * 0.5;
            orb.style.transform = `translate(${moveX * factor}px, ${moveY * factor}px)`;
        });
    });
}

// Auto-focus on role select on page load
window.addEventListener('load', function() {
    document.getElementById('role').focus();
});

// CAPTCHA auto-refresh on page load
window.addEventListener('load', function() {
    refreshCaptcha();
});

// Security: Disable right-click on CAPTCHA image
document.getElementById('captchaImage').addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

// Security: Prevent CAPTCHA image drag
document.getElementById('captchaImage').addEventListener('dragstart', function(e) {
    e.preventDefault();
    return false;
});

// Initialize
createStars();
updateClock();
setInterval(updateClock, 1000);

// Play welcome message when page loads
window.addEventListener('load', function() {
    window.speechSynthesis.onvoiceschanged = function() {
        setTimeout(playWelcomeMessage, 500);
    };
    setTimeout(playWelcomeMessage, 1000);
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const role = document.getElementById('role').value;
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const captcha = document.getElementById('captcha').value.trim();
    
    if (!role) {
        e.preventDefault();
        alert('Please select your role');
        return false;
    }
    
    if (!username) {
        e.preventDefault();
        alert('Please enter your username');
        return false;
    }
    
    if (!password) {
        e.preventDefault();
        alert('Please enter your password');
        return false;
    }
    
    if (captcha.length !== 6) {
        e.preventDefault();
        alert('Please enter the 6-character CAPTCHA code');
        return false;
    }
});

// Auto-clear error messages after 5 seconds
window.addEventListener('load', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
    </script>
</body>
</html>