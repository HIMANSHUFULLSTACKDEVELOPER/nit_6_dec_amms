<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db.php';
checkRole(['hod']);

$user = getCurrentUser();
$department_id = $_SESSION['department_id'];

// Get current academic year from URL or default
$current_year = isset($_GET['year']) ? $_GET['year'] : "2025-2026";

// Get department info
$dept_query = "SELECT * FROM departments WHERE id = $department_id";
$dept_result = $conn->query($dept_query);
$department = $dept_result->fetch_assoc();

// Get statistics
$stats = [];

// Total teachers in department
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND department_id = $department_id AND is_active = 1");
$stats['teachers'] = $result->fetch_assoc()['count'];

// Total students in department (all years if academic_year column doesn't exist)
$student_count_query = "SELECT COUNT(*) as count FROM students WHERE department_id = $department_id AND is_active = 1";
// Check if academic_year column exists
$check_column = $conn->query("SHOW COLUMNS FROM students LIKE 'academic_year'");
if ($check_column && $check_column->num_rows > 0) {
    $student_count_query = "SELECT COUNT(*) as count FROM students WHERE department_id = $department_id AND is_active = 1 AND academic_year = '$current_year'";
}
$result = $conn->query($student_count_query);
$stats['students'] = $result->fetch_assoc()['count'];

// Total classes in department (all years if academic_year column doesn't exist)
$class_count_query = "SELECT COUNT(*) as count FROM classes WHERE department_id = $department_id";
$check_column = $conn->query("SHOW COLUMNS FROM classes LIKE 'academic_year'");
if ($check_column && $check_column->num_rows > 0) {
    $class_count_query = "SELECT COUNT(*) as count FROM classes WHERE department_id = $department_id AND academic_year = '$current_year'";
}
$result = $conn->query($class_count_query);
$stats['classes'] = $result->fetch_assoc()['count'];

// Today's attendance in department
$today = date('Y-m-d');
$today_query = "SELECT COUNT(*) as total,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent
                FROM student_attendance sa
                JOIN classes c ON sa.class_id = c.id
                WHERE c.department_id = $department_id AND sa.attendance_date = '$today'";
$today_result = $conn->query($today_query);
$today_stats = $today_result->fetch_assoc();

// Get all available academic years (only if column exists)
$available_years = array("2025-2026", "2024-2025", "2023-2024"); // Default years
$check_column = $conn->query("SHOW COLUMNS FROM classes LIKE 'academic_year'");
if ($check_column && $check_column->num_rows > 0) {
    $years_query = "SELECT DISTINCT academic_year FROM classes WHERE department_id = $department_id AND academic_year IS NOT NULL ORDER BY academic_year DESC";
    $years_result = $conn->query($years_query);
    if ($years_result && $years_result->num_rows > 0) {
        $available_years = [];
        while ($year_row = $years_result->fetch_assoc()) {
            $available_years[] = $year_row['academic_year'];
        }
    }
}

// Get department teachers
$teachers_query = "SELECT * FROM users WHERE role = 'teacher' AND department_id = $department_id AND is_active = 1 ORDER BY full_name";
$teachers = $conn->query($teachers_query);

// Get department classes with attendance
$classes_query = "SELECT c.*, u.full_name as teacher_name,
                  (SELECT COUNT(*) FROM students WHERE class_id = c.id AND is_active = 1) as student_count,
                  (SELECT COUNT(*) FROM student_attendance WHERE class_id = c.id AND attendance_date = '$today') as today_marked
                  FROM classes c
                  LEFT JOIN users u ON c.teacher_id = u.id
                  WHERE c.department_id = $department_id
                  ORDER BY c.class_name";
$classes = $conn->query($classes_query);

// Get students
$students_query = "SELECT s.*, c.class_name 
                   FROM students s
                   LEFT JOIN classes c ON s.class_id = c.id
                   WHERE s.department_id = $department_id AND s.is_active = 1
                   ORDER BY s.full_name";
