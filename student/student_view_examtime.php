<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../db.php';

// Check if student is logged in
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Get student info
$student_id = $_SESSION['user_id'];
$student_query = "SELECT s.*, c.class_name, d.dept_name 
                  FROM students s 
                  LEFT JOIN classes c ON s.class_id = c.id 
                  LEFT JOIN departments d ON s.department_id = d.id 
                  WHERE s.id = $student_id";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);

$department_id = $student['department_id'] ?? null;

// Get selected section from GET parameter or default to 'All'
$selected_section = isset($_GET['section']) ? mysqli_real_escape_string($conn, $_GET['section']) : 'All';

// Fetch all unique sections for the dropdown
$sections_query = "SELECT DISTINCT section FROM nit_importnoticess WHERE department_id = '$department_id' ORDER BY section ASC";
$sections_result = mysqli_query($conn, $sections_query);

// Extract unique individual sections from comma-separated values
$unique_sections = [];
if($sections_result) {
    while($row = mysqli_fetch_assoc($sections_result)) {
        $sections_array = preg_split('/[,&]+/', $row['section']);
        foreach($sections_array as $sec) {
            $sec = trim($sec);
            if($sec && !in_array($sec, $unique_sections)) {
                $unique_sections[] = $sec;
            }
        }
    }
    sort($unique_sections);
}

// Fetch exam schedules for student's department with optional section filter
if($selected_section === 'All') {
    $fetch_query = "SELECT * FROM nit_importnoticess WHERE department_id = '$department_id' ORDER BY exam_date ASC, time ASC";
} else {
    $fetch_query = "SELECT * FROM nit_importnoticess 
                    WHERE department_id = '$department_id' 
                    AND (section LIKE '%$selected_section%' 
                         OR section = '$selected_section')
                    ORDER BY exam_date ASC, time ASC";
}
$result = mysqli_query($conn, $fetch_query);

// Get statistics
$total_exams = mysqli_num_rows($result);
$upcoming_exams = 0;
$today = date('Y-m-d');

mysqli_data_seek($result, 0);
while($row = mysqli_fetch_assoc($result)) {
    if($row['exam_date'] >= $today) {
        $upcoming_exams++;
    }
}
mysqli_data_seek($result, 0);

