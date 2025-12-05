<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

// Check if user is logged in
if(!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Get user type and ID from POST
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Validate user
if($user_type !== $_SESSION['role'] || $user_id !== $_SESSION['user_id']) {
    echo "<script>alert('Unauthorized access!'); window.location.href='index.php';</script>";
    exit();
}

// Handle photo upload
if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $target_dir = "uploads/student_photos/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Validate file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    
    if(!in_array($file_extension, $allowed_types)) {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'); window.history.back();</script>";
        exit();
    }
    
    // Validate file size (max 5MB)
    if($_FILES["photo"]["size"] > 5000000) {
        echo "<script>alert('File is too large. Maximum size is 5MB.'); window.history.back();</script>";
        exit();
    }
    
    // Check if file is an actual image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if($check === false) {
        echo "<script>alert('File is not an image.'); window.history.back();</script>";
        exit();
    }
    
    // Generate new filename
    $new_filename = $user_type . "_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Delete old photo if exists
    if($user_type === 'student') {
        $old_photo_query = "SELECT photo FROM students WHERE id = $user_id";
        $old_photo_result = mysqli_query($conn, $old_photo_query);
        if($old_photo_result && mysqli_num_rows($old_photo_result) > 0) {
            $old_photo_row = mysqli_fetch_assoc($old_photo_result);
            if(!empty($old_photo_row['photo'])) {
                $old_photo_path = $target_dir . $old_photo_row['photo'];
                if(file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
        }
    }
    
    // Upload file
    if(move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        // Update database based on user type
        if($user_type === 'student') {
            $update_query = "UPDATE students SET photo = ? WHERE id = ?";
        } elseif($user_type === 'hod') {
            $update_query = "UPDATE hods SET photo = ? WHERE id = ?";
        } elseif($user_type === 'parent') {
            $update_query = "UPDATE parents SET photo = ? WHERE id = ?";
        } else {
            echo "<script>alert('Invalid user type!'); window.history.back();</script>";
            exit();
        }
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Photo uploaded successfully!'); window.location.href='student/view_exam_schedule.php';</script>";
        } else {
            echo "<script>alert('Database update failed!'); window.history.back();</script>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Failed to upload photo. Please try again.'); window.history.back();</script>";
    }
} else {
    $error_message = isset($_FILES['photo']) ? $_FILES['photo']['error'] : 'No file uploaded';
    echo "<script>alert('Error uploading file: " . $error_message . "'); window.history.back();</script>";
}

mysqli_close($conn);
?>