$students = $conn->query($students_query);

// Include notices component
$notices_path = __DIR__ . '/../admin/notices_component.php';
if (!file_exists($notices_path)) {
    $notices_path = __DIR__ . '/notices_component.php';
}
if (file_exists($notices_path)) {
    require_once $notices_path;
}




// Get count of student resumes
$resume_count_query = "SELECT COUNT(*) as count FROM student_resumes";
$resume_count_result = $conn->query($resume_count_query);
$resume_count = $resume_count_result->fetch_assoc()['count'];






// Get leave applications statistics for the department
$leave_stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN la.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN la.status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN la.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_applications la
    JOIN students s ON la.student_id = s.id
    WHERE s.department_id = $department_id";
$leave_stats_result = $conn->query($leave_stats_query);
$leave_stats = $leave_stats_result->fetch_assoc();

// Get recent leave applications (last 30 days)
$leave_applications_query = "SELECT 
    la.*,
    s.full_name as student_name,
    s.roll_number,
    s.email as student_email,
    c.class_name,
    c.section,
    c.year,
    t.full_name as teacher_name,
    t.email as teacher_email
    FROM leave_applications la
    JOIN students s ON la.student_id = s.id
    JOIN classes c ON la.class_id = c.id
    LEFT JOIN users t ON la.teacher_id = t.id
    WHERE s.department_id = $department_id
    AND la.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY la.created_at DESC
    LIMIT 50";
$leave_applications = $conn->query($leave_applications_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
    <title>HOD Dashboard - NIT AMMS</title>
    <link rel="stylesheet" href="hod_style_new.css">
    <style>
        /* HOD Dashboard - Enhanced Modern Styles */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
    overflow-x: hidden;
}

/* Animated Background Particles */
.particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
}

.particle {
    position: absolute;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    animation: float 15s infinite ease-in-out;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
}

/* Enhanced Navbar */
.navbar {
    background: rgba(26, 31, 58, 0.95);
    backdrop-filter: blur(20px);
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 15px;
}

.navbar-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    animation: rotateLogo 10s linear infinite;
}

@keyframes rotateLogo {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.navbar h1 {
    color: white;
    font-size: 24px;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 25px;
    color: white;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 20px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
}

.main-content {
    padding: 40px;
    max-width: 1600px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Hero Welcome Section */
.hero-welcome {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 50px;
    border-radius: 30px;
    margin-bottom: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    opacity: 0.1;
    z-index: 0;
}

.animated-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" fill="%23667eea" opacity="0.2"/></svg>');
    background-size: cover;
    animation: wave 8s linear infinite;
    z-index: 0;
}

@keyframes wave {
    0% { background-position: 0 0; }
    100% { background-position: 1200px 0; }
}

.hero-content {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 40px;
    align-items: center;
}

.hero-text h2 {
    font-size: 42px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 15px;
    font-weight: 800;
}

.hero-text p {
    font-size: 18px;
    color: #666;
    margin-bottom: 10px;
}

/* Year Selector */
.year-selector {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.5);
    padding: 12px 20px;
    border-radius: 15px;
    border: 2px solid rgba(102, 126, 234, 0.3);
}

.year-select {
    padding: 8px 16px;
    border: 2px solid rgba(102, 126, 234, 0.5);
    border-radius: 10px;
    background: white;
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.3s;
}

.year-select:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.year-select:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    margin-top: 30px;
}

.hero-stat-item {
    text-align: center;
    background: rgba(255, 255, 255, 0.5);
    padding: 20px;
    border-radius: 15px;
    border: 2px solid rgba(102, 126, 234, 0.2);
    transition: all 0.3s ease;
}

.hero-stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    border-color: rgba(102, 126, 234, 0.5);
}

.hero-stat-value {
    font-size: 36px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 5px;
}

.hero-stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

/* Glass Clock */
.glass-clock {
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 25px;
    text-align: center;
    border: 2px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    min-width: 280px;
}

