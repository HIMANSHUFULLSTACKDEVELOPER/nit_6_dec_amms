<?php

require_once '../db.php';
checkRole(['teacher']);

$user = getCurrentUser();
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Get academic year
$academic_year = '';
if (isset($_GET['academic_year'])) {
    $academic_year = sanitize($_GET['academic_year']);
} else {
    $year_query = "SELECT academic_year FROM classes WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($year_query);
    $stmt->bind_param("ii", $class_id, $user['id']);
    $stmt->execute();
    $year_result = $stmt->get_result()->fetch_assoc();
    $academic_year = $year_result['academic_year'] ?? '';
}

// Handle message sending via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    header('Content-Type: application/json');
    
    $student_id = intval($_POST['student_id']);
    $student_name = sanitize($_POST['student_name']);
    $student_email = sanitize($_POST['student_email']);
    $message = sanitize($_POST['message']);
    $date = sanitize($_POST['date']);
    
    // Check if student_notifications table exists
    $check_table = "SHOW TABLES LIKE 'student_notifications'";
    $result = $conn->query($check_table);
    
    if ($result->num_rows == 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS student_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            teacher_id INT NOT NULL,
            class_id INT NOT NULL,
            message TEXT NOT NULL,
            notification_date DATE NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$conn->query($create_table)) {
            echo json_encode(['success' => false, 'error' => 'Failed to create table']);
            exit();
        }
    }
    
    // Insert message
    $insert_msg = "INSERT INTO student_notifications 
                   (student_id, teacher_id, class_id, message, notification_date) 
                   VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_msg);
    $stmt->bind_param("iiiss", $student_id, $user['id'], $class_id, $message, $date);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save message']);
    }
    exit();
}

// Verify teacher access
$verify_query = "SELECT c.* FROM classes c WHERE c.id = ? AND c.teacher_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $class_id, $user['id']);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    header("Location: index.php");
    exit();
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $attendance_date = sanitize($_POST['attendance_date']);
    $attendance_data = $_POST['attendance'] ?? [];
    
    $success_count = 0;
    
    foreach ($attendance_data as $student_id => $status) {
        $student_id = intval($student_id);
        $status = sanitize($status);
        
        // Check if exists
        $check_query = "SELECT id FROM student_attendance 
                       WHERE student_id = ? AND class_id = ? AND attendance_date = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("iis", $student_id, $class_id, $attendance_date);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            $update_query = "UPDATE student_attendance 
                           SET status = ?, marked_by = ?, marked_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sii", $status, $user['id'], $existing['id']);
            if ($update_stmt->execute()) $success_count++;
        } else {
            $insert_query = "INSERT INTO student_attendance 
                           (student_id, class_id, attendance_date, status, marked_by) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iissi", $student_id, $class_id, $attendance_date, $status, $user['id']);
            if ($insert_stmt->execute()) $success_count++;
        }
    }
    
    if ($success_count > 0) {
        $success = "✅ Attendance saved successfully! ($success_count students)";
    }
}

// Get date
$attendance_date = isset($_GET['date']) ? sanitize($_GET['date']) : 
                  (isset($_POST['attendance_date']) ? sanitize($_POST['attendance_date']) : date('Y-m-d'));

// Calculate last 3 days
$last_3_days = [];
for ($i = 1; $i <= 3; $i++) {
    $last_3_days[] = date('Y-m-d', strtotime($attendance_date . " -$i day"));
}

// Get students
$students_query = "SELECT s.*, sa.status as today_status
                   FROM students s
                   LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                       AND sa.attendance_date = ? AND sa.class_id = ?
                   INNER JOIN classes c ON s.class_id = c.id
                   WHERE c.section = ? AND c.year = ? AND c.semester = ? 
                   AND c.academic_year = ? AND s.is_active = 1
                   ORDER BY s.roll_number";

$stmt = $conn->prepare($students_query);
$stmt->bind_param("sissss", $attendance_date, $class_id, $class['section'], 
                  $class['year'], $class['semester'], $class['academic_year']);
$stmt->execute();
$students = $stmt->get_result();

// Calculate stats
$total_students = 0;
$present_count = 0;
$absent_count = 0;
$late_count = 0;

$students_array = [];
while ($student = $students->fetch_assoc()) {
    $students_array[] = $student;
    $total_students++;
    
    if ($student['today_status'] == 'present') $present_count++;
    elseif ($student['today_status'] == 'absent') $absent_count++;
    elseif ($student['today_status'] == 'late') $late_count++;
}

