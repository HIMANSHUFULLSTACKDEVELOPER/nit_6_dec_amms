<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$assignment_id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Fetch assignment details
$sql = "SELECT ha.*, cs.subject_name, c.class_name 
        FROM homework_assignments ha
        LEFT JOIN class_subjects cs ON ha.subject_id = cs.id
        LEFT JOIN classes c ON ha.class_id = c.id
        WHERE ha.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    header("Location: student_view_homework.php");
    exit();
}

// Check if already submitted
$check_sql = "SELECT id FROM homework_submissions WHERE assignment_id = ? AND student_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $assignment_id, $student_id);
$check_stmt->execute();
$already_submitted = $check_stmt->get_result()->num_rows > 0;
$check_stmt->close();

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $submission_text = trim($_POST['submission_text'] ?? '');
    
    if (empty($submission_text) && empty($_FILES['submission_file']['name'])) {
        $error = "Please provide either a text submission or upload a file!";
    } else {
        $file_name = null;
        $file_path = null;
        $file_type = null;
        $file_size = null;
        
        // Handle file upload
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/submissions/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = $_FILES['submission_file']['name'];
            $file_tmp = $_FILES['submission_file']['tmp_name'];
            $file_size = $_FILES['submission_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt', 'zip'];
            
            if (in_array($file_ext, $allowed)) {
                $unique_name = uniqid() . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $unique_name;
                
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $file_type = $_FILES['submission_file']['type'];
                } else {
                    $error = "Failed to upload file!";
                }
            } else {
                $error = "Invalid file type!";
            }
        }
        
        // Insert submission
        if (empty($error)) {
            // Check if late
            $due_date = strtotime($assignment['due_date']);
            $today = strtotime(date('Y-m-d'));
            $status = ($today > $due_date) ? 'late' : 'submitted';
            
            $insert_sql = "INSERT INTO homework_submissions 
                          (assignment_id, student_id, submission_text, file_name, file_path, 
                           file_type, file_size, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iissssss", $assignment_id, $student_id, $submission_text,
                                    $file_name, $file_path, $file_type, $file_size, $status);
            
            if ($insert_stmt->execute()) {
                $message = "Homework submitted successfully!";
                $already_submitted = true;
            } else {
                $error = "Error submitting homework: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Homework</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .assignment-info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .info-row { margin-bottom: 10px; }
        .info-label { font-weight: bold; color: #555; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; resize: vertical; min-height: 150px; }
        input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <a href="student_view_homework.php" class="back-link">← Back to Homework List</a>
        <h2>Submit Homework</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="assignment-info">
            <div class="info-row">
                <span class="info-label">Title:</span> <?php echo htmlspecialchars($assignment['title']); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Subject:</span> <?php echo htmlspecialchars($assignment['subject_name']); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Class:</span> <?php echo htmlspecialchars($assignment['class_name']); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Due Date:</span> <?php echo date('d M Y', strtotime($assignment['due_date'])); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Total Marks:</span> <?php echo $assignment['total_marks']; ?>
            </div>
            <div class="info-row">
                <span class="info-label">Description:</span><br>
                <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
            </div>
            <?php if ($assignment['file_path']): ?>
            <div class="info-row">
                <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download style="color: #007bff;">📥 Download Assignment File</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$already_submitted): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Your Answer/Solution</label>
                <textarea name="submission_text" placeholder="Type your answer here..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Upload File (Optional)</label>
                <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.zip">
                <small style="color: #666;">Allowed: PDF, DOC, DOCX, JPG, PNG, TXT, ZIP (Max 10MB)</small>
            </div>
            
            <button type="submit">Submit Homework</button>
        </form>
        <?php else: ?>
            <div class="alert alert-success">
                ✓ You have already submitted this homework!
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>