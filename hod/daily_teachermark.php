<?php
require_once '../db.php';
checkRole(['hod']);

$user = getCurrentUser();

// Get statistics - CORRECTED TO SHOW ALL DATA
$stats = [];

// Total students (ALL students, no filter)
$result = $conn->query("SELECT COUNT(*) as count FROM students");
$stats['students'] = $result->fetch_assoc()['count'];

// Total teachers (active only)
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND is_active = 1");
$stats['teachers'] = $result->fetch_assoc()['count'];

// Total HODs (active only)
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'hod' AND is_active = 1");
$stats['hods'] = $result->fetch_assoc()['count'];

// Total classes
$result = $conn->query("SELECT COUNT(*) as count FROM classes");
$stats['classes'] = $result->fetch_assoc()['count'];

// Total departments
$result = $conn->query("SELECT COUNT(*) as count FROM departments");
$stats['departments'] = $result->fetch_assoc()['count'];

// Today's attendance - Present
$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) as count FROM student_attendance WHERE attendance_date = '$today' AND status = 'present'");
$stats['today_present'] = $result->fetch_assoc()['count'];

// Today's attendance - Absent
$result = $conn->query("SELECT COUNT(*) as count FROM student_attendance WHERE attendance_date = '$today' AND status = 'absent'");
$stats['today_absent'] = $result->fetch_assoc()['count'];

// Today's attendance - Late
$result = $conn->query("SELECT COUNT(*) as count FROM student_attendance WHERE attendance_date = '$today' AND status = 'late'");
$stats['today_late'] = $result->fetch_assoc()['count'];

// Total attendance marked today
$result = $conn->query("SELECT COUNT(*) as count FROM student_attendance WHERE attendance_date = '$today'");
$stats['today_total_marked'] = $result->fetch_assoc()['count'];

// Attendance percentage today
$stats['today_percentage'] = $stats['today_total_marked'] > 0 ? round(($stats['today_present'] / $stats['today_total_marked']) * 100, 1) : 0;

// Get all classes with attendance info - FIXED to count students in section properly
$classes_query = "SELECT c.*, u.full_name as teacher_name, d.dept_name,
                  (SELECT COUNT(DISTINCT s.id) FROM students s 
                   WHERE s.class_id IN (SELECT id FROM classes WHERE section = c.section AND year = c.year AND semester = c.semester)
                   AND s.department_id = c.department_id) as student_count,
                  (SELECT COUNT(*) FROM student_attendance WHERE class_id = c.id AND attendance_date = '$today') as today_marked,
                  (SELECT COUNT(*) FROM student_attendance WHERE class_id = c.id AND attendance_date = '$today' AND status = 'present') as today_present,
                  (SELECT COUNT(*) FROM student_attendance WHERE class_id = c.id AND attendance_date = '$today' AND status = 'absent') as today_absent,
                  (SELECT COUNT(*) FROM student_attendance WHERE class_id = c.id AND attendance_date = '$today' AND status = 'late') as today_late
                  FROM classes c
                  LEFT JOIN users u ON c.teacher_id = u.id
                  LEFT JOIN departments d ON c.department_id = d.id
                  ORDER BY c.section, c.year, c.semester, u.full_name";
$classes = $conn->query($classes_query);

// Get all students with today's attendance status
$students_query = "SELECT s.*, c.class_name, d.dept_name,
                   (SELECT status FROM student_attendance 
                    WHERE student_id = s.id AND attendance_date = '$today' LIMIT 1) as today_status
                   FROM students s
                   LEFT JOIN classes c ON s.class_id = c.id
                   LEFT JOIN departments d ON s.department_id = d.id
                   ORDER BY s.full_name";
$students = $conn->query($students_query);