$present_percentage = $total_students > 0 ? round(($present_count / $total_students) * 100, 1) : 0;
$absent_percentage = $total_students > 0 ? round(($absent_count / $total_students) * 100, 1) : 0;
$late_percentage = $total_students > 0 ? round(($late_count / $total_students) * 100, 1) : 0;

// Get last 3 days attendance for all students
$last_3_days_attendance = [];
$placeholders = implode(',', array_fill(0, count($last_3_days), '?'));
$history_query = "SELECT student_id, attendance_date, status 
                  FROM student_attendance 
                  WHERE class_id = ? AND attendance_date IN ($placeholders)
                  ORDER BY attendance_date DESC";

$history_stmt = $conn->prepare($history_query);
$params = array_merge([$class_id], $last_3_days);
$types = str_repeat('s', count($last_3_days) + 1);
$types[0] = 'i';
$history_stmt->bind_param($types, ...$params);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

while ($row = $history_result->fetch_assoc()) {
    $last_3_days_attendance[$row['student_id']][$row['attendance_date']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mark Attendance - <?php echo htmlspecialchars($class['section']); ?></title>
    <link rel="stylesheet" href="mark_attendance_mobile.css">
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml">
    <style>
        /* Mobile-First Responsive Design for Attendance System */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-tap-highlight-color: transparent;
}

:root {
    --primary-color: #667eea;
    --primary-dark: #764ba2;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    --card-bg: rgba(255, 255, 255, 0.98);
    --text-dark: #2c3e50;
    --text-light: #666;
    --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.15);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: var(--bg-gradient);
    background-attachment: fixed;
    min-height: 100vh;
    padding-bottom: 80px;
}

/* Mobile Header */
.mobile-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(26, 31, 58, 0.98);
    backdrop-filter: blur(20px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
    padding: 12px 16px;
}

.header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.back-btn,
.logout-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.back-btn:active,
.logout-btn:active {
    transform: scale(0.95);
    background: rgba(255, 255, 255, 0.2);
}

.header-title {
    flex: 1;
    text-align: center;
    min-width: 0;
}

.header-title h1 {
    color: white;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 2px;
}

.header-title p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
}

.header-info {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 11px;
    flex-wrap: wrap;
}

.teacher-name,
.academic-year {
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.1);
    padding: 6px 10px;
    border-radius: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    text-align: center;
    min-width: 0;
}

/* Main Container */
.main-container {
    padding: 12px;
    max-width: 100%;
}

/* Alert Messages */
.alert {
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 12px;
    font-size: 13px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: rgba(212, 237, 218, 0.95);
    border-left: 4px solid var(--success-color);
    color: #155724;
}

.alert-error,
.alert-warning {
    background: rgba(248, 215, 218, 0.95);
    border-left: 4px solid var(--danger-color);
    color: #721c24;
}

/* Date Card */
.date-card {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 12px;
    box-shadow: var(--shadow);
}

.date-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.date-nav-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border: none;
    border-radius: 12px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.date-nav-btn:active {
    transform: scale(0.95);
}

.date-nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.date-display {
    flex: 1;
    text-align: center;
    min-width: 0;
}

.date-display input[type="date"] {
    width: 100%;
    padding: 10px 8px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    background: white;
}

.date-display label {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    color: var(--text-light);
    font-weight: 500;
}

