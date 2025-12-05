<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nit_student_attendance');

// Security Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes in seconds
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('CSRF_TOKEN_LENGTH', 32);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Set MySQL timezone
$conn->query("SET time_zone = '+05:30'");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Security: Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// ============================================
// SECURITY FUNCTION: Sanitize Input
// ============================================
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = $conn->real_escape_string($data);
    return $data;
}

// ============================================
// SECURITY FUNCTION: Check if user is logged in
// ============================================
function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        if ($inactive_time > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// ============================================
// SECURITY FUNCTION: Check user role
// ============================================
function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../index.php?error=unauthorized");
        exit();
    }
}

// ============================================
// SECURITY FUNCTION: Get current user info
// ============================================
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    if ($role === 'student') {
        $query = "SELECT * FROM students WHERE id = ?";
    } elseif ($role === 'parent') {
        $query = "SELECT * FROM parents WHERE id = ?";
    } else {
        $query = "SELECT * FROM users WHERE id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// ============================================
// SECURITY FUNCTION: Generate CSRF Token
// ============================================
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

// ============================================
// SECURITY FUNCTION: Verify CSRF Token
// ============================================
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// SECURITY FUNCTION: Log Security Event
// ============================================
function logSecurityEvent($event_type, $user_id = null, $username = null, $description = '', $severity = 'medium') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO security_events (event_type, user_id, username, ip_address, description, severity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $event_type, $user_id, $username, $ip_address, $description, $severity);
    $stmt->execute();
}

// ============================================
// SECURITY FUNCTION: Check Account Lock Status
// ============================================
function isAccountLocked($user_id, $role) {
    global $conn;
    
    if ($role === 'student') {
        $table = 'students';
    } elseif ($role === 'parent') {
        $table = 'parents';
    } else {
        $table = 'users';
    }
    
    $query = "SELECT account_locked, lock_until FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['account_locked'] == 1) {
            if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
                return true; // Still locked
            } else {
                // Unlock account if lock period expired
                $unlock_query = "UPDATE $table SET account_locked = 0, lock_until = NULL, failed_attempts = 0 WHERE id = ?";
                $unlock_stmt = $conn->prepare($unlock_query);
                $unlock_stmt->bind_param("i", $user_id);
                $unlock_stmt->execute();
                return false;
            }
        }
    }
    
    return false;
}

// ============================================
// SECURITY FUNCTION: Increment Failed Attempts
// ============================================
function incrementFailedAttempts($user_id, $role) {
    global $conn;
    
    if ($role === 'student') {
        $table = 'students';
    } elseif ($role === 'parent') {
        $table = 'parents';
    } else {
        $table = 'users';
    }
    
    $query = "UPDATE $table SET failed_attempts = failed_attempts + 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Check if should lock account
    $check_query = "SELECT failed_attempts FROM $table WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user['failed_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $lock_until = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $lock_query = "UPDATE $table SET account_locked = 1, lock_until = ? WHERE id = ?";
        $lock_stmt = $conn->prepare($lock_query);
        $lock_stmt->bind_param("si", $lock_until, $user_id);
        $lock_stmt->execute();
        
        logSecurityEvent('account_locked', $user_id, null, "Account locked after " . MAX_LOGIN_ATTEMPTS . " failed attempts", 'high');
    }
}

// ============================================
// SECURITY FUNCTION: Reset Failed Attempts
// ============================================
function resetFailedAttempts($user_id, $role) {
    global $conn;
    
    if ($role === 'student') {
        $table = 'students';
    } elseif ($role === 'parent') {
        $table = 'parents';
    } else {
        $table = 'users';
    }
    
    $query = "UPDATE $table SET failed_attempts = 0, account_locked = 0, lock_until = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// ============================================
// SECURITY FUNCTION: Get Client IP Address
// ============================================
function getClientIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'UNKNOWN';
}

// ============================================
// SECURITY FUNCTION: Validate Password Strength
// ============================================
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

// ============================================
// SECURITY FUNCTION: Prevent Session Fixation
// ============================================
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// ============================================
// SECURITY: Auto-logout on browser close
// ============================================
if (isset($_SESSION['user_id']) && !isset($_SESSION['session_start'])) {
    $_SESSION['session_start'] = time();
}
?>