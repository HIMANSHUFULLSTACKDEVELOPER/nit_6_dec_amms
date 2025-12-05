<?php
/**
 * Database Configuration File
 * Place this file in your project root directory
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');                // Your MySQL password
define('DB_NAME', 'nit_student_attendance'); // Your database name

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper encoding
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('Asia/Kolkata');

// Base URL for your application (adjust as needed)
define('BASE_URL', 'http://localhost/NIT_28noverber-main/');

// Upload directories
define('HOMEWORK_UPLOAD_DIR', __DIR__ . '/uploads/homework/');
define('SUBMISSION_UPLOAD_DIR', __DIR__ . '/uploads/submissions/');

// Create upload directories if they don't exist
if (!file_exists(HOMEWORK_UPLOAD_DIR)) {
    mkdir(HOMEWORK_UPLOAD_DIR, 0777, true);
}
if (!file_exists(SUBMISSION_UPLOAD_DIR)) {
    mkdir(SUBMISSION_UPLOAD_DIR, 0777, true);
}

// Maximum file upload size (in bytes) - 10MB
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

// Allowed file types for homework uploads
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png']);
?>