.clock-icon {
    font-size: 48px;
    margin-bottom: 15px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.glass-clock .time {
    font-size: 48px;
    font-weight: 800;
    font-family: 'Courier New', monospace;
    color: #2c3e50;
    letter-spacing: 2px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.glass-clock .date {
    font-size: 14px;
    color: #666;
    margin-top: 10px;
    font-weight: 500;
}

/* Premium Stats Cards */
.premium-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin: 40px 0;
}

.premium-stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.premium-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
    background-size: 200% 100%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.premium-stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
}

.stat-icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.stat-details h4 {
    color: #666;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.stat-value-large {
    font-size: 42px;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

/* Tables */
.table-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
    margin: 30px 0;
    border: 2px solid rgba(255, 255, 255, 0.5);
    scroll-margin-top: 100px; /* For smooth scroll to anchor */
}

.table-container h3 {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

thead th {
    padding: 15px;
    color: white;
    font-weight: 600;
    text-align: left;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s;
}

tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: translateX(5px);
}

tbody td {
    padding: 18px 15px;
    color: #2c3e50;
    font-size: 14px;
}

/* Badges */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

/* Buttons */
.btn {
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-block;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 13px;
}

/* Footer */
.footer {
    background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #2a3254 100%);
    position: relative;
    overflow: hidden;
    margin-top: 60px;
}

