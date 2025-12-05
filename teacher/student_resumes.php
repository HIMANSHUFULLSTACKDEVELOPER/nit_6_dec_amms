<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db.php';
checkRole(['teacher']);

// Get selected filters
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$class_filter = isset($_GET['class']) ? $_GET['class'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT s.id, s.full_name, s.roll_number, s.email, s.phone,
          d.dept_name, c.class_name, c.section,
          r.objective, r.skills, r.theme, r.updated_at
          FROM students s
          LEFT JOIN departments d ON s.department_id = d.id
          LEFT JOIN classes c ON s.class_id = c.id
          INNER JOIN student_resumes r ON s.id = r.student_id
          WHERE 1=1";

$params = [];
$types = '';

if ($department_filter) {
    $query .= " AND s.department_id = ?";
    $params[] = $department_filter;
    $types .= 'i';
}

if ($class_filter) {
    $query .= " AND s.class_id = ?";
    $params[] = $class_filter;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (s.full_name LIKE ? OR s.roll_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY s.full_name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get departments for filter
$dept_query = "SELECT id, dept_name FROM departments ORDER BY dept_name";
$departments = $conn->query($dept_query);

// Get classes for filter
$class_query = "SELECT id, class_name, section FROM classes ORDER BY class_name, section";
$classes = $conn->query($class_query);

// Get total resume count
$total_query = "SELECT COUNT(*) as total FROM student_resumes";
$total_result = $conn->query($total_query);
$total_resumes = $total_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Resumes - NIT College</title>
    <link rel="icon" href="../Nit_logo.png" type="image/png" />
   <style>
   
       

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-content h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header-content p {
            color: #666;
            font-size: 16px;
        }

        .filters {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .filters h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #666;
            font-size: 14px;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .student-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .student-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .student-name {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .student-roll {
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
        }

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
        }

        .info-item .icon {
            width: 18px;
            text-align: center;
        }

        .theme-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .theme-professional {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
        }

        .theme-modern {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .theme-creative {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .theme-minimal {
            background: #2c3e50;
            color: white;
        }

        .objective-preview {
            font-size: 13px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .skills-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }

        .skill-tag {
            padding: 4px 10px;
            background: #f0f0f0;
            border-radius: 15px;
            font-size: 12px;
            color: #555;
        }

        .last-updated {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }

        .no-results {
            background: white;
            padding: 60px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .no-results .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #666;
            font-size: 16px;
        }

  /* ========================================
   MOBILE LAYOUT FIXES
   ======================================== */

/* Fix for stat cards showing partial numbers */
@media (max-width: 768px) {
    .stat-card {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .stat-card .number {
        white-space: nowrap;
        width: 100%;
        text-align: center;
    }

    .stat-card .label {
        white-space: nowrap;
        width: 100%;
        text-align: center;
    }
}

/* Fix for filter dropdowns being cut off */
@media (max-width: 768px) {
    .filters {
        overflow: visible;
    }

    .filter-row {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .filter-group {
        width: 100%;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        box-sizing: border-box;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 20px;
        padding-right: 40px;
    }

    /* Remove default dropdown arrow on IE */
    .filter-group select::-ms-expand {
        display: none;
    }
}

/* Fix for buttons stacking properly */
@media (max-width: 768px) {
    .btn {
        width: 100%;
        display: block;
        text-align: center;
        margin: 5px 0;
    }

    .filter-group .btn {
        margin-top: 0;
    }
}

/* Ensure header doesn't overflow */
@media (max-width: 768px) {
    .header {
        overflow: hidden;
    }

    .header-content {
        width: 100%;
        margin-bottom: 10px;
    }

    .header-content h1 {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
}

/* Fix for student cards */
@media (max-width: 768px) {
    .student-card {
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .student-name {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
}

/* Additional spacing fixes */
@media (max-width: 480px) {
    .filter-row {
        gap: 12px;
    }

    .filter-group {
        margin-bottom: 0;
    }

    .btn {
        padding: 12px 20px;
        font-size: 15px;
    }
}

/* Fix for very small screens */
@media (max-width: 360px) {
    .stat-card .number {
        font-size: 28px;
    }

    .filters h3 {
        font-size: 16px;
    }

    .btn {
        padding: 10px 15px;
        font-size: 14px;
    }
}

/* Ensure proper scrolling */
@media (max-width: 768px) {
    body {
        overflow-x: hidden;
    }

    .container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    * {
        max-width: 100%;
    }
}

/* Fix for form elements */
@media (max-width: 768px) {
    form {
        width: 100%;
    }

    input, select, button {
        box-sizing: border-box;
        max-width: 100%;
    }
}/* ========================================
   IMPROVED MOBILE STYLES - COMPLETE FIX
   ======================================== */

/* Base mobile reset */
@media (max-width: 768px) {
    * {
        box-sizing: border-box;
    }

    body {
        padding: 10px;
        overflow-x: hidden;
        width: 100%;
    }

    .container {
        padding: 0;
        width: 100%;
        max-width: 100%;
        overflow: visible;
    }

    /* Header Section */
    .header {
        padding: 20px;
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
        margin-bottom: 15px;
    }

    .header-content {
        width: 100%;
    }

    .header-content h1 {
        font-size: 24px;
        line-height: 1.3;
        margin-bottom: 8px;
    }

    .header-content p {
        font-size: 14px;
        line-height: 1.5;
    }

    /* Stats Section - FIX FOR CUT OFF NUMBERS */
    .stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .stat-card {
        padding: 20px 15px;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .stat-card .number {
        font-size: 32px;
        margin-bottom: 8px;
        white-space: nowrap;
        line-height: 1;
    }

    .stat-card .label {
        font-size: 13px;
        white-space: nowrap;
        text-align: center;
    }

    /* Filters Section - FIX FOR OVERFLOW */
    .filters {
        padding: 20px;
        margin-bottom: 15px;
        overflow: visible;
        width: 100%;
    }

    .filters h3 {
        font-size: 18px;
        margin-bottom: 15px;
    }

    .filter-row {
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .filter-group {
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-size: 14px;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 12px 15px;
        font-size: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Custom dropdown arrow */
    .filter-group select {
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 18px;
        padding-right: 40px;
    }

    .filter-group select::-ms-expand {
        display: none;
    }

    /* Buttons */
    .btn {
        width: 100%;
        padding: 12px 20px;
        font-size: 15px;
        border-radius: 8px;
        margin: 0;
        display: block;
        text-align: center;
    }

    .btn-primary {
        margin-bottom: 10px;
    }

    /* Students Grid */
    .students-grid {
        grid-template-columns: 1fr;
        gap: 15px;
        width: 100%;
    }

    .student-card {
        padding: 20px;
        width: 100%;
        border-radius: 12px;
    }

    .student-header {
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .student-name {
        font-size: 18px;
        line-height: 1.3;
        word-break: break-word;
    }

    .student-roll {
        font-size: 14px;
        margin-top: 5px;
    }

    .student-info {
        gap: 10px;
        margin-bottom: 15px;
    }

    .info-item {
        font-size: 14px;
        line-height: 1.5;
    }

    .info-item .icon {
        width: 20px;
        flex-shrink: 0;
    }

    /* Theme & Skills */
    .theme-badge {
        font-size: 12px;
        padding: 6px 12px;
        margin-bottom: 12px;
        display: inline-block;
    }

    .skills-preview {
        gap: 6px;
        margin-bottom: 15px;
    }

    .skill-tag {
        font-size: 12px;
        padding: 5px 10px;
    }

    .objective-preview {
        font-size: 13px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .last-updated {
        font-size: 12px;
        margin-bottom: 15px;
        color: #999;
    }

    /* No Results */
    .no-results {
        padding: 50px 20px;
        text-align: center;
    }

    .no-results .icon {
        font-size: 56px;
        margin-bottom: 20px;
    }

    .no-results h3 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .no-results p {
        font-size: 14px;
        line-height: 1.5;
    }
}

/* Extra small mobile */
@media (max-width: 480px) {
    body {
        padding: 8px;
    }

    .header {
        padding: 15px;
    }

    .header-content h1 {
        font-size: 20px;
    }

    .stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .stat-card {
        padding: 18px 15px;
    }

    .stat-card .number {
        font-size: 28px;
    }

    .filters {
        padding: 15px;
    }

    .filter-group input,
    .filter-group select {
        padding: 10px 12px;
        font-size: 14px;
    }

    .btn {
        padding: 11px 16px;
        font-size: 14px;
    }

    .student-card {
        padding: 15px;
    }

    .student-name {
        font-size: 17px;
    }
}

/* Very small screens */
@media (max-width: 360px) {
    body {
        padding: 5px;
    }

    .header {
        padding: 12px;
        margin-bottom: 12px;
    }

    .header-content h1 {
        font-size: 18px;
    }

    .header-content p {
        font-size: 13px;
    }

    .stat-card {
        padding: 15px 10px;
        min-height: 90px;
    }

    .stat-card .number {
        font-size: 26px;
    }

    .stat-card .label {
        font-size: 12px;
    }

    .filters {
        padding: 12px;
    }

    .filters h3 {
        font-size: 16px;
        margin-bottom: 12px;
    }

    .filter-group label {
        font-size: 13px;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 10px;
        font-size: 13px;
    }

    .btn {
        padding: 10px 12px;
        font-size: 13px;
    }

    .student-card {
        padding: 12px;
    }

    .student-name {
        font-size: 16px;
    }
}

/* Landscape orientation for mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .filter-group:nth-last-child(-n+2) {
        grid-column: span 1;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>📄 Student Resumes</h1>
                <p>View and manage all student resumes</p>
            </div>
            <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="number"><?php echo $total_resumes; ?></div>
                <div class="label">Total Resumes</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $students->num_rows; ?></div>
                <div class="label">Filtered Results</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <h3>🔍 Filter Resumes</h3>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Department</label>
                        <select name="department">
                            <option value="">All Departments</option>
                            <?php 
                            if ($departments && $departments->num_rows > 0):
                                while($dept = $departments->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $department_filter == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Class</label>
                        <select name="class">
                            <option value="">All Classes</option>
                            <?php 
                            if ($classes && $classes->num_rows > 0):
                                while($class = $classes->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $class_filter == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Name or Roll Number" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                    </div>

                    <div class="filter-group">
                        <a href="student_resumes.php" class="btn btn-secondary" style="width: 100%;">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Students Grid -->
        <?php if ($students->num_rows > 0): ?>
            <div class="students-grid">
                <?php while($student = $students->fetch_assoc()): ?>
                    <div class="student-card">
                        <div class="student-header">
                            <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                            <div class="student-roll">Roll: <?php echo htmlspecialchars($student['roll_number']); ?></div>
                        </div>

                        <div class="student-info">
                            
                            <div class="info-item">
                                <span class="icon">📚</span>
                                <span><?php echo htmlspecialchars($student['dept_name']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="icon">📱</span>
                                <span><?php echo htmlspecialchars($student['phone']); ?></span>
                            </div>
                        </div>

                        <span class="theme-badge theme-<?php echo htmlspecialchars($student['theme']); ?>">
                            <?php echo ucfirst(htmlspecialchars($student['theme'])); ?> Theme
                        </span>

                        <?php if (!empty($student['objective'])): ?>
                            <div class="objective-preview">
                                <strong>Objective:</strong> <?php echo htmlspecialchars($student['objective']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($student['skills'])): ?>
                            <div class="skills-preview">
                                <?php
                                $skills = array_slice(array_filter(array_map('trim', explode(',', $student['skills']))), 0, 5);
                                foreach ($skills as $skill):
                                ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                                <?php if (count(explode(',', $student['skills'])) > 5): ?>
                                    <span class="skill-tag">+more</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="last-updated">
                            Last updated: <?php echo date('M d, Y', strtotime($student['updated_at'])); ?>
                        </div>

                        <a href="view_student_resume.php?id=<?php echo $student['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                            👁️ View Full Resume
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="icon">📭</div>
                <h3>No Resumes Found</h3>
                <p>No students have created resumes yet, or no results match your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>