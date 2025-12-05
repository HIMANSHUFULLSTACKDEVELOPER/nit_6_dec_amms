<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



require_once '../db.php';
checkRole(['parent']);

$parent_id = $_SESSION['user_id'];
$student_id = $_SESSION['student_id'];

// Get parent info
$parent_query = "SELECT * FROM parents WHERE id = $parent_id";
$parent_result = $conn->query($parent_query);
$parent = $parent_result ? $parent_result->fetch_assoc() : ['parent_name' => 'Parent'];

// Get student info
$student_query = "SELECT s.*, d.dept_name, c.class_name, c.section
                  FROM students s
                  LEFT JOIN departments d ON s.department_id = d.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE s.id = $student_id";
$student_result = $conn->query($student_query);
$student = $student_result ? $student_result->fetch_assoc() : [];

// Get today's attendance
$today = date('Y-m-d');
$today_query = "SELECT * FROM student_attendance 
                WHERE student_id = $student_id AND attendance_date = '$today'";
$today_result = $conn->query($today_query);
$today_attendance = $today_result ? $today_result->fetch_assoc() : null;

// Get current month statistics
$current_month = date('Y-m');
$month_stats_query = "SELECT 
                      COUNT(*) as total_days,
                      SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                      SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                      SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                      FROM student_attendance
                      WHERE student_id = $student_id 
                      AND DATE_FORMAT(attendance_date, '%Y-%m') = '$current_month'";