.footer-border {
    height: 2px;
    background: linear-gradient(90deg, #4a9eff, #00d4ff, #4a9eff, #00d4ff);
    background-size: 200% 100%;
    animation: borderMove 3s linear infinite;
}

@keyframes borderMove {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.footer-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px 20px;
}

.developer-section {
    background: rgba(255, 255, 255, 0.03);
    padding: 20px;
    border-radius: 15px;
    border: 1px solid rgba(74, 158, 255, 0.15);
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.developer-section p {
    color: #ffffff;
    font-size: 14px;
    margin: 0 0 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.company-link {
    display: inline-block;
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    padding: 8px 24px;
    border: 2px solid #4a9eff;
    border-radius: 30px;
    background: linear-gradient(135deg, rgba(74, 158, 255, 0.2), rgba(0, 212, 255, 0.2));
    box-shadow: 0 3px 12px rgba(74, 158, 255, 0.3);
    margin-bottom: 15px;
    transition: all 0.3s;
}

.company-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(74, 158, 255, 0.5);
}

.divider {
    width: 50%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    margin: 15px auto;
}

.team-label {
    color: #888;
    font-size: 10px;
    margin: 0 0 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 600;
}

.developer-badges {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 12px;
}

.developer-badge {
    color: #ffffff;
    font-size: 13px;
    text-decoration: none;
    padding: 8px 16px;
    background: linear-gradient(135deg, rgba(74, 158, 255, 0.25), rgba(0, 212, 255, 0.25));
    border-radius: 20px;
    border: 1px solid rgba(74, 158, 255, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 3px 10px rgba(74, 158, 255, 0.2);
    transition: all 0.3s;
}

.developer-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(74, 158, 255, 0.4);
}

.role-tags {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.role-tag {
    color: #4a9eff;
    font-size: 10px;
    padding: 4px 12px;
    background: rgba(74, 158, 255, 0.1);
    border-radius: 12px;
    border: 1px solid rgba(74, 158, 255, 0.3);
}

.footer-bottom {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.footer-bottom p {
    color: #888;
    font-size: 12px;
    margin: 0 0 10px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .premium-stats { grid-template-columns: repeat(2, 1fr); }
    .hero-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .navbar { padding: 15px 20px; flex-direction: column; gap: 15px; }
    .navbar h1 { font-size: 18px; }
    .main-content { padding: 20px; }
    .hero-welcome { padding: 30px 20px; }
    .hero-content { grid-template-columns: 1fr; text-align: center; }
    .hero-text h2 { font-size: 28px; }
    .hero-stats { grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .glass-clock { min-width: 100%; margin-top: 20px; }
    .premium-stats { grid-template-columns: 1fr; gap: 15px; }
    .table-container { padding: 20px; overflow-x: auto; }
    table { font-size: 12px; }
    .user-info { flex-direction: column; gap: 10px; }
    .year-selector { flex-direction: column; gap: 10px; }
}

@media (max-width: 480px) {
    .stat-value-large { font-size: 32px; }
    .developer-badges { flex-direction: column; }
    .hero-text h2 { font-size: 24px; }
    .hero-stats { grid-template-columns: 1fr; }
    .hero-stat-value { font-size: 28px; }
}







.quick-action-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    margin-bottom: 20px;
}

.quick-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.action-link {
    display: flex;
    align-items: center;
    gap: 20px;
    text-decoration: none;
    color: inherit;
}

.action-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
}

.action-content {
    flex: 1;
}

.action-content h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 5px;
}

.action-content p {
    font-size: 14px;
    color: #666;
}

.action-arrow {
    font-size: 24px;
    color: #667eea;
    font-weight: bold;
}
    </style>

    <script>
        /* HOD Dashboard JavaScript */

// Live Clock Function
function updateClock() {
    const now = new Date();
    
    // Time in 12-hour format
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    // Convert to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // If hour is 0, make it 12
    hours = String(hours).padStart(2, '0');
    
    const clockElement = document.getElementById('liveClock');
    if (clockElement) {
        clockElement.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
    }
    
    // Date
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('en-US', options);
    const dateElement = document.getElementById('liveDate');
    if (dateElement) {
        dateElement.textContent = dateString;
    }
}

// Filter by Year Function
function filterByYear(year) {
    if (year) {
        showToast('Loading data for ' + year + '...', 'info');
        
        // Reload page with year parameter
        const url = new URL(window.location.href);
        url.searchParams.set('year', year);
        window.location.href = url.toString();
    }
}

// Update clock immediately and then every second
updateClock();
setInterval(updateClock, 1000);

// Create more animated particles dynamically
function createParticles() {
    const particlesContainer = document.querySelector('.particles');
    if (!particlesContainer) return;
    
    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 15 + 5 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 15 + 's';
        particle.style.animationDuration = Math.random() * 10 + 10 + 's';
        particlesContainer.appendChild(particle);
    }
}

// Initialize particles on page load
document.addEventListener('DOMContentLoaded', function() {
    createParticles();
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add fade-in animation for stat cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all stat cards
    document.querySelectorAll('.premium-stat-card, .table-container').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
    
    // Add click animation to buttons
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Table row highlight
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(102, 126, 234, 0.08)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Year selector change animation
    const yearSelect = document.getElementById('academicYear');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            this.style.transform = 'scale(1.05)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
        });
    }
});

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 15px 25px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 
                     type === 'error' ? 'linear-gradient(135deg, #ff6b6b, #ee5a5a)' : 
                     'linear-gradient(135deg, #667eea, #764ba2)'};
        color: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        font-weight: 600;
        animation: slideInRight 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add toast animations