// Handle photo path
$photo_path = '../uploads/student_photos/default-avatar.png'; // Default photo
if(!empty($student['photo'])) {
    $student_photo = '../uploads/student_photos/' . $student['photo'];
    if(file_exists($student_photo)) {
        $photo_path = $student_photo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
    <title>Student - Exam Schedule</title>
    <link rel="stylesheet" href="css/exam_schedule.css">
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.header {
    text-align: center;
    margin-bottom: 40px;
}

h1 {
    color: #333;
    font-size: 36px;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.subtitle {
    color: #666;
    font-size: 16px;
}

.student-info {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    gap: 25px;
    align-items: center;
}

.student-photo-section {
    flex-shrink: 0;
}

.photo-wrapper {
    position: relative;
    display: inline-block;
}

.student-photo {
    width: 120px;
    height: 120px;
    border-radius: 12px;
    object-fit: cover;
    border: 4px solid #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    display: block;
}

.upload-photo-btn {
    position: absolute;
    bottom: -10px;
    right: -10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: 3px solid white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-photo-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    background: linear-gradient(135deg, #764ba2, #667eea);
}

.upload-photo-btn:active {
    transform: scale(0.95);
}

.student-details-grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 12px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 16px;
    color: #333;
    font-weight: 600;
}

.nav-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

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

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.filter-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.filter-label {
    font-size: 14px;
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
    display: block;
}

.filter-group {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.section-select {
    padding: 12px 20px;
    border: 2px solid #667eea;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    min-width: 200px;
}

.section-select:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.filter-btn {
    padding: 12px 28px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.reset-btn {
    padding: 12px 28px;
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.reset-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.stats-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.stat-number {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.95;
    font-weight: 500;
}

.table-container {
    overflow-x: auto;
    margin-top: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

thead th {
    padding: 16px 12px;
    color: white;
    text-align: left;
    font-weight: 600;
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
    padding: 16px 12px;
    color: #333;
    font-size: 14px;
}

.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-marks {
    background: #ffc107;
    color: #000;
}

.badge-upcoming {
    background: #28a745;
    color: white;
}

.badge-today {
    background: #ff6b6b;
    color: white;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.badge-past {
    background: #6c757d;
    color: white;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.no-data-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.download-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    margin-top: 30px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-hall-ticket {
    margin-left: 15px;
    background: linear-gradient(135deg, #28a745, #20c997);
}

.btn-hall-ticket:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

@media print {
    body {
        background: white;
        padding: 0;
    }
    .container {
        box-shadow: none;
        padding: 20px;
    }
    .download-btn, .nav-buttons, .stats-bar, .filter-section {
        display: none;
    }
    .student-info {
        background: white;
        border: 2px solid #333;
        page-break-inside: avoid;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 20px;
    }
    
    h1 {
        font-size: 28px;
    }
    
    .student-info {
        flex-direction: column;
        text-align: center;
    }
    
    .student-details-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-bar {
        grid-template-columns: 1fr;
    }
    
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .section-select {
        width: 100%;
    }
    
    table {
        font-size: 12px;
    }
    
    thead th, tbody td {
        padding: 10px 8px;
    }
    
    .download-btn {
        width: 100%;
        justify-content: center;
        margin-left: 0 !important;
        margin-top: 10px;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 My Examination Schedule</h1>
            <p class="subtitle">Student Dashboard - View Your Upcoming Exams</p>
        </div>
        
        <div class="student-info">
            <div class="student-photo-section">
                <div class="photo-wrapper">
                    <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Student Photo" class="student-photo" id="studentPhoto">
                    <form id="photoForm" method="POST" action="../uploads_photo.php" enctype="multipart/form-data" style="display: inline;">
                        <input type="hidden" name="user_type" value="student">
                        <input type="hidden" name="user_id" value="<?php echo $student_id; ?>">
                        <input type="file" 
                               name="photo" 
                               id="photoInput" 
                               accept="image/*" 
                               style="display: none;"
                               onchange="document.getElementById('photoForm').submit();">
                        <button type="button" 
                                class="upload-photo-btn" 
                                onclick="document.getElementById('photoInput').click();"
                                title="Upload Photo">
                            📷
                        </button>
                    </form>
                </div>
            </div>
            <div class="student-details-grid">
                <div class="info-item">
                    <span class="info-label">Student Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Roll Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['roll_number']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="nav-buttons">
            <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
            <a href="view_child_paper_marks.php" class="btn btn-primary">📊 View Marks</a>
        </div>
        
        <!-- Section Filter -->
        <div class="filter-section">
            <label class="filter-label">🔍 Filter by Section:</label>
            <form method="GET" action="">
                <div class="filter-group">
                    <select name="section" class="section-select" id="sectionSelect">
                        <option value="All" <?php echo $selected_section === 'All' ? 'selected' : ''; ?>>All Sections</option>
                        <?php foreach($unique_sections as $section): ?>
                            <option value="<?php echo htmlspecialchars($section); ?>" 
                                    <?php echo $selected_section === $section ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="filter-btn">Apply Filter</button>
                    <?php if($selected_section !== 'All'): ?>
                        <a href="?" class="reset-btn">Clear Filter</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_exams; ?></div>
                <div class="stat-label">Total Exams<?php echo $selected_section !== 'All' ? ' ('.htmlspecialchars($selected_section).')' : ''; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $upcoming_exams; ?></div>
                <div class="stat-label">Upcoming Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_exams - $upcoming_exams; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        
        <div class="table-container">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sr.No</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Section</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Marks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sr_no = 1;
                        mysqli_data_seek($result, 0);
                        while($row = mysqli_fetch_assoc($result)): 
                            $exam_date = $row['exam_date'];
                            $is_today = $exam_date === $today;
                            $is_upcoming = $exam_date > $today;
                        ?>
                        <tr>
                            <td><strong><?php echo $sr_no++; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($exam_date)); ?></td>
                            <td><?php echo htmlspecialchars($row['section']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['day']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                            <td><span class="badge badge-marks"><?php echo htmlspecialchars($row['marks']); ?> Marks</span></td>
                            <td>
                                <?php if($is_today): ?>
                                    <span class="badge badge-today">Today</span>
                                <?php elseif($is_upcoming): ?>
                                    <span class="badge badge-upcoming">Upcoming</span>
                                <?php else: ?>
                                    <span class="badge badge-past">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <center>
                    <button class="download-btn" onclick="window.print()">
                        <span>📥</span>
                        <span>Download Schedule</span>
                    </button>
                    <button class="download-btn btn-hall-ticket" onclick="downloadHallTicket()">
                        <span>🎫</span>
                        <span>Download Hall Ticket</span>
                    </button>
                </center>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📅</div>
                    <h3>No Exam Schedules Available</h3>
                    <p>
                        <?php if($selected_section !== 'All'): ?>
                            No exams found for section "<?php echo htmlspecialchars($selected_section); ?>". Try selecting a different section or clear the filter.
                        <?php else: ?>
                            Your exam schedule will appear here once it's published by your HOD
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Hidden data for JavaScript -->
    <div id="studentData" style="display:none;"
         data-name="<?php echo htmlspecialchars($student['full_name']); ?>"
         data-roll="<?php echo htmlspecialchars($student['roll_number']); ?>"
         data-class="<?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?>"
         data-dept="<?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?>"
         data-photo="<?php echo htmlspecialchars($photo_path); ?>">
    </div>
    
    <div id="examData" style="display:none;">
        <?php 
        mysqli_data_seek($result, 0);
        $exam_array = [];
        $sr_counter = 1;
        while($row = mysqli_fetch_assoc($result)): 
            $exam_array[] = [
                'srNo' => $sr_counter++,
                'subject' => $row['subject'],
                'date' => date('d/m/Y', strtotime($row['exam_date'])),
                'section' => $row['section'],
                'day' => $row['day'],
                'time' => $row['time'],
                'marks' => $row['marks']
            ];
        endwhile;
        echo htmlspecialchars(json_encode($exam_array));
        ?>
    </div>
    
    <script src="js/hall_ticket.js"></script>
    <script>
        function downloadHallTicket() {
    // Get student data from hidden div
    const studentDataEl = document.getElementById('studentData');
    const studentName = studentDataEl.dataset.name;
    const rollNumber = studentDataEl.dataset.roll;
    const className = studentDataEl.dataset.class;
    const department = studentDataEl.dataset.dept;
    const photoPath = studentDataEl.dataset.photo;
    
    // Get exam data from hidden div
    const examDataEl = document.getElementById('examData');
    const examData = JSON.parse(examDataEl.textContent);
    
    // Convert image to base64 for embedding in hall ticket
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = function() {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const photoBase64 = canvas.toDataURL('image/jpeg');
        
        // Generate hall ticket with embedded photo
        generateHallTicket(studentName, rollNumber, className, department, photoBase64, examData);
    };
    
    img.onerror = function() {
        // If image fails to load, use placeholder
        const photoBase64 = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBQaG90bzwvdGV4dD48L3N2Zz4=';
        generateHallTicket(studentName, rollNumber, className, department, photoBase64, examData);
    };
    
    img.src = photoPath;
}

function generateHallTicket(studentName, rollNumber, className, department, photoBase64, examData) {
    // Create hall ticket HTML with embedded photo
    let hallTicketHTML = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hall Ticket - ${rollNumber}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.6;
        }
        .hall-ticket-container {
            border: 3px solid #000;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 5px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 14pt;
            margin: 5px 0;
            font-weight: bold;
        }
        .header h3 {
            font-size: 12pt;
            margin: 5px 0;
            color: #0066cc;
            font-weight: bold;
        }
        .title {
            font-size: 13pt;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            text-align: center;
            background-color: #f0f0f0;
            padding: 10px;
            border: 1px solid #000;
        }
        .note {
            font-size: 11pt;
            margin: 10px 0 20px 0;
            font-style: italic;
            text-align: center;
        }
        .student-info-container {
            margin: 20px 0;
            border: 2px solid #000;
            padding: 20px;
            background-color: #f9f9f9;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .student-photo {
            flex-shrink: 0;
            border: 2px solid #000;
            padding: 5px;
            background: white;
        }
        .student-photo img {
            width: 120px;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .student-details {
            flex: 1;
        }
        .info-row {
            margin: 10px 0;
            display: flex;
            border-bottom: 1px dotted #666;
            padding-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
            font-size: 11pt;
        }
        .info-value {
            flex: 1;
            font-size: 11pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #e0e0e0;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }
        td {
            padding: 8px;
            text-align: center;
            font-size: 10pt;
        }
        .instructions {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff9e6;
            border: 1px solid #000;
        }
        .instructions h4 {
            margin-bottom: 10px;
            font-size: 11pt;
            text-decoration: underline;
        }
        .instructions ul {
            margin-left: 20px;
            font-size: 10pt;
        }
        .instructions li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
        }
        .signature-section {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin: 60px auto 10px auto;
        }
        .signature-label {
            font-size: 10pt;
            font-weight: bold;
        }
        .digital-note {
            text-align: center;
            margin-top: 30px;
            font-size: 9pt;
            font-style: italic;
            color: #666;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
        @media print {
            body {
                padding: 0;
            }
            .hall-ticket-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">HALL TICKET</div>
    <div class="hall-ticket-container">
        <div class="header">
            <h1>NAGPUR INSTITUTE OF TECHNOLOGY, NAGPUR</h1>
            <h2>B.Tech FIRST YEAR (I SEM)</h2>
            <h3>SESSION 2025-26</h3>
        </div>
        
        <div class="title">HALL TICKET - MST-II (SESSIONAL EXAMINATION)</div>
        
        <div class="note">All the students of FY BTECH are informed to note time table of Sessional Examination (II)</div>
        
        <div class="student-info-container">
            <div class="student-photo">
                <img src="${photoBase64}" alt="Student Photo">
            </div>
            <div class="student-details">
                <div class="info-row">
                    <span class="info-label">Student Name:</span>
                    <span class="info-value">${studentName}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Roll Number:</span>
                    <span class="info-value">${rollNumber}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Class:</span>
                    <span class="info-value">${className}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Department:</span>
                    <span class="info-value">${department}</span>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Sr.No</th>
                    <th style="width: 35%;">Subject</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 15%;">Section</th>
                    <th style="width: 12%;">Day</th>
                    <th style="width: 15%;">Time</th>
                </tr>
            </thead>
            <tbody>`;
    
    examData.forEach((exam, index) => {
        hallTicketHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td style="text-align: left; padding-left: 10px;">${exam.subject}</td>
                    <td>${exam.date}</td>
                    <td>${exam.section}</td>
                    <td><strong>${exam.day}</strong></td>
                    <td>${exam.time}</td>
                </tr>`;
    });
    
    hallTicketHTML += `
            </tbody>
        </table>
        
        <div class="instructions">
            <h4>INSTRUCTIONS FOR CANDIDATES:</h4>
            <ul>
                <li>Candidates must bring this hall ticket to the examination hall.</li>
                <li>Candidates should report 15 minutes before the commencement of examination.</li>
                
            </ul>
        </div>
        <div class="digital-note">
            This is a digitally generated hall ticket and does not require any official signature or stamp.<br>
           
    </div>
</body>
</html>`;
    
    // Open new window and print
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    if (printWindow) {
        printWindow.document.write(hallTicketHTML);
        printWindow.document.close();
        
        // Wait for content and images to load then print
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
    } else {
        alert('Please allow pop-ups to download the hall ticket.');
    }
}
    </script>
</body>
</html>