$month_stats_result = $conn->query($month_stats_query);
$month_stats = $month_stats_result ? $month_stats_result->fetch_assoc() : ['total_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];

$total_days = $month_stats['total_days'] ?? 0;
$attendance_percentage = $total_days > 0 ? round(($month_stats['present'] / $total_days) * 100, 2) : 0;

// Get overall statistics
$overall_stats_query = "SELECT 
                        COUNT(*) as total_days,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                        FROM student_attendance
                        WHERE student_id = $student_id";
$overall_stats_result = $conn->query($overall_stats_query);
$overall_stats = $overall_stats_result ? $overall_stats_result->fetch_assoc() : ['total_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];

$overall_total = $overall_stats['total_days'] ?? 0;
$overall_percentage = $overall_total > 0 ? round(($overall_stats['present'] / $overall_total) * 100, 2) : 0;

// Get recent attendance - simplified query first
$recent_query = "SELECT sa.*, 
                 s.class_id, s.department_id
                 FROM student_attendance sa
                 LEFT JOIN students s ON sa.student_id = s.id
                 WHERE sa.student_id = $student_id 
                 ORDER BY sa.attendance_date DESC LIMIT 10";
$recent_attendance = $conn->query($recent_query);

// Display class section with proper formatting
$section_names = [
    'Civil' => '🏗️ Civil Engineering',
    'Mechanical' => '⚙️ Mechanical Engineering',
    'CSE-A' => '💻 Computer Science - A',
    'CSE-B' => '💻 Computer Science - B',
    'Electrical' => '⚡ Electrical Engineering'
];

$display_section = isset($section_names[$student['section'] ?? '']) ? 
                   $section_names[$student['section']] : 
                   htmlspecialchars($student['section'] ?? $student['class_name'] ?? 'N/A');

// Parenting tips array
$parenting_tips = [
    ["tip" => "Encourage your child to maintain regular study habits. Consistency is key to academic success.", "icon" => "📚"],
    ["tip" => "Celebrate small victories and progress. Positive reinforcement builds confidence and motivation.", "icon" => "🌟"],
    ["tip" => "Maintain open communication with teachers. Regular engagement helps track your child's progress.", "icon" => "👨‍🏫"],
    ["tip" => "Ensure your child gets adequate sleep and nutrition. A healthy body supports a healthy mind.", "icon" => "😴"],
    ["tip" => "Create a dedicated study space at home. A quiet, organized environment improves focus.", "icon" => "🏠"],
    ["tip" => "Encourage extracurricular activities. Balance between academics and hobbies is essential.", "icon" => "⚽"],
    ["tip" => "Monitor screen time and ensure a healthy balance between study and recreation.", "icon" => "📱"],
    ["tip" => "Be involved but not overbearing. Guide your child while allowing them to learn independence.", "icon" => "🤝"],
    ["tip" => "Teach time management skills early. Help your child create schedules and stick to them.", "icon" => "⏰"],
    ["tip" => "Show interest in what they're learning. Ask questions about their day and their subjects.", "icon" => "💭"]
];

// Father's love quotes
$father_quotes = [
    ["quote" => "A father's love is the foundation upon which children build their dreams.", "author" => "Unknown"],
    ["quote" => "Behind every great child is a father who believed in them first.", "author" => "Unknown"],
    ["quote" => "A father is someone you look up to no matter how tall you grow.", "author" => "Unknown"],
    ["quote" => "The greatest gift I ever had came from God; I call him Dad.", "author" => "Unknown"],
    ["quote" => "A father's guidance shapes the person their child becomes.", "author" => "Unknown"],
    ["quote" => "No matter how old we are, we always need our father's wisdom.", "author" => "Unknown"],
    ["quote" => "A father's love knows no boundaries, no limits, only endless devotion.", "author" => "Unknown"],
    ["quote" => "Being a father means being there - in presence, in spirit, in heart.", "author" => "Unknown"]
];

// Select random items
$daily_tip = $parenting_tips[array_rand($parenting_tips)];
$daily_quote = $father_quotes[array_rand($father_quotes)];




// Include notices component
$notices_path = __DIR__ . '/../admin/notices_component.php';
if (!file_exists($notices_path)) {
    $notices_path = __DIR__ . '/notices_component.php';
}
if (file_exists($notices_path)) {
    require_once $notices_path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - NIT College</title>
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #123899ff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
        }
        .particles {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0; pointer-events: none;
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
        .navbar {
            background: rgba(26, 31, 58, 0.95);
            backdrop-filter: blur(20px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-brand { display: flex; align-items: center; gap: 15px; }
        .navbar-logo {
            width: 50px; height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #123899ff, #f5576c);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            animation: rotateLogo 10s linear infinite;
        }
        @keyframes rotateLogo {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .navbar h1 {
            color: white; font-size: 24px; font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .user-info { display: flex; align-items: center; gap: 15px; color: white; }
        .user-profile {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px; border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .main-content {
            padding: 40px; max-width: 1600px;
            margin: 0 auto; position: relative; z-index: 1;
        }
        .glass-clock {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            padding: 30px; border-radius: 25px; text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            min-width: 280px;
            animation: slideInRight 0.8s ease-out;
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .clock-icon { font-size: 48px; margin-bottom: 15px; animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .glass-clock .time {
            font-size: 48px; font-weight: 800;
            font-family: 'Courier New', monospace;
            color: #2c3e50; letter-spacing: 2px;
        }
        .glass-clock .date { font-size: 14px; color: #666; margin-top: 10px; font-weight: 500; }
        .tip-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 50px; border-radius: 30px; margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative; overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        .tip-background {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg,  #123899ff, 0%, #f5576c 50%, #4facfe 100%);
            opacity: 0.1; z-index: 0;
        }
        .tip-content {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: 1fr auto;
            gap: 40px; align-items: center;
        }
        .tip-text-area h3 {
            font-size: 28px;
            background: linear-gradient(135deg, #123899ff, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px; font-weight: 800;
        }
        .tip-text {
            font-size: 20px; color: #2c3e50;
            line-height: 1.8; font-weight: 500;
            padding-left: 30px; border-left: 4px solid #123899ff;
        }
        /* Father's Love Section */
        .father-love-section {
            background: linear-gradient(135deg, #123899ff, #123899ff);
            backdrop-filter: blur(20px);
            padding: 50px; border-radius: 30px; margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative; overflow: hidden;
            border: 3px solid rgba(255, 107, 107, 0.3);
        }
        .father-love-content {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: auto 1fr auto;
            gap: 40px; align-items: center;
        }
        .father-icon { font-size: 100px; animation: heartbeat 1.5s ease-in-out infinite; }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1); }
            50% { transform: scale(1); }
            75% { transform: scale(1.15); }
        }
        .father-love-text h3 {
            font-size: 32px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a, #ff8e53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px; font-weight: 800;
        }
        .father-quote {
            font-size: 22px; color: #5a3e36;
            line-height: 1.8; font-style: italic;
            padding: 25px; background: rgba(255, 255, 255, 0.6);
            border-radius: 20px; border-left: 5px solid #ff6b6b;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .father-quote-author {
            margin-top: 15px; text-align: right;
            color: #888; font-size: 14px; font-weight: 600;
        }
        .love-stats {
            display: flex; flex-direction: column; gap: 15px;
            background: rgba(255, 255, 255, 0.7);
            padding: 25px; border-radius: 20px; text-align: center;
        }
        .love-stat-item { display: flex; flex-direction: column; gap: 5px; }
        .love-stat-icon { font-size: 30px; }
        .love-stat-label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .love-stat-value { font-size: 18px; font-weight: 700; color: #ff6b6b; }
        .floating-hearts {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none; overflow: hidden;
        }
        .floating-heart {
            position: absolute; font-size: 20px;
            animation: floatHeart 6s ease-in-out infinite;
            opacity: 0.6;
        }
        @keyframes floatHeart {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-200px) rotate(360deg); opacity: 0; }
        }
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px; padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            margin: 30px 0;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        .profile-card h2 {
            font-size: 28px;
            background: linear-gradient(135deg, #123899ff, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 25px; font-weight: 800;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px; margin-top: 20px;
        }
        .profile-item {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.05), rgba(245, 87, 108, 0.05));
            padding: 15px 20px; border-radius: 12px;
            border-left: 4px solid #123899ff;
        }
        .profile-item strong {
            color: #123899ff; display: block; margin-bottom: 5px;
            font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px; margin: 40px 0;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 30px; border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            position: relative; overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 100%; height: 4px;
            background: linear-gradient(90deg, #123899ff, #f5576c, #4facfe);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(240, 147, 251, 0.4);
        }
        .stat-card h3 {
            color: #666; font-size: 13px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;
        }
        .stat-value {
            font-size: 48px; font-weight: 800;
            background: linear-gradient(135deg, #123899ff, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .alert {
            padding: 20px 30px; border-radius: 15px;
            margin: 30px 0; animation: slideDown 0.5s ease-out;
            backdrop-filter: blur(10px); border: 2px solid;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success { background: rgba(212, 237, 218, 0.95); border-color: #28a745; color: #155724; }
        .alert-warning { background: rgba(255, 243, 205, 0.95); border-color: #ffc107; color: #856404; }
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px; padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            margin: 30px 0;
            border: 2px solid rgba(255, 255, 255, 0.5);
            overflow-x: auto;
        }
        .table-container h3 { font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        thead { background: linear-gradient(135deg, #123899ff, #f5576c); }
        thead th {
            padding: 15px 12px; color: white; font-weight: 600;
            text-align: left; font-size: 13px;
            text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
        }
        tbody tr { border-bottom: 1px solid #f0f0f0; transition: all 0.3s; }
        tbody tr:hover { background: rgba(240, 147, 251, 0.05); transform: translateX(5px); }
        tbody td { padding: 16px 12px; color: #2c3e50; font-size: 13px; }
        .badge {
            padding: 6px 14px; border-radius: 20px; font-size: 11px;
            font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; display: inline-block;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .btn {
            padding: 12px 24px; border-radius: 12px;
            text-decoration: none; font-weight: 600;
            transition: all 0.3s; display: inline-block;
            border: none; cursor: pointer; font-size: 14px;
            text-align: center; margin: 5px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #123899ff, #f5576c);
            color: white; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(240, 147, 251, 0.6); }
        .btn-info {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
        }
        .btn-info:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6); }
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6); }
        .teacher-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; padding: 4px 10px; border-radius: 15px;
            font-size: 11px; font-weight: 600;
        }
        .class-badge {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white; padding: 4px 10px; border-radius: 15px;
            font-size: 11px; font-weight: 600;
        }
        .dept-badge {
            background: linear-gradient(135deg, #123899ff, #f5576c);
            color: white; padding: 4px 10px; border-radius: 15px;
            font-size: 11px; font-weight: 600;
        }
        .time-badge {
            background: rgba(102, 126, 234, 0.1); color: #667eea;
            padding: 4px 10px; border-radius: 15px;
            font-size: 11px; font-weight: 600;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        .footer {
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #2a3254 100%);
            position: relative; overflow: hidden; margin-top: 60px;
        }
        .footer-border {
            height: 2px;
            background: linear-gradient(90deg, #123899ff, #f5576c, #4facfe);
            background-size: 200% 100%;
            animation: borderMove 3s linear infinite;
        }
        @keyframes borderMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
       /* Compact Navbar - All in One Row */
.navbar {
    background: rgba(26, 31, 58, 0.95);
    backdrop-filter: blur(20px);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: nowrap;
    gap: 20px;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.navbar-logo {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #123899ff, #f5576c);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    animation: rotateLogo 10s linear infinite;
    flex-shrink: 0;
}

.navbar h1 {
    color: white;
    font-size: 20px;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    white-space: nowrap;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    flex-wrap: nowrap;
    flex-shrink: 1;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 15px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    white-space: nowrap;
    font-size: 13px;
}

.btn {
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    cursor: pointer;
    font-size: 13px;
    text-align: center;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-primary {
    background: linear-gradient(135deg, #123899ff, #f5576c);
    color: white;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(240, 147, 251, 0.6);
}

.btn-info {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
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

@keyframes rotateLogo {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .navbar {
        padding: 12px 15px;
        gap: 8px;
    }

    .navbar-logo {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .navbar h1 {
        font-size: 16px;
    }

    .btn {
        padding: 8px 12px;
        font-size: 11px;
    }

    .user-profile {
        padding: 6px 10px;
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 10px 10px;
        gap: 6px;
    }

    .navbar h1 {
        display: none;
    }

    .navbar-logo {
        width: 35px;
        height: 35px;
        font-size: 16px;
    }

    .user-profile span:last-child {
        display: none;
    }

    .btn {
        padding: 6px 10px;
        font-size: 10px;
    }
}/* ===== BASE STYLES ===== */
.tip-container {
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

.tip-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #123899ff 0%, #f5576c 50%, #4facfe 100%);
    opacity: 0.1;
    z-index: 0;
}

.tip-content {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 40px;
    align-items: flex-start;
}

.tip-text-area h3 {
    font-size: 28px;
    background: linear-gradient(135deg, #123899ff, #f5576c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    font-weight: 800;
}

.tip-text {
    font-size: 20px;
    color: #2c3e50;
    line-height: 1.8;
    font-weight: 500;
    padding-left: 30px;
    border-left: 4px solid #123899ff;
}

.glass-clock {
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 25px;
    text-align: center;
    border: 2px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    min-width: 280px;
    animation: slideInRight 0.8s ease-out;
}

.clock-icon {
    font-size: 48px;
    margin-bottom: 15px;
    animation: pulse 2s ease-in-out infinite;
}

.glass-clock .time {
    font-size: 48px;
    font-weight: 800;
    font-family: 'Courier New', monospace;
    color: #2c3e50;
    letter-spacing: 2px;
}

.glass-clock .date {
    font-size: 14px;
    color: #666;
    margin-top: 10px;
    font-weight: 500;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* ===== MOBILE DEVICES (320px - 480px) ===== */
@media (max-width: 480px) {
    .tip-container {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 20px;
    }

    .tip-content {
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: center;
    }

    .tip-text-area h3 {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .tip-text {
        font-size: 14px;
        line-height: 1.6;
        padding-left: 15px;
        border-left: 3px solid #123899ff;
    }

    .glass-clock {
        min-width: 100%;
        padding: 20px;
        border-radius: 15px;
    }

    .clock-icon {
        font-size: 36px;
        margin-bottom: 10px;
    }

    .glass-clock .time {
        font-size: 32px;
        letter-spacing: 1px;
    }

    .glass-clock .date {
        font-size: 12px;
        margin-top: 8px;
    }
}

/* ===== SMALL TABLETS (481px - 768px) ===== */
@media (max-width: 768px) and (min-width: 481px) {
    .tip-container {
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 25px;
    }

    .tip-content {
        grid-template-columns: 1fr;
        gap: 25px;
        align-items: center;
    }

    .tip-text-area h3 {
        font-size: 24px;
        margin-bottom: 18px;
    }

    .tip-text {
        font-size: 16px;
        line-height: 1.7;
        padding-left: 20px;
        border-left: 4px solid #123899ff;
    }

    .glass-clock {
        min-width: 100%;
        max-width: 300px;
        padding: 25px;
        margin: 0 auto;
        border-radius: 20px;
    }

    .clock-icon {
        font-size: 40px;
        margin-bottom: 12px;
    }

    .glass-clock .time {
        font-size: 40px;
        letter-spacing: 1.5px;
    }

    .glass-clock .date {
        font-size: 13px;
        margin-top: 10px;
    }
}

/* ===== TABLETS (769px - 1024px) ===== */
@media (max-width: 1024px) and (min-width: 769px) {
    .tip-container {
        padding: 40px;
        margin-bottom: 35px;
        border-radius: 28px;
    }

    .tip-content {
        grid-template-columns: 1fr 280px;
        gap: 35px;
        align-items: flex-start;
    }

    .tip-text-area h3 {
        font-size: 26px;
        margin-bottom: 20px;
    }

    .tip-text {
        font-size: 17px;
        line-height: 1.75;
        padding-left: 25px;
        border-left: 4px solid #123899ff;
    }

    .glass-clock {
        min-width: 100%;
        padding: 28px;
        border-radius: 22px;
    }

    .clock-icon {
        font-size: 44px;
        margin-bottom: 14px;
    }

    .glass-clock .time {
        font-size: 44px;
        letter-spacing: 2px;
    }

    .glass-clock .date {
        font-size: 13px;
        margin-top: 10px;
    }
}

/* ===== MEDIUM SCREENS (1025px - 1440px) ===== */
@media (max-width: 1440px) and (min-width: 1025px) {
    .tip-container {
        padding: 45px;
        margin-bottom: 40px;
        border-radius: 30px;
    }

    .tip-content {
        grid-template-columns: 1fr auto;
        gap: 40px;
        align-items: flex-start;
    }

    .tip-text-area h3 {
        font-size: 28px;
        margin-bottom: 22px;
    }

    .tip-text {
        font-size: 18px;
        line-height: 1.8;
        padding-left: 30px;
        border-left: 4px solid #123899ff;
    }

    .glass-clock {
        min-width: 280px;
        padding: 30px;
        border-radius: 25px;
    }

    .clock-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .glass-clock .time {
        font-size: 48px;
        letter-spacing: 2px;
    }

    .glass-clock .date {
        font-size: 14px;
        margin-top: 10px;
    }
}

/* ===== LARGE SCREENS (1441px and above) ===== */
@media (min-width: 1441px) {
    .tip-container {
        padding: 50px;
        margin-bottom: 40px;
        border-radius: 30px;
    }

    .tip-content {
        grid-template-columns: 1fr auto;
        gap: 40px;
        align-items: flex-start;
    }

    .tip-text-area h3 {
        font-size: 28px;
        margin-bottom: 20px;
    }

    .tip-text {
        font-size: 20px;
        line-height: 1.8;
        padding-left: 30px;
        border-left: 4px solid #123899ff;
    }

    .glass-clock {
        min-width: 280px;
        padding: 30px;
        border-radius: 25px;
    }

    .clock-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .glass-clock .time {
        font-size: 48px;
        letter-spacing: 2px;
    }

    .glass-clock .date {
        font-size: 14px;
        margin-top: 10px;
    }
}

/* ===== LANDSCAPE MODE (Mobile) ===== */
@media (max-height: 600px) and (orientation: landscape) {
    .tip-container {
        padding: 15px 20px;
        margin-bottom: 15px;
        border-radius: 15px;
    }

    .tip-content {
        grid-template-columns: 1fr auto;
        gap: 20px;
    }

    .tip-text-area h3 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    .tip-text {
        font-size: 13px;
        line-height: 1.5;
        padding-left: 12px;
        border-left: 3px solid #123899ff;
    }

    .glass-clock {
        min-width: 200px;
        padding: 15px;
        border-radius: 12px;
    }

    .clock-icon {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .glass-clock .time {
        font-size: 28px;
        letter-spacing: 1px;
    }

    .glass-clock .date {
        font-size: 10px;
        margin-top: 5px;
    }
}

/* ===== ULTRA-WIDE SCREENS (2560px and above) ===== */
@media (min-width: 2560px) {
    .tip-container {
        padding: 60px;
        margin-bottom: 50px;
        border-radius: 40px;
    }

    .tip-content {
        grid-template-columns: 1fr 350px;
        gap: 50px;
    }

    .tip-text-area h3 {
        font-size: 36px;
        margin-bottom: 25px;
    }

    .tip-text {
        font-size: 24px;
        line-height: 1.9;
        padding-left: 35px;
        border-left: 5px solid #123899ff;
    }

    .glass-clock {
        min-width: 320px;
        padding: 35px;
        border-radius: 30px;
    }

    .clock-icon {
        font-size: 56px;
        margin-bottom: 18px;
    }

    .glass-clock .time {
        font-size: 56px;
        letter-spacing: 3px;
    }

    .glass-clock .date {
        font-size: 16px;
        margin-top: 12px;
    }
}
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle" style="width: 10px; height: 10px; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 15px; height: 15px; left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 8px; height: 8px; left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 12px; height: 12px; left: 40%; animation-delay: 6s;"></div>
        <div class="particle" style="width: 10px; height: 10px; left: 50%; animation-delay: 8s;"></div>
        <div class="particle" style="width: 14px; height: 14px; left: 60%; animation-delay: 10s;"></div>
        <div class="particle" style="width: 9px; height: 9px; left: 70%; animation-delay: 12s;"></div>
        <div class="particle" style="width: 11px; height: 11px; left: 80%; animation-delay: 14s;"></div>
    </div>

    <nav class="navbar">
        <div class="navbar-brand">
            <div class="navbar-logo">👨‍👩‍👦</div>
            <h1>NIT AMMS - Parent Portal</h1>
        </div>
        <div class="user-info">
            <a href="profile.php" class="btn btn-info">👤 My Profile</a>
            <div class="user-profile">
                <span>👨‍👩‍👦</span>
                <span><?php echo htmlspecialchars($parent['parent_name'] ?? 'Parent'); ?></span>
            </div>
            <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <!-- Parenting Tip Section -->
        <div class="tip-container">
            <div class="tip-background"></div>
            <div class="tip-content">
                <div class="tip-text-area">
                    <h3>💡 Daily Parenting Tip</h3>
                    <p class="tip-text"><?php echo htmlspecialchars($daily_tip['tip']); ?></p>
                </div>
                <div class="glass-clock">
                    <div class="clock-icon">⏰</div>
                    <div class="time" id="clock">--:--:--</div>
                    <div class="date" id="date">Loading...</div>
                </div>
            </div>
        </div>

       
        <!-- Child's Profile Card -->
        <div class="profile-card">
            <h2>👤 Your Child's Profile</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <strong>Full Name</strong>
                    <div><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <strong>Roll Number</strong>
                    <div><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></div>
                </div>
                
                <div class="profile-item">
                    <strong>Phone</strong>
                    <div><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <strong>Department</strong>
                    <div><?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <strong>Class/Section</strong>
                    <div><?php echo $display_section; ?></div>
                </div>
                <div class="profile-item">
                    <strong>Year</strong>
                    <div><?php echo htmlspecialchars($student['year'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <strong>Semester</strong>
                    <div><?php echo htmlspecialchars($student['semester'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <strong>Status</strong>
                    <div>
                        <?php if (isset($student['is_active']) && $student['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>






            <!-- Quick Actions Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 30px 0; padding: 0;">
    
    <a href="view_child_paper_marks.php" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 15px; text-decoration: none; display: flex; align-items: center; gap: 15px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); transition: all 0.3s ease; border: 2px solid rgba(255, 255, 255, 0.2);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(102, 126, 234, 0.3)';">
        <span style="font-size: 32px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📊</span>
        <span style="font-weight: 600; font-size: 15px;"> 📊 Child's Marks</span>
    </a>

    <a href="new.php" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: 15px; text-decoration: none; display: flex; align-items: center; gap: 15px; box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3); transition: all 0.3s ease; border: 2px solid rgba(255, 255, 255, 0.2);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(240, 147, 251, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(240, 147, 251, 0.3)';">
        <span style="font-size: 32px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📢</span>
        <span style="font-weight: 600; font-size: 15px;">Daily  News</span>
    </a>

    <a href="teachercall.php" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; padding: 20px; border-radius: 15px; text-decoration: none; display: flex; align-items: center; gap: 15px; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3); transition: all 0.3s ease; border: 2px solid rgba(255, 255, 255, 0.2);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(79, 172, 254, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(79, 172, 254, 0.3)';">
        <span style="font-size: 32px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📞</span>
        <span style="font-weight: 600; font-size: 15px;">Teacher Call</span>
    </a>

    <a href="dailytimetable.php" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; padding: 20px; border-radius: 15px; text-decoration: none; display: flex; align-items: center; gap: 15px; box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3); transition: all 0.3s ease; border: 2px solid rgba(255, 255, 255, 0.2);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(67, 233, 123, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(67, 233, 123, 0.3)';">
        <span style="font-size: 32px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📅</span>
        <span style="font-weight: 600; font-size: 15px;">Daily class timetable</span>
    </a>


   

</div>


<!-- Add responsive media query styling -->
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        a[style*="padding: 20px"] {
            padding: 15px !important;
            font-size: 14px !important;
        }
        
        span[style*="font-size: 32px"] {
            font-size: 28px !important;
            width: 45px !important;
            height: 45px !important;
        }
    }
</style>
          <!-- DISPLAY NOTICES HERE -->
        <?php 
        if (function_exists('displayNotices')) {
            displayNotices('parent'); 
        }
        ?>

        <!-- Today's Attendance Alert -->
        <?php if ($today_attendance): ?>
            <?php $alert_class = ($today_attendance['status'] === 'present') ? 'success' : 'warning'; ?>
            <div class="alert alert-<?php echo $alert_class; ?>">
                <?php if ($today_attendance['status'] === 'present'): ?>
                    <strong>✅ Your child was marked PRESENT today</strong>
                <?php elseif ($today_attendance['status'] === 'absent'): ?>
                    <strong>❌ Your child was marked ABSENT today</strong>
                <?php else: ?>
                    <strong>⏰ Your child was marked LATE today</strong>
                <?php endif; ?>
                <?php if (!empty($today_attendance['remarks'])): ?>
                    <br><strong>Remarks:</strong> <?php echo htmlspecialchars($today_attendance['remarks']); ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>⚠️ Attendance not marked yet for today</strong>
            </div>
        <?php endif; ?>

        <!-- Monthly Statistics -->
        <h3 style="font-size: 28px; background: linear-gradient(135deg, #123899ff, #f5576c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 800; margin: 40px 0 20px;">📊 This Month's Attendance Statistics</h3>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📅 Total Classes</h3>
                <div class="stat-value"><?php echo $total_days; ?></div>
            </div>
            <div class="stat-card">
                <h3>✅ Present</h3>
                <div class="stat-value" style="color: #28a745;"><?php echo $month_stats['present'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>❌ Absent</h3>
                <div class="stat-value" style="color: #dc3545;"><?php echo $month_stats['absent'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>⏰ Late</h3>
                <div class="stat-value" style="color: #ffc107;"><?php echo $month_stats['late'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>📈 Attendance %</h3>
                <div class="stat-value" style="color: <?php echo $attendance_percentage >= 75 ? '#28a745' : '#dc3545'; ?>">
                    <?php echo $attendance_percentage; ?>%
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📊 Overall Statistics</h3>
                <p style="margin: 10px 0;"><strong>Total Days:</strong> <?php echo $overall_total; ?></p>
                <p style="margin: 10px 0;"><strong>Present:</strong> <span style="color: #28a745; font-weight: 700;"><?php echo $overall_stats['present'] ?? 0; ?></span></p>
                <p style="margin: 10px 0;"><strong>Absent:</strong> <span style="color: #dc3545; font-weight: 700;"><?php echo $overall_stats['absent'] ?? 0; ?></span></p>
                <p style="margin: 10px 0;"><strong>Late:</strong> <span style="color: #ffc107; font-weight: 700;"><?php echo $overall_stats['late'] ?? 0; ?></span></p>
                <p style="margin: 10px 0;"><strong>Overall %:</strong> 
                    <span style="color: <?php echo $overall_percentage >= 75 ? '#28a745' : '#dc3545'; ?>; font-size: 24px; font-weight: 800;">
                        <?php echo $overall_percentage; ?>%
                    </span>
                </p>
                <?php if ($overall_percentage < 75): ?>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107; border-radius: 8px;">
                        <strong style="color: #856404;">⚠️ Attention Required</strong>
                        <p style="margin: 5px 0 0; color: #856404; font-size: 13px;">Your child's attendance is below 75%.</p>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(40, 167, 69, 0.1); border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #155724;">✅ Excellent!</strong>
                        <p style="margin: 5px 0 0; color: #155724; font-size: 13px;">Your child maintains good attendance!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Attendance Table -->
        <div class="table-container">
            <h3>📝 Recent Attendance Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>📅 Date</th>
                        <th>📆 Day</th>
                        <th>👨‍🏫 Marked By</th>
                        <th>🏛️ Class</th>
                        <th>🎓 Department</th>
                        <th>✓ Status</th>
                        <th>📝 Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_attendance && $recent_attendance->num_rows > 0): ?>
                        <?php while ($record = $recent_attendance->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo date('d M Y', strtotime($record['attendance_date'])); ?></strong></td>
                            <td><?php echo date('l', strtotime($record['attendance_date'])); ?></td>
                            <td><span class="teacher-badge"><?php echo htmlspecialchars($record['marked_by'] ?? 'System'); ?></span></td>
                            <td><span class="class-badge"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A') . ' - ' . htmlspecialchars($student['section'] ?? ''); ?></span></td>
                            <td><span class="dept-badge"><?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php
                                $status = $record['status'] ?? 'unknown';
                                if ($status === 'present') { $sc = 'badge-success'; $si = '✅'; }
                                elseif ($status === 'absent') { $sc = 'badge-danger'; $si = '❌'; }
                                else { $sc = 'badge-warning'; $si = '⏰'; }
                                ?>
                                <span class="badge <?php echo $sc; ?>"><?php echo $si . ' ' . strtoupper($status); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">No attendance records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin: 40px 0;">
            <a href="attendance_report.php" class="btn btn-primary">📊 View Detailed Report</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-border"></div>
        <div style="max-width: 1000px; margin: 0 auto; padding: 30px 20px 20px;">
            <div style="background: rgba(255, 255, 255, 0.03); padding: 20px; border-radius: 15px; border: 1px solid rgba(240, 147, 251, 0.15); text-align: center;">
                <p style="color: #ffffff; font-size: 14px; margin: 0 0 12px;">💻 Intellectual property owned by</p>
                <div style="margin-top: 12px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                    <a href="https://himanshufullstackdeveloper.github.io/hp3/" style="color: #ffffff; font-size: 13px; text-decoration: none; padding: 8px 16px; background: linear-gradient(135deg, rgba(240, 147, 251, 0.25), rgba(245, 87, 108, 0.25)); border-radius: 20px; border: 1px solid rgba(240, 147, 251, 0.4);">✨
HP3 Technologies</a>
                 </div>
            </div>
            <div style="margin-top: 25px; text-align: center;">
                <p style="color: #888; font-size: 12px;">© 2025 NIT AMMS. All rights reserved.</p>
                  </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = h + ':' + m + ':' + s;
            const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date').textContent = now.toLocaleDateString('en-US', opts);
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>