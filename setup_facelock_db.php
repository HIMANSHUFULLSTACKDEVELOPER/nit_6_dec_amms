<?php
require 'db.php';

// SQL to create facelock_users table
$sql = "CREATE TABLE IF NOT EXISTS facelock_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    face_photo LONGBLOB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ FaceLock database table created successfully!<br>";
    echo "Table: facelock_users<br>";
    echo "<br>";
    echo "Now you can use the FaceLock system at: <a href='facelock.php'>facelock.php</a>";
} else {
    echo "❌ Error creating table: " . $conn->error;
}

$conn->close();
?>