.today-btn {
    width: 100%;
    padding: 10px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.today-btn:active {
    transform: scale(0.98);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}

.stat-card {
    background: var(--card-bg);
    border-radius: 14px;
    padding: 14px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.stat-card:active {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    font-size: 28px;
    margin-bottom: 6px;
}

.stat-value {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 2px;
}

.stat-label {
    font-size: 11px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-percent {
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
}

.stat-present {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
}

.stat-present .stat-value,
.stat-present .stat-percent {
    color: var(--success-color);
}

.stat-absent {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(231, 76, 60, 0.1));
}

.stat-absent .stat-value,
.stat-absent .stat-percent {
    color: var(--danger-color);
}

.stat-late {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(243, 156, 18, 0.1));
}

.stat-late .stat-value,
.stat-late .stat-percent {
    color: var(--warning-color);
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.quick-btn {
    padding: 10px 6px;
    border: none;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.quick-btn:active {
    transform: scale(0.95);
}

.quick-present {
    background: linear-gradient(135deg, var(--success-color), #20c997);
}

.quick-absent {
    background: linear-gradient(135deg, var(--danger-color), #e74c3c);
}

.quick-late {
    background: linear-gradient(135deg, var(--warning-color), #f39c12);
    color: #000;
}

/* Students List */
.students-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.student-card {
    background: var(--card-bg);
    border-radius: 14px;
    padding: 14px;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    gap: 8px;
}

.student-info {
    flex: 1;
    min-width: 0;
}

.roll-number {
    display: inline-block;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    margin-bottom: 6px;
}

.student-info h3 {
    font-size: 15px;
    color: var(--text-dark);
    margin-bottom: 4px;
    word-wrap: break-word;
}

.student-info p {
    font-size: 11px;
    color: var(--text-light);
    word-wrap: break-word;
}

/* Attendance History - Last 3 Days */
.attendance-history {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.history-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 4px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.history-icon {
    font-size: 14px;
    margin-bottom: 2px;
}

.history-label {
    font-size: 8px;
    font-weight: 600;
    text-transform: uppercase;
}

.history-present {
    background: rgba(40, 167, 69, 0.15);
    color: var(--success-color);
}

.history-absent {
    background: rgba(220, 53, 69, 0.15);
    color: var(--danger-color);
}

.history-late {
    background: rgba(255, 193, 7, 0.15);
    color: #856404;
}

.history-unmarked {
    background: rgba(0, 0, 0, 0.05);
    color: #999;
}

.history-day:active {
    transform: scale(0.95);
}

/* Attendance Buttons */
.attendance-buttons {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 10px;
}

.att-btn {
    position: relative;
    cursor: pointer;
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 10px 6px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: white;
}

.att-btn input[type="radio"] {
    display: none;
}

.att-btn:active {
    transform: scale(0.97);
}

.att-present.active {
    background: linear-gradient(135deg, var(--success-color), #20c997);
    border-color: var(--success-color);
    color: white;
}

.att-absent.active {
    background: linear-gradient(135deg, var(--danger-color), #e74c3c);
    border-color: var(--danger-color);
    color: white;
}

.att-late.active {
    background: linear-gradient(135deg, var(--warning-color), #f39c12);
    border-color: var(--warning-color);
    color: #000;
}

/* Message Button */
.message-btn {
    width: 100%;
    padding: 10px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.message-btn:active {
    transform: scale(0.98);
}

.message-btn.sent {
    background: linear-gradient(135deg, var(--success-color), #20c997);
    cursor: not-allowed;
}

/* Floating Save Button */
.floating-save {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 10px 12px;
    background: rgba(26, 31, 58, 0.98);
    backdrop-filter: blur(20px);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.2);
    z-index: 90;
}

.save-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

.save-btn:active {
    transform: scale(0.98);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 200;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-radius: 24px 24px 0 0;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-header h2 {
    font-size: 16px;
    color: var(--text-dark);
}

.close-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.close-btn:active {
    transform: scale(0.95);
    background: rgba(0, 0, 0, 0.1);
}

.modal-body {
    padding: 16px;
}

.student-details {
    background: rgba(102, 126, 234, 0.1);
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.student-details p {
    margin: 4px 0;
    font-size: 13px;
    color: var(--text-dark);
    word-wrap: break-word;
}

.templates {
    margin-bottom: 12px;
}

.templates label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.template-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
}

.template-buttons button {
    padding: 8px;
    background: rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.template-buttons button:active {
    transform: scale(0.97);
    background: rgba(102, 126, 234, 0.2);
}

#messageText {
    width: 100%;
    padding: 12px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 12px;
    font-size: 13px;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
}

#messageText:focus {
    outline: none;
    border-color: var(--primary-color);
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.btn-cancel,
.btn-send {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel {
    background: rgba(0, 0, 0, 0.05);
    color: var(--text-dark);
}

.btn-send {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
}

.btn-cancel:active,
.btn-send:active {
    transform: scale(0.98);
}

/* Small Mobile Devices (iPhone SE, etc.) */
@media (max-width: 375px) {
    .header-title h1 {
        font-size: 14px;
    }
    
    .header-title p {
        font-size: 11px;
    }
    
    .header-info {
        font-size: 10px;
    }
    
    .teacher-name,
    .academic-year {
        padding: 5px 8px;
    }
    
    .stat-icon {
        font-size: 24px;
    }
    
    .stat-value {
        font-size: 20px;
    }
    
    .stat-label {
        font-size: 10px;
    }
    
    .quick-btn {
        font-size: 11px;
        padding: 8px 4px;
    }
    
    .student-info h3 {
        font-size: 14px;
    }
    
    .att-btn {
        font-size: 11px;
        padding: 8px 4px;
    }
    
    .history-day {
        min-width: 28px;
    }
    
    .history-icon {
        font-size: 12px;
    }
}

/* Tablet Screens */
@media (min-width: 768px) {
    .main-container {
        max-width: 768px;
        margin: 0 auto;
        padding: 20px;
    }

    .header-title h1 {
        font-size: 20px;
    }

    .header-title p {
        font-size: 14px;
    }

    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .stat-card {
        padding: 18px;
    }

    .stat-icon {
        font-size: 36px;
    }

    .stat-value {
        font-size: 28px;
    }

    .quick-actions {
        max-width: 600px;
        margin: 0 auto 20px;
        gap: 12px;
    }

    .quick-btn {
        padding: 12px 16px;
        font-size: 14px;
    }

    .students-list {
        gap: 14px;
    }

    .student-card {
        padding: 18px;
    }

    .student-info h3 {
        font-size: 17px;
    }

    .att-btn {
        padding: 12px 8px;
        font-size: 13px;
    }
    
    .history-day {
        min-width: 38px;
        padding: 6px;
    }
    
    .history-icon {
        font-size: 16px;
    }
    
    .history-label {
        font-size: 9px;
    }

    .modal-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        bottom: auto;
        right: auto;
        max-width: 500px;
        border-radius: 24px;
        animation: scaleIn 0.3s ease;
    }

    @keyframes scaleIn {
        from {
            transform: translate(-50%, -50%) scale(0.9);
            opacity: 0;
        }
        to {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
    }
}

/* Desktop Screens */
@media (min-width: 1024px) {
    body {
        padding-bottom: 0;
    }

    .mobile-header {
        padding: 20px 40px;
    }

    .header-top {
        max-width: 1400px;
        margin: 0 auto;
    }

    .header-title h1 {
        font-size: 24px;
    }

    .header-title p {
        font-size: 16px;
    }

    .header-info {
        max-width: 1400px;
        margin: 12px auto 0;
        font-size: 14px;
    }

    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px;
    }

    .date-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 32px;
    }

    .date-controls {
        margin-bottom: 0;
        flex: 1;
        max-width: 600px;
    }

    .date-nav-btn {
        width: 50px;
        height: 50px;
    }

    .today-btn {
        width: auto;
        min-width: 150px;
    }

    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .stat-card {
        padding: 28px;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .stat-icon {
        font-size: 42px;
    }

    .stat-value {
        font-size: 36px;
    }

    .quick-actions {
        display: flex;
        justify-content: center;
        gap: 16px;
        max-width: 800px;
        margin: 0 auto 32px;
    }

    .quick-btn {
        padding: 14px 32px;
        font-size: 15px;
        flex: 1;
        max-width: 250px;
        cursor: pointer;
    }

    .quick-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .students-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .student-card {
        padding: 24px;
        cursor: default;
    }

    .student-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }

    .student-info h3 {
        font-size: 18px;
    }
    
    .history-day {
        min-width: 42px;
        padding: 8px;
    }
    
    .history-icon {
        font-size: 18px;
    }
    
    .history-label {
        font-size: 10px;
    }
    
    .history-day:hover {
        transform: scale(1.1);
        box-shadow: var(--shadow);
    }

    .att-btn {
        padding: 14px 12px;
        font-size: 14px;
        cursor: pointer;
    }

    .att-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .message-btn {
        cursor: pointer;
    }

    .message-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    }

    .floating-save {
        position: static;
        padding: 32px 0;
        background: transparent;
        box-shadow: none;
    }

    .save-btn {
        max-width: 400px;
        margin: 0 auto;
        display: block;
        padding: 18px 48px;
        font-size: 18px;
        cursor: pointer;
    }

    .save-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.5);
    }

    .modal-content {
        max-width: 600px;
        max-height: 85vh;
    }

    .template-buttons {
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .template-buttons button:hover {
        background: rgba(102, 126, 234, 0.2);
        transform: translateY(-2px);
    }
}

/* Large Desktop */
@media (min-width: 1440px) {
    .main-container {
        max-width: 1600px;
        padding: 40px;
    }

    .students-list {
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
}

/* Ultra-Wide Screens */
@media (min-width: 1920px) {
    .students-list {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Loading state */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Print Styles */
@media print {
    .mobile-header,
    .quick-actions,
    .floating-save,
    .message-btn,
    .back-btn,
    .logout-btn,
    .today-btn,
    .date-nav-btn {
        display: none !important;
    }

    body {
        background: white;
    }

    .student-card {
        page-break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .attendance-history {
        border: 1px solid #ddd;
        padding: 4px;
        border-radius: 4px;
    }
}
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="header-top">
            <a href="index.php?academic_year=<?php echo urlencode($class['academic_year']); ?>" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="header-title">
                <h1>📚 Mark Attendance</h1>
                <p><?php echo htmlspecialchars($class['section']); ?></p>
            </div>
            <a href="../logout.php" class="logout-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
            </a>
        </div>
        
        <div class="header-info">
            <span class="teacher-name">👨‍🏫 <?php echo htmlspecialchars($user['full_name']); ?></span>
            <span class="academic-year">📅 <?php echo htmlspecialchars($class['academic_year']); ?></span>
        </div>
    </header>

    <main class="main-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Date Card -->
        <div class="date-card">
            <div class="date-controls">
                <button type="button" onclick="navigateDate(-1)" class="date-nav-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                
                <div class="date-display">
                    <input type="date" id="date_selector" value="<?php echo $attendance_date; ?>" 
                           max="<?php echo date('Y-m-d'); ?>" onchange="changeDate(this.value)">
                    <label><?php echo date('D, M j, Y', strtotime($attendance_date)); ?></label>
                </div>
                
                <button type="button" onclick="navigateDate(1)" class="date-nav-btn"
                        <?php echo ($attendance_date >= date('Y-m-d')) ? 'disabled' : ''; ?>>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
            </div>
            
            <?php if ($attendance_date != date('Y-m-d')): ?>
            <button type="button" onclick="changeDate('<?php echo date('Y-m-d'); ?>')" class="today-btn">
                📅 Today
            </button>
            <?php endif; ?>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?php echo $total_students; ?></div>
                <div class="stat-label">Total</div>
            </div>
            
            <div class="stat-card stat-present">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $present_count; ?></div>
                <div class="stat-label">Present</div>
                <div class="stat-percent"><?php echo $present_percentage; ?>%</div>
            </div>
            
            <div class="stat-card stat-absent">
                <div class="stat-icon">❌</div>
                <div class="stat-value"><?php echo $absent_count; ?></div>
                <div class="stat-label">Absent</div>
                <div class="stat-percent"><?php echo $absent_percentage; ?>%</div>
            </div>
            
            <div class="stat-card stat-late">
                <div class="stat-icon">⏰</div>
                <div class="stat-value"><?php echo $late_count; ?></div>
                <div class="stat-label">Late</div>
                <div class="stat-percent"><?php echo $late_percentage; ?>%</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button type="button" onclick="markAll('present')" class="quick-btn quick-present">
                ✅ All Present
            </button>
            <button type="button" onclick="markAll('absent')" class="quick-btn quick-absent">
                ❌ All Absent
            </button>
            <button type="button" onclick="markAll('late')" class="quick-btn quick-late">
                ⏰ All Late
            </button>
        </div>

        <!-- Students List -->
        <?php if ($total_students > 0): ?>
        <form method="POST" id="attendanceForm" onsubmit="return validateAttendance()">
            <input type="hidden" name="attendance_date" value="<?php echo $attendance_date; ?>">
            
            <div class="students-list">
                <?php foreach ($students_array as $student): ?>
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-info">
                            <span class="roll-number"><?php echo htmlspecialchars($student['roll_number']); ?></span>
                            <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                        
                        <!-- Last 3 Days History -->
                        <div class="attendance-history">
                            <?php 
                            $student_id = $student['id'];
                            foreach ($last_3_days as $index => $date):
                                $status = $last_3_days_attendance[$student_id][$date] ?? 'unmarked';
                                $day_label = date('D', strtotime($date));
                                
                                $status_icon = '';
                                $status_class = '';
                                
                                if ($status == 'present') {
                                    $status_icon = '✅';
                                    $status_class = 'history-present';
                                } elseif ($status == 'absent') {
                                    $status_icon = '❌';
                                    $status_class = 'history-absent';
                                } elseif ($status == 'late') {
                                    $status_icon = '⏰';
                                    $status_class = 'history-late';
                                } else {
                                    $status_icon = '—';
                                    $status_class = 'history-unmarked';
                                }
                            ?>
                            <div class="history-day <?php echo $status_class; ?>" 
                                 title="<?php echo date('M j', strtotime($date)) . ': ' . ucfirst($status); ?>">
                                <div class="history-icon"><?php echo $status_icon; ?></div>
                                <div class="history-label"><?php echo $day_label; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="attendance-buttons">
                        <label class="att-btn att-present <?php echo ($student['today_status'] == 'present') ? 'active' : ''; ?>">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                   value="present" <?php echo ($student['today_status'] == 'present') ? 'checked' : ''; ?>>
                            <span>✅ Present</span>
                        </label>
                        
                        <label class="att-btn att-absent <?php echo ($student['today_status'] == 'absent') ? 'active' : ''; ?>">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                   value="absent" <?php echo ($student['today_status'] == 'absent') ? 'checked' : ''; ?>>
                            <span>❌ Absent</span>
                        </label>
                        
                        <label class="att-btn att-late <?php echo ($student['today_status'] == 'late') ? 'active' : ''; ?>">
                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                   value="late" <?php echo ($student['today_status'] == 'late') ? 'checked' : ''; ?>>
                            <span>⏰ Late</span>
                        </label>
                    </div>
                    
                    <button type="button" class="message-btn" data-student-id="<?php echo $student['id']; ?>"
                            onclick="openMessageModal(<?php echo $student['id']; ?>, 
                                    '<?php echo addslashes($student['full_name']); ?>', 
                                    '<?php echo addslashes($student['email']); ?>')">
                        📧 Send Message
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="floating-save">
                <button type="submit" name="save_attendance" class="save-btn">💾 Save Attendance</button>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-warning">
            ⚠️ No students found in section "<?php echo htmlspecialchars($class['section']); ?>".
        </div>
        <?php endif; ?>
    </main>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>📧 Send Message</h2>
                <button class="close-btn" onclick="closeMessageModal()">✕</button>
            </div>
            
            <div class="modal-body">
                <div class="student-details">
                    <p><strong>Student:</strong> <span id="studentNameDisplay"></span></p>
                    <p><strong>Email:</strong> <span id="studentEmailDisplay"></span></p>
                </div>

                <div class="templates">
                    <label>Quick Templates:</label>
                    <div class="template-buttons">
                        <button type="button" onclick="useTemplate('absent')">❌ Absent</button>
                        <button type="button" onclick="useTemplate('consecutive')">🚫 Consecutive</button>
                        <button type="button" onclick="useTemplate('late')">⏰ Late</button>
                        <button type="button" onclick="useTemplate('concern')">💭 Concern</button>
                    </div>
                </div>

                <textarea id="messageText" rows="6" placeholder="Type your message..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeMessageModal()" class="btn-cancel">Cancel</button>
                <button type="button" onclick="sendMessage()" class="btn-send">Send</button>
            </div>
        </div>
    </div>

    <script src="mark_attendance_mobile.js"></script>

    <script>
        // Mobile-Optimized JavaScript for Attendance System

// Global variables
let currentStudentData = {};
let hasUnsavedChanges = false;

// Change date without submitting attendance
function changeDate(dateValue) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('date', dateValue);
    window.location.href = 'mark_attendance.php?' + urlParams.toString();
}

// Navigate to previous or next day
function navigateDate(days) {
    const currentDate = new Date(document.getElementById('date_selector').value);
    currentDate.setDate(currentDate.getDate() + days);
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Don't allow future dates
    if (currentDate > today && days > 0) {
        return;
    }
    
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');
    const newDate = `${year}-${month}-${day}`;
    
    changeDate(newDate);
}

// Mark all students with a specific status
function markAll(status) {
    const radioButtons = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
    
    radioButtons.forEach(radio => {
        radio.checked = true;
        
        // Update visual state
        const parentLabel = radio.closest('.att-btn');
        const allLabels = parentLabel.parentElement.querySelectorAll('.att-btn');
        
        allLabels.forEach(label => label.classList.remove('active'));
        parentLabel.classList.add('active');
    });
    
    hasUnsavedChanges = true;
    
    // Haptic feedback for mobile
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
    
    // Show success message
    showToast(`All students marked as ${status}!`, 'success');
}

// Validate attendance before submission
function validateAttendance() {
    const checkedRadios = document.querySelectorAll('input[type="radio"]:checked');
    
    if (checkedRadios.length === 0) {
        showToast('Please mark attendance for at least one student!', 'warning');
        return false;
    }
    
    const confirmed = confirm(`Save attendance for ${checkedRadios.length} students?`);
    
    if (confirmed) {
        hasUnsavedChanges = false;
        showLoadingState();
    }
    
    return confirmed;
}

// Message Modal Functions
function openMessageModal(studentId, studentName, studentEmail) {
    currentStudentData = {
        id: studentId,
        name: studentName,
        email: studentEmail
    };

    document.getElementById('messageModal').style.display = 'block';
    document.getElementById('studentNameDisplay').textContent = studentName;
    document.getElementById('studentEmailDisplay').textContent = studentEmail;
    document.getElementById('messageText').value = '';
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate(30);
    }
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
    currentStudentData = {};
    
    // Re-enable body scroll
    document.body.style.overflow = '';
}

function useTemplate(template) {
    const templates = {
        absent: `Dear ${currentStudentData.name},

We noticed you were absent from class today. Please ensure to attend regularly and catch up on missed coursework.

If you have any valid reason for absence, please contact us.

Best regards,
Your Teacher`,
        
        consecutive: `Dear ${currentStudentData.name},

We have observed consecutive absences from your side. Regular attendance is crucial for your academic performance.

Please meet with me to discuss this matter.

Best regards,
Your Teacher`,
        
        late: `Dear ${currentStudentData.name},

You were marked late for today's class. Please try to arrive on time to avoid missing important information.

Best regards,
Your Teacher`,
        
        concern: `Dear ${currentStudentData.name},

I wanted to reach out regarding your attendance. Is everything okay? Please feel free to discuss any concerns with me.

Best regards,
Your Teacher`
    };

    document.getElementById('messageText').value = templates[template];
    
    // Haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate(30);
    }
}

function sendMessage() {
    const message = document.getElementById('messageText').value.trim();
    
    if (!message) {
        showToast('Please enter a message!', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('send_message', '1');
    formData.append('student_id', currentStudentData.id);
    formData.append('student_name', currentStudentData.name);
    formData.append('student_email', currentStudentData.email);
    formData.append('message', message);
    formData.append('date', document.getElementById('date_selector').value);

    // Show loading state
    showLoadingState();

    const urlParams = new URLSearchParams(window.location.search);
    const classIdParam = urlParams.get('class_id');
    const academicYearParam = urlParams.get('academic_year') || '';

    let fetchUrl = `mark_attendance.php?class_id=${classIdParam}`;
    if (academicYearParam) {
        fetchUrl += `&academic_year=${encodeURIComponent(academicYearParam)}`;
    }

    fetch(fetchUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            showToast('Message sent successfully!', 'success');
            closeMessageModal();
            
            // Update button to show message was sent
            const btn = document.querySelector(`button[data-student-id="${currentStudentData.id}"]`);
            if (btn) {
                btn.classList.add('sent');
                btn.innerHTML = '✓ Message Sent';
                btn.disabled = true;
            }
            
            // Haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate([50, 100, 50]);
            }
        } else {
            showToast('Failed to send message: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        hideLoadingState();
        showToast('Error sending message. Please try again.', 'error');
        console.error('Error:', error);
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#007bff'};
        color: ${type === 'warning' ? '#000' : '#fff'};
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        z-index: 1000;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease, fadeOut 0.3s ease 2.7s;
        max-width: 90%;
        text-align: center;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Show/hide loading state
function showLoadingState() {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    const spinner = document.createElement('div');
    spinner.style.cssText = `
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    `;
    
    overlay.appendChild(spinner);
    document.body.appendChild(overlay);
    
    // Add spin animation
    if (!document.querySelector('#spinAnimation')) {
        const style = document.createElement('style');
        style.id = 'spinAnimation';
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
}

function hideLoadingState() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Handle radio button changes
function handleAttendanceChange(event) {
    const radio = event.target;
    
    if (radio.type === 'radio') {
        const parentLabel = radio.closest('.att-btn');
        const allLabels = parentLabel.parentElement.querySelectorAll('.att-btn');
        
        // Remove active class from all labels
        allLabels.forEach(label => label.classList.remove('active'));
        
        // Add active class to selected label
        parentLabel.classList.add('active');
        
        hasUnsavedChanges = true;
        
        // Haptic feedback
        if (navigator.vibrate) {
            navigator.vibrate(20);
        }
    }
}

// Prevent accidental navigation
function preventAccidentalNavigation() {
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
}

// Add tooltip functionality for attendance history
function addHistoryTooltips() {
    const historyDays = document.querySelectorAll('.history-day');
    
    historyDays.forEach(day => {
        day.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (title) {
                const tooltip = document.createElement('div');
                tooltip.className = 'history-tooltip';
                tooltip.textContent = title;
                tooltip.style.cssText = `
                    position: absolute;
                    background: rgba(0, 0, 0, 0.9);
                    color: white;
                    padding: 6px 10px;
                    border-radius: 6px;
                    font-size: 11px;
                    white-space: nowrap;
                    z-index: 1000;
                    pointer-events: none;
                `;
                
                this.appendChild(tooltip);
                
                // Position tooltip
                const rect = this.getBoundingClientRect();
                tooltip.style.bottom = '100%';
                tooltip.style.left = '50%';
                tooltip.style.transform = 'translateX(-50%)';
                tooltip.style.marginBottom = '5px';
            }
        });
        
        day.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.history-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Mobile Attendance System Initialized with 3-Day History');
    
    // Detect device type
    const isDesktop = window.innerWidth >= 1024;
    const isMobile = window.innerWidth < 768;
    
    console.log(`Device: ${isDesktop ? 'Desktop 💻' : isMobile ? 'Mobile 📱' : 'Tablet 📱'}`);
    
    // Handle attendance button clicks
    document.querySelectorAll('.attendance-buttons').forEach(container => {
        container.addEventListener('change', handleAttendanceChange);
        
        // Also handle label clicks
        container.querySelectorAll('.att-btn').forEach(label => {
            label.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        });
    });
    
    // Handle form submission
    const form = document.getElementById('attendanceForm');
    if (form) {
        form.addEventListener('submit', () => {
            hasUnsavedChanges = false;
        });
    }
    
    // Close modal on overlay click
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.classList.contains('modal-overlay')) {
                closeMessageModal();
            }
        });
    }
    
    // Prevent accidental navigation
    preventAccidentalNavigation();
    
    // Add tooltips for desktop
    if (isDesktop) {
        addHistoryTooltips();
    }
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Escape to close modal
        if (e.key === 'Escape') {
            closeMessageModal();
        }
        
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const saveButton = document.querySelector('button[name="save_attendance"]');
            if (saveButton) {
                saveButton.click();
            }
        }
    });
    
    // Add pull-to-refresh indication (mobile only)
    if (isMobile) {
        let touchStartY = 0;
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            const touchY = e.touches[0].clientY;
            const touchDiff = touchY - touchStartY;
            
            // If scrolled to top and pulling down
            if (window.scrollY === 0 && touchDiff > 0) {
                // Could add pull-to-refresh indicator here
            }
        }, { passive: true });
    }
    
    // Desktop-specific enhancements
    if (isDesktop) {
        // Add hover effects for student cards
        document.querySelectorAll('.student-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Add keyboard navigation for attendance buttons
        document.querySelectorAll('.student-card').forEach((card, index) => {
            const buttons = card.querySelectorAll('.att-btn');
            
            buttons.forEach((btn, btnIndex) => {
                btn.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight' && btnIndex < buttons.length - 1) {
                        buttons[btnIndex + 1].focus();
                        e.preventDefault();
                    } else if (e.key === 'ArrowLeft' && btnIndex > 0) {
                        buttons[btnIndex - 1].focus();
                        e.preventDefault();
                    } else if (e.key === 'Enter' || e.key === ' ') {
                        btn.click();
                        e.preventDefault();
                    }
                });
            });
        });
        
        console.log('✨ Desktop enhancements enabled');
    }
    
    // Log attendance statistics
    const totalStudents = document.querySelectorAll('.student-card').length;
    const historyDays = document.querySelectorAll('.history-day').length / totalStudents;
    console.log(`📊 Total Students: ${totalStudents}`);
    console.log(`📅 History Days Displayed: ${historyDays}`);
    
    // Set initial active states based on checked radios
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        const parentLabel = radio.closest('.att-btn');
        if (parentLabel) {
            parentLabel.classList.add('active');
        }
    });
    
    // Add responsive resize handler
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const newWidth = window.innerWidth;
            console.log(`📐 Window resized: ${newWidth}px`);
            
            // Re-add tooltips if switching to desktop
            if (newWidth >= 1024) {
                addHistoryTooltips();
            }
        }, 250);
    });
    
    console.log('✅ All event listeners attached (with 3-day history support)');
});

// Handle visibility change (save battery when app in background)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        console.log('📱 App moved to background');
    } else {
        console.log('📱 App returned to foreground');
    }
});

// Handle online/offline status
window.addEventListener('online', () => {
    showToast('You are now online', 'success');
});

window.addEventListener('offline', () => {
    showToast('You are offline. Changes will be saved when online.', 'warning');
});

// Export functions for use in HTML onclick handlers
window.changeDate = changeDate;
window.navigateDate = navigateDate;
window.markAll = markAll;
window.validateAttendance = validateAttendance;
window.openMessageModal = openMessageModal;
window.closeMessageModal = closeMessageModal;
window.useTemplate = useTemplate;
window.sendMessage = sendMessage;
    </script>
</body>
</html>