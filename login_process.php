<?php
require_once 'db.php';

// Enable error reporting for debugging (Remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security: Prevent direct access if not POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// ============================================
// SECURITY FEATURE 1: CSRF TOKEN VALIDATION
// ============================================
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: index.php?error=csrf");
    exit();
}

// ============================================
// SECURITY FEATURE 2: RATE LIMITING
// ============================================
$ip_address = $_SERVER['REMOTE_ADDR'];
$current_time = time();

// Initialize rate limiting if not exists
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

// Clean old attempts (older than 15 minutes)
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 900; // 15 minutes
});

// Check if too many attempts
if (count($_SESSION['login_attempts']) >= 5) {
    $wait_time = 900 - ($current_time - min($_SESSION['login_attempts']));
    header("Location: index.php?error=rate_limit&wait=" . ceil($wait_time / 60));
    exit();
}

// ============================================
// SECURITY FEATURE 3: CAPTCHA VALIDATION
// ============================================
if (!isset($_POST['captcha']) || !isset($_SESSION['captcha_answer'])) {
    $_SESSION['login_attempts'][] = $current_time;
    header("Location: index.php?error=captcha");
    exit();
}

if (strtoupper($_POST['captcha']) !== strtoupper($_SESSION['captcha_answer'])) {
    $_SESSION['login_attempts'][] = $current_time;
    unset($_SESSION['captcha_answer']);
    header("Location: index.php?error=captcha");
    exit();
}

// Clear captcha after use
unset($_SESSION['captcha_answer']);

// ============================================
// SECURITY FEATURE 4: INPUT VALIDATION & SANITIZATION
// ============================================
$role = isset($_POST['role']) ? sanitize($_POST['role']) : '';
$username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password

// Validate required fields
if (empty($role) || empty($username) || empty($password)) {
    $_SESSION['login_attempts'][] = $current_time;
    header("Location: index.php?error=invalid");
    exit();
}

// Validate role
$allowed_roles = ['admin', 'hod', 'teacher', 'student', 'parent'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['login_attempts'][] = $current_time;
    header("Location: index.php?error=invalid");
    exit();
}

// ============================================
// SECURITY FEATURE 5: LOG FAILED ATTEMPTS
// ============================================
function logLoginAttempt($conn, $username, $role, $ip, $success) {
    try {
        // Check if table exists, if not create it
        $check_table = $conn->query("SHOW TABLES LIKE 'login_attempts'");
        if ($check_table->num_rows == 0) {
            $conn->query("CREATE TABLE login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255),
                role VARCHAR(50),
                ip_address VARCHAR(45),
                attempt_time DATETIME,
                status VARCHAR(20),
                reason VARCHAR(255)
            )");
        }
        
        $status = $success ? 'success' : 'failed';
        $reason = $success ? 'Login successful' : 'Invalid credentials';
        $stmt = $conn->prepare("INSERT INTO login_attempts (username, role, ip_address, attempt_time, status, reason) VALUES (?, ?, ?, NOW(), ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $username, $role, $ip, $status, $reason);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Silently fail - logging should not break login
        error_log("Login attempt logging failed: " . $e->getMessage());
    }
}

// ============================================
// LOGIN LOGIC BY ROLE
// ============================================
$login_successful = false;
$user_data = null;
$redirect = '';

try {
    if ($role === 'student') {
        $query = "SELECT * FROM students WHERE (roll_number = ? OR email = ?) LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $login_successful = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'student';
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['roll_number'] = $user['roll_number'];
                $_SESSION['department_id'] = isset($user['department_id']) ? $user['department_id'] : null;
                $_SESSION['class_id'] = isset($user['class_id']) ? $user['class_id'] : null;
                $_SESSION['last_activity'] = time();
                $redirect = "student/index.php";
            }
        }
        $stmt->close();
    } 
    elseif ($role === 'parent') {
        $query = "SELECT * FROM parents WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $login_successful = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'parent';
                $_SESSION['parent_name'] = $user['parent_name'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['last_activity'] = time();
                $redirect = "parent/index.php";
            }
        }
        $stmt->close();
    } 
    else {
        // Admin, HOD, Teacher
        $query = "SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $login_successful = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['last_activity'] = time();
                
                if ($role === 'hod' || $role === 'teacher') {
                    $_SESSION['department_id'] = isset($user['department_id']) ? $user['department_id'] : null;
                }
                $redirect = "$role/index.php";
            }
        }
        $stmt->close();
    }

    // ============================================
    // HANDLE LOGIN RESULT
    // ============================================
    if ($login_successful) {
        // Clear failed attempts
        $_SESSION['login_attempts'] = [];
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Log successful login
        logLoginAttempt($conn, $username, $role, $ip_address, true);
        
        // Update last login time (optional - will fail silently if column doesn't exist)
        try {
            if ($role === 'student') {
                $update_stmt = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = ?");
            } elseif ($role === 'parent') {
                $update_stmt = $conn->prepare("UPDATE parents SET last_login = NOW() WHERE id = ?");
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            }
            
            if ($update_stmt) {
                $update_stmt->bind_param("i", $_SESSION['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        } catch (Exception $e) {
            // Ignore if last_login column doesn't exist
            error_log("Last login update failed: " . $e->getMessage());
        }
        
        header("Location: " . $redirect);
        exit();
    } else {
        // Record failed attempt
        $_SESSION['login_attempts'][] = $current_time;
        logLoginAttempt($conn, $username, $role, $ip_address, false);
        
        header("Location: index.php?error=invalid");
        exit();
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Login process error: " . $e->getMessage());
    
    // Record failed attempt
    $_SESSION['login_attempts'][] = $current_time;
    
    // Redirect with error
    header("Location: index.php?error=invalid");
    exit();
}

$conn->close();
?>