const toastStyle = document.createElement('style');
toastStyle.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }
`;
document.head.appendChild(toastStyle);

// Expose functions globally
window.showToast = showToast;
window.updateClock = updateClock;
window.filterByYear = filterByYear;

// Console log for developers
console.log('%c🎓 NIT AMMS - HOD Dashboard', 'color: #667eea; font-size: 24px; font-weight: bold;');
console.log('%c✨ Developed by Techyug Software Pvt. Ltd.', 'color: #764ba2; font-size: 14px;');
console.log('%c👨‍💻 Developers: Himanshu Patil & Pranay Panore', 'color: #f093fb; font-size: 12px;');

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        const perfData = performance.getEntriesByType('navigation')[0];
        console.log(`⚡ Page loaded in ${Math.round(perfData.loadEventEnd - perfData.fetchStart)}ms`);
    });
}
    </script>
</head>
<body>

 <!-- Animated Background Particles -->
    <div class="particles">
        <div class="particle" style="width: 10px; height: 10px; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 15px; height: 15px; left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 8px; height: 8px; left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 12px; height: 12px; left: 50%; animation-delay: 1s;"></div>
        <div class="particle" style="width: 10px; height: 10px; left: 70%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 14px; height: 14px; left: 85%; animation-delay: 5s;"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
       
        <div class="user-info">
            <a href="index.php" class="btn btn-secondary btn-sm">Dashboard</a>
               </div>
    </nav>


    <br><br><br>
  
   <!-- Leave Applications Statistics Card -->
<div class="premium-stat-card" style="grid-column: span 2;">
    <div class="stat-icon-wrapper">📝</div>
    <div class="stat-details">
        <h4>Leave Applications (Last 30 Days)</h4>
        <div style="display: flex; gap: 30px; margin-top: 15px; flex-wrap: wrap;">
            <div>
                <div class="stat-value-large" style="font-size: 28px;"><?php echo $leave_stats['total']; ?></div>
                <small style="color: #666;">Total</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #ffc107;"><?php echo $leave_stats['pending']; ?></div>
                <small style="color: #666;">Pending</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #28a745;"><?php echo $leave_stats['approved']; ?></div>
                <small style="color: #666;">Approved</small>
            </div>
            <div>
                <div class="stat-value-large" style="font-size: 28px; color: #dc3545;"><?php echo $leave_stats['rejected']; ?></div>
                <small style="color: #666;">Rejected</small>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <a href="#leaveApplicationsTable" class="btn btn-primary btn-sm">📋 View All Applications</a>
        </div>
    </div>
</div>

<!-- Leave Applications Table -->
<div class="table-container" id="leaveApplicationsTable" style="margin-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>📝 Department Leave Applications (Last 30 Days)</h3>
        <div style="display: flex; gap: 10px;">
            <select id="leaveStatusFilter" class="filter-select" onchange="filterLeaveApplications()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
    
    <?php if ($leave_applications && $leave_applications->num_rows > 0): ?>
    <div style="overflow-x: auto;">
        <table id="leaveApplicationsTableData">
            <thead>
                <tr>
                    <th>Application Date</th>
                    <th>Student Details</th>
                    <th>Class</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($app = $leave_applications->fetch_assoc()): ?>
                <tr class="leave-row" data-status="<?php echo $app['status']; ?>">
                    <td>
                        <strong><?php echo date('d M Y', strtotime($app['created_at'])); ?></strong><br>
                        <small style="color: #666;"><?php echo date('h:i A', strtotime($app['created_at'])); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['student_name']); ?></strong><br>
                        <small style="color: #666;">Roll: <?php echo htmlspecialchars($app['roll_number']); ?></small><br>
                        <small style="color: #999;"><?php echo htmlspecialchars($app['student_email']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($app['class_name']); ?><br>
                        <small style="color: #666;">
                            <?php echo htmlspecialchars($app['section']); ?> | Year <?php echo $app['year']; ?>
                        </small>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?php echo ucfirst(htmlspecialchars($app['leave_type'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $start = date('d M', strtotime($app['start_date']));
                        $end = date('d M Y', strtotime($app['end_date']));
                        $days = (strtotime($app['end_date']) - strtotime($app['start_date'])) / (60 * 60 * 24) + 1;
                        ?>
                        <strong><?php echo $start; ?> - <?php echo $end; ?></strong><br>
                        <small style="color: #666;">(<?php echo $days; ?> day<?php echo $days > 1 ? 's' : ''; ?>)</small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['teacher_name'] ?? 'Not Assigned'); ?></strong><br>
                        <small style="color: #999;"><?php echo htmlspecialchars($app['teacher_email'] ?? 'N/A'); ?></small>
                    </td>
                    <td>
                        <?php 
                        $statusClass = $app['status'];
                        $statusIcon = $app['status'] === 'approved' ? '✅' : 
                                     ($app['status'] === 'rejected' ? '❌' : '⏳');
                        ?>
                        <span class="badge badge-<?php echo $statusClass === 'approved' ? 'success' : 
                                                              ($statusClass === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo $statusIcon; ?> <?php echo ucfirst($app['status']); ?>
                        </span>
                        <?php if ($app['updated_at'] && $app['updated_at'] != $app['created_at']): ?>
                            <br><small style="color: #999;">
                                Updated: <?php echo date('d M Y', strtotime($app['updated_at'])); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="viewLeaveDetails(<?php echo $app['id']; ?>)" class="btn btn-primary btn-sm">
                            👁️ View
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: #666;">
        <p style="font-size: 48px; margin-bottom: 20px;">📭</p>
        <p style="font-size: 18px;">No leave applications in the last 30 days</p>
        <p style="margin-top: 10px; color: #999;">Leave applications will appear here once students submit them.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Leave Application Details Modal -->
<div id="leaveDetailsModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>📝 Leave Application Details</h3>
            <button onclick="closeLeaveModal()" class="btn btn-secondary btn-sm">✕ Close</button>
        </div>
        <div id="leaveDetailsContent">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>


<style>
.filter-select {
    padding: 10px 15px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 10px;
    background: white;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-select:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.filter-select:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 20px;
    max-height: 85vh;
    overflow-y: auto;
    width: 90%;
    animation: slideUp 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.leave-detail-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 4px solid #667eea;
}

.leave-detail-item strong {
    color: #667eea;
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}
</style>

<!-- Add this JavaScript in your <script> section -->
<script>
// Filter leave applications by status
function filterLeaveApplications() {
    const filter = document.getElementById('leaveStatusFilter').value;
    const rows = document.querySelectorAll('.leave-row');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (filter === 'all' || status === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View leave application details
function viewLeaveDetails(leaveId) {
    console.log('Viewing leave application ID:', leaveId); // Debug log
    
    // Validate ID
    if (!leaveId || leaveId <= 0) {
        alert('Invalid leave application ID');
        return;
    }
    
    // Show modal with loading message
    const modal = document.getElementById('leaveDetailsModal');
    const content = document.getElementById('leaveDetailsContent');
    
    modal.style.display = 'flex';
    content.innerHTML = '<div style="text-align: center; padding: 40px;"><p>⏳ Loading details...</p></div>';
    
    // Fetch details via AJAX
    fetch(`get_leave_details.php?id=${encodeURIComponent(leaveId)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            
            if (data.success) {
                displayLeaveDetails(data.application);
            } else {
                content.innerHTML = `
                    <div style="text-align: center; padding: 30px;">
                        <p style="color: #dc3545; font-size: 18px; margin-bottom: 10px;">❌ Error</p>
                        <p style="color: #666;">${data.message || 'Error loading details.'}</p>
                        ${data.debug ? `<pre style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: left; font-size: 12px;">${JSON.stringify(data.debug, null, 2)}</pre>` : ''}
                        <button onclick="closeLeaveModal()" class="btn btn-secondary" style="margin-top: 15px;">Close</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            content.innerHTML = `
                <div style="text-align: center; padding: 30px;">
                    <p style="color: #dc3545; font-size: 18px; margin-bottom: 10px;">❌ Network Error</p>
                    <p style="color: #666;">Failed to load details. Please check your connection and try again.</p>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">${error.message}</p>
                    <button onclick="closeLeaveModal()" class="btn btn-secondary" style="margin-top: 15px;">Close</button>
                </div>
            `;
        });
}