// Get teachers with student count
$teachers_query = "SELECT u.id, u.full_name, u.email, u.phone, d.dept_name,
                   COUNT(DISTINCT c.id) as class_count,
                   COUNT(DISTINCT s.id) as student_count
                   FROM users u
                   LEFT JOIN departments d ON u.department_id = d.id
                   LEFT JOIN classes c ON u.id = c.teacher_id
                   LEFT JOIN students s ON c.id = s.class_id
                   WHERE u.role = 'teacher' AND u.is_active = 1
                   GROUP BY u.id
                   ORDER BY u.full_name";
$teachers = $conn->query($teachers_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complete Overview</title>
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
        }

        .navbar {
            background: rgba(26, 31, 58, 0.95);
            backdrop-filter: blur(20px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .navbar h1 {
            color: white;
            font-size: 26px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .hero-section h2 {
            font-size: 36px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 30px;
            font-weight: 800;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
        }

        .stat-card {
            padding: 25px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }

        .stat-card.default {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-card.present {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .stat-card.absent {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .stat-card.late {
            background: linear-gradient(135deg, #ffc107, #ff9800);
        }

        .stat-icon {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 40px;
            font-weight: 800;
            margin: 8px 0;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            opacity: 0.95;
            font-weight: 600;
        }

        .stat-subtext {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 5px;
        }

        .table-section {
            margin: 40px 0;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .table-header h3 {
            font-size: 28px;
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .search-box {
            background: white;
            padding: 12px 20px;
            border-radius: 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .search-box input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.5);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        thead th {
            padding: 18px 15px;
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
            background: rgba(102, 126, 234, 0.08);
            transform: translateX(5px);
        }

        tbody td {
            padding: 18px 15px;
            color: #2c3e50;
            font-size: 14px;
        }

        .badge {
            padding: 8px 16px;
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

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-late {
            background: #ffe0b2;
            color: #e65100;
        }

        .badge-pending {
            background: #b3e5fc;
            color: #01579b;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
            }

            .navbar h1 {
                font-size: 18px;
            }

            .hero-section {
                padding: 25px;
            }

            .hero-section h2 {
                font-size: 24px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .table-container {
                padding: 20px;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-header h3 {
                font-size: 20px;
                margin: 0;
            }

            .search-box {
                max-width: 100%;
            }

            table {
                font-size: 12px;
            }

            thead th,
            tbody td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <h1>🎓 Admin Dashboard - Complete System Overview</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($user['full_name']); ?></span>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </nav>

        <!-- Hero Section with Statistics -->
        <div class="hero-section">
            <h2>📊 System Statistics & Today's Attendance</h2>
            <div class="stats-grid">
                <!-- General Stats -->
                <div class="stat-card default">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-label">Total Students</div>
                    <span class="stat-value"><?php echo $stats['students']; ?></span>
                </div>

                <div class="stat-card default">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-label">Total Teachers</div>
                    <span class="stat-value"><?php echo $stats['teachers']; ?></span>
                </div>

                <div class="stat-card default">
                    <div class="stat-icon">📚</div>
                    <div class="stat-label">Active Classes</div>
                    <span class="stat-value"><?php echo $stats['classes']; ?></span>
                </div>

                <div class="stat-card default">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-label">Departments</div>
                    <span class="stat-value"><?php echo $stats['departments']; ?></span>
                </div>

                <!-- Today's Attendance -->
                <div class="stat-card present">
                    <div class="stat-icon">✅</div>
                    <div class="stat-label">Today Present</div>
                    <span class="stat-value"><?php echo $stats['today_present']; ?></span>
                    <div class="stat-subtext"><?php echo $stats['today_percentage']; ?>% Attendance</div>
                </div>

                <div class="stat-card absent">
                    <div class="stat-icon">❌</div>
                    <div class="stat-label">Today Absent</div>
                    <span class="stat-value"><?php echo $stats['today_absent']; ?></span>
                </div>

                <div class="stat-card late">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-label">Today Late</div>
                    <span class="stat-value"><?php echo $stats['today_late']; ?></span>
                </div>

                <div class="stat-card default">
                    <div class="stat-icon">📋</div>
                    <div class="stat-label">Total Marked</div>
                    <span class="stat-value"><?php echo $stats['today_total_marked']; ?></span>
                    <div class="stat-subtext">Today's Attendance</div>
                </div>
            </div>
        </div>

        <!-- All Classes Table -->
        <div class="table-section">
            <div class="table-header">
                <h3>📚 All Department Classes</h3>
                <div class="search-box">
                    <input type="text" id="classSearch" placeholder="🔍 Search classes...">
                </div>
            </div>
            <div class="table-container">
                <?php if ($classes->num_rows > 0): ?>
                <table id="classesTable">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Students</th>
                            <th>✅ Present</th>
                            <th>❌ Absent</th>
                            <th>⏰ Late</th>
                            <th>Today's Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($class = $classes->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($class['dept_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($class['year'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($class['section'] ?? 'N/A'); ?></span></td>
                            <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Not Assigned'); ?></td>
                            <td><span class="badge badge-success"><?php echo $class['student_count']; ?> Students</span></td>
                            <td><span class="badge badge-success">✅ <?php echo $class['today_present']; ?></span></td>
                            <td><span class="badge badge-danger">❌ <?php echo $class['today_absent']; ?></span></td>
                            <td><span class="badge badge-late">⏰ <?php echo $class['today_late']; ?></span></td>
                            <td>
                                <?php if ($class['today_marked'] > 0): ?>
                                    <span class="badge badge-success">✅ Marked</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Classes Found</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Students Table -->
        <div class="table-section">
            <div class="table-header">
                <h3>👨‍🎓 All Students - <?php echo $stats['students']; ?> Total</h3>
                <div class="search-box">
                    <input type="text" id="studentSearch" placeholder="🔍 Search students...">
                </div>
            </div>
            <div class="table-container">
                <?php if ($students->num_rows > 0): ?>
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Class</th>
                            <th>Today's Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($student = $students->fetch_assoc()): 
                            $status = $student['today_status'];
                            if ($status === 'present') {
                                $badge_class = 'badge-success';
                                $status_text = '✅ Present';
                            } elseif ($status === 'absent') {
                                $badge_class = 'badge-danger';
                                $status_text = '❌ Absent';
                            } elseif ($status === 'late') {
                                $badge_class = 'badge-late';
                                $status_text = '⏰ Late';
                            } else {
                                $badge_class = 'badge-secondary';
                                $status_text = '❓ Not Marked';
                            }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👨‍🎓</div>
                    <h3>No Students Found</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Teachers Table -->
        <div class="table-section">
            <div class="table-header">
                <h3>👨‍🏫 All Teachers - <?php echo $stats['teachers']; ?> Total</h3>
                <div class="search-box">
                    <input type="text" id="teacherSearch" placeholder="🔍 Search teachers...">
                </div>
            </div>
            <div class="table-container">
                <?php if ($teachers->num_rows > 0): ?>
                <table id="teachersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Classes Teaching</th>
                            <th>Students Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($teacher = $teachers->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($teacher['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($teacher['dept_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-info"><?php echo $teacher['class_count']; ?></span></td>
                            <td><span class="badge badge-success"><?php echo $teacher['student_count']; ?> Students</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👨‍🏫</div>
                    <h3>No Teachers Found</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Search functionality for Classes
        document.getElementById('classSearch').addEventListener('keyup', function() {
            const input = this.value.toUpperCase();
            const table = document.getElementById('classesTable');
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const text = rows[i].textContent || rows[i].innerText;
                rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? '' : 'none';
            }
        });

        // Search functionality for Students
        document.getElementById('studentSearch').addEventListener('keyup', function() {
            const input = this.value.toUpperCase();
            const table = document.getElementById('studentsTable');
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const text = rows[i].textContent || rows[i].innerText;
                rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? '' : 'none';
            }
        });

        // Search functionality for Teachers
        document.getElementById('teacherSearch').addEventListener('keyup', function() {
            const input = this.value.toUpperCase();
            const table = document.getElementById('teachersTable');
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const text = rows[i].textContent || rows[i].innerText;
                rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? '' : 'none';
            }
        });
    </script>
</body>
</html>