// Display leave details in modal
function displayLeaveDetails(app) {
    // Validate data
    if (!app || typeof app !== 'object') {
        document.getElementById('leaveDetailsContent').innerHTML = 
            '<p style="color: red; text-align: center; padding: 30px;">Invalid application data received.</p>';
        return;
    }
    
    const statusClass = app.status === 'approved' ? 'success' : 
                       (app.status === 'rejected' ? 'danger' : 'warning');
    const statusIcon = app.status === 'approved' ? '✅' : 
                      (app.status === 'rejected' ? '❌' : '⏳');
    
    let html = `
        <div class="leave-detail-item">
            <strong>📋 Subject</strong>
            ${escapeHtml(app.subject || app.leave_type || 'N/A')}
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div class="leave-detail-item">
                <strong>👨‍🎓 Student</strong>
                ${escapeHtml(app.student_name || 'N/A')}<br>
                <small style="color: #666;">Roll: ${escapeHtml(app.roll_number || 'N/A')}</small><br>
                <small style="color: #999;">${escapeHtml(app.student_email || 'N/A')}</small>
            </div>
            
            <div class="leave-detail-item">
                <strong>📚 Class</strong>
                ${escapeHtml(app.class_name || 'N/A')} - ${escapeHtml(app.section || 'N/A')}<br>
                <small style="color: #666;">Year ${escapeHtml(app.year || 'N/A')}</small>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div class="leave-detail-item">
                <strong>🏷️ Leave Type</strong>
                ${escapeHtml((app.leave_type || 'N/A').charAt(0).toUpperCase() + (app.leave_type || 'N/A').slice(1))}
            </div>
            
            <div class="leave-detail-item">
                <strong>📅 Duration</strong>
                ${app.start_date ? formatDate(app.start_date) : 'N/A'} to ${app.end_date ? formatDate(app.end_date) : 'N/A'}<br>
                <small style="color: #666;">(${app.total_days || 0} day${(app.total_days || 0) > 1 ? 's' : ''})</small>
            </div>
        </div>
        
        <div class="leave-detail-item">
            <strong>💬 Reason</strong>
            ${escapeHtml(app.reason || 'No reason provided').replace(/\n/g, '<br>')}
        </div>
        
        ${app.attachment ? `
        <div class="leave-detail-item">
            <strong>📎 Attachment</strong>
            <a href="../uploads/leave_applications/${escapeHtml(app.attachment)}" target="_blank" class="btn btn-secondary btn-sm">
                📄 View Attachment
            </a>
        </div>
        ` : ''}
        
        <div class="leave-detail-item">
            <strong>👨‍🏫 Teacher</strong>
            ${escapeHtml(app.teacher_name || 'Not Assigned')}<br>
            ${app.teacher_email ? `<small style="color: #999;">${escapeHtml(app.teacher_email)}</small>` : ''}
        </div>
        
        <div class="leave-detail-item" style="border-left-color: ${statusClass === 'success' ? '#28a745' : (statusClass === 'danger' ? '#dc3545' : '#ffc107')};">
            <strong>📊 Status</strong>
            <span class="badge badge-${statusClass}" style="font-size: 14px; padding: 8px 16px;">
                ${statusIcon} ${escapeHtml((app.status || 'UNKNOWN').toUpperCase())}
            </span>
            ${app.teacher_remarks ? `
            <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #667eea;">👨‍🏫 Teacher's Remarks:</strong>
                <p style="margin-top: 8px; line-height: 1.6;">${escapeHtml(app.teacher_remarks).replace(/\n/g, '<br>')}</p>
            </div>
            ` : ''}
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div class="leave-detail-item">
                <strong>📆 Applied On</strong>
                ${app.created_at ? formatDateTime(app.created_at) : 'N/A'}
            </div>
            
            ${app.updated_at && app.updated_at !== app.created_at ? `
            <div class="leave-detail-item">
                <strong>🔄 Last Updated</strong>
                ${formatDateTime(app.updated_at)}
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('leaveDetailsContent').innerHTML = html;
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Close leave modal
function closeLeaveModal() {
    document.getElementById('leaveDetailsModal').style.display = 'none';
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Helper function to format date and time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
    return date.toLocaleDateString('en-US', dateOptions) + ', ' + 
           date.toLocaleTimeString('en-US', timeOptions);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('leaveDetailsModal');
    if (event.target === modal) {
        closeLeaveModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLeaveModal();
    }
});
</script>


 
  
    <script src="hod_script_new.js"></script>
</body>
</html>