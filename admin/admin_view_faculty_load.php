<?php
require_once '../db.php';
checkRole(['admin']);

$user = getCurrentUser();

// Get current academic year and semester
$current_year = isset($_GET['year']) ? $_GET['year'] : "2025-2026";
$current_semester = isset($_GET['semester']) ? $_GET['semester'] : "I";
$selected_dept = isset($_GET['department']) ? $_GET['department'] : 'all';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $hod_id = $_POST['hod_id'];
    $teacher_ids = isset($_POST['teacher_ids']) ? json_decode($_POST['teacher_ids']) : [];
    $message = $_POST['message'];
    
    // Insert message into database
    $insert_query = "INSERT INTO hod_messages (hod_id, sent_by, message, teacher_ids, academic_year, semester, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $teacher_ids_json = json_encode($teacher_ids);
    $insert_stmt->bind_param("iissss", $hod_id, $user['id'], $message, $teacher_ids_json, $current_year, $current_semester);
    
    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Message sent to HOD successfully!";
    } else {
        $_SESSION['error_message'] = "Error sending message!";
    }
    
    header("Location: admin_view_faculty_load.php?year=$current_year&semester=$current_semester&department=$selected_dept");
    exit();
}

// Get all departments
$depts_query = "SELECT d.*, u.full_name as hod_name, u.id as hod_id 
                FROM departments d 
                LEFT JOIN users u ON d.hod_id = u.id 
                ORDER BY d.dept_name";
$departments = $conn->query($depts_query);

// Get faculty loads based on selected department
if ($selected_dept === 'all') {
    $loads_query = "SELECT fl.*, u.full_name, u.email, d.dept_name, d.dept_code, hod.id as hod_id, hod.full_name as hod_name
                    FROM faculty_load fl 
                    JOIN users u ON fl.teacher_id = u.id 
                    JOIN departments d ON fl.department_id = d.id
                    LEFT JOIN users hod ON d.hod_id = hod.id
                    WHERE fl.academic_year = ? AND fl.semester = ?
                    ORDER BY d.dept_name, u.full_name";
    $loads_stmt = $conn->prepare($loads_query);
    $loads_stmt->bind_param("ss", $current_year, $current_semester);
} else {
    $loads_query = "SELECT fl.*, u.full_name, u.email, d.dept_name, d.dept_code, hod.id as hod_id, hod.full_name as hod_name
                    FROM faculty_load fl 
                    JOIN users u ON fl.teacher_id = u.id 
                    JOIN departments d ON fl.department_id = d.id
                    LEFT JOIN users hod ON d.hod_id = hod.id
                    WHERE fl.academic_year = ? AND fl.semester = ? AND fl.department_id = ?
                    ORDER BY u.full_name";
    $loads_stmt = $conn->prepare($loads_query);
    $loads_stmt->bind_param("ssi", $current_year, $current_semester, $selected_dept);
}

$loads_stmt->execute();
$loads = $loads_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Faculty Load - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        
        .container { max-width: 1600px; margin: 0 auto; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #667eea; }
        .header h1 { font-size: 28px; color: #2c3e50; }
        .header .back-btn { padding: 12px 24px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; }
        
        .filter-section { background: #f8f9fa; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .filter-section label { font-weight: 600; color: #2c3e50; }
        .filter-section select { padding: 10px 15px; border: 2px solid #667eea; border-radius: 8px; font-size: 14px; min-width: 150px; }
        
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); }
        .summary-card h4 { font-size: 14px; margin-bottom: 10px; opacity: 0.9; }
        .summary-card .value { font-size: 36px; font-weight: 800; }
        
        .department-section { margin-bottom: 40px; border: 2px solid #667eea; border-radius: 15px; overflow: hidden; }
        .department-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .department-header h3 { font-size: 20px; }
        .department-header .hod-info { font-size: 14px; opacity: 0.9; }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        thead th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; color: #2c3e50; border-bottom: 2px solid #667eea; }
        tbody tr { border-bottom: 1px solid #f0f0f0; transition: all 0.3s; }
        tbody tr:hover { background: rgba(102, 126, 234, 0.05); }
        tbody td { padding: 15px; color: #2c3e50; font-size: 14px; }
        
        .load-badge { padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 12px; display: inline-block; }
        .load-light { background: #d4edda; color: #155724; }
        .load-medium { background: #fff3cd; color: #856404; }
        .load-heavy { background: #f8d7da; color: #721c24; }
        
        .action-buttons { padding: 20px; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 14px; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4); }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: white; margin: 5% auto; padding: 0; border-radius: 20px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 20px 20px 0 0; }
        .modal-header h2 { font-size: 22px; }
        .modal-body { padding: 30px; }
        .modal-footer { padding: 20px 30px; background: #f8f9fa; border-radius: 0 0 20px 20px; display: flex; justify-content: flex-end; gap: 10px; }
        .close { color: white; float: right; font-size: 32px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .close:hover { transform: rotate(90deg); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px; font-size: 14px; }
        .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 120px; }
        .form-group textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        .selected-teachers { background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 10px; }
        .selected-teachers h4 { font-size: 14px; color: #2c3e50; margin-bottom: 10px; }
        .teacher-chip { display: inline-block; background: #667eea; color: white; padding: 6px 12px; border-radius: 15px; font-size: 12px; margin: 4px; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .filter-section { flex-direction: column; align-items: stretch; }
            .modal-content { width: 95%; margin: 10% auto; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-chart-bar"></i> Faculty Load Management</h1>
                <p style="color: #666; margin-top: 5px;">View and manage faculty workload across all departments</p>
            </div>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <label><i class="fas fa-calendar"></i> Academic Year:</label>
            <select onchange="window.location.href='?year='+this.value+'&semester=<?php echo $current_semester; ?>&department=<?php echo $selected_dept; ?>'">
                <option value="2025-2026" <?php echo $current_year === '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                <option value="2024-2025" <?php echo $current_year === '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                <option value="2023-2024" <?php echo $current_year === '2023-2024' ? 'selected' : ''; ?>>2023-2024</option>
            </select>
            
            <label><i class="fas fa-book"></i> Semester:</label>
            <select onchange="window.location.href='?year=<?php echo $current_year; ?>&semester='+this.value+'&department=<?php echo $selected_dept; ?>'">
                <option value="I" <?php echo $current_semester === 'I' ? 'selected' : ''; ?>>I Semester</option>
                <option value="II" <?php echo $current_semester === 'II' ? 'selected' : ''; ?>>II Semester</option>
            </select>
            
            <label><i class="fas fa-building"></i> Department:</label>
            <select onchange="window.location.href='?year=<?php echo $current_year; ?>&semester=<?php echo $current_semester; ?>&department='+this.value">
                <option value="all" <?php echo $selected_dept === 'all' ? 'selected' : ''; ?>>All Departments</option>
                <?php 
                $departments->data_seek(0);
                while ($dept = $departments->fetch_assoc()): 
                ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo $selected_dept == $dept['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['dept_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Summary Cards -->
        <?php
        $loads->data_seek(0);
        $total_faculty = 0;
        $avg_load = 0;
        $light_load = 0;
        $heavy_load = 0;
        
        while ($load = $loads->fetch_assoc()) {
            $total_faculty++;
            $avg_load += $load['total'];
            if ($load['total'] < 10) $light_load++;
            if ($load['total'] > 15) $heavy_load++;
        }
        
        $avg_load = $total_faculty > 0 ? round($avg_load / $total_faculty, 1) : 0;
        $loads->data_seek(0);
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <h4><i class="fas fa-users"></i> Total Faculty</h4>
                <div class="value"><?php echo $total_faculty; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-chart-line"></i> Average Load</h4>
                <div class="value"><?php echo $avg_load; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-battery-quarter"></i> Light Load (<10)</h4>
                <div class="value"><?php echo $light_load; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-battery-full"></i> Heavy Load (>15)</h4>
                <div class="value"><?php echo $heavy_load; ?></div>
            </div>
        </div>

        <!-- Faculty Load by Department -->
        <?php
        $current_dept = null;
        $dept_teachers = [];
        $dept_hod_id = null;
        $dept_hod_name = null;
        $sr_no = 1;
        
        $loads->data_seek(0);
        while ($load = $loads->fetch_assoc()):
            if ($current_dept !== $load['dept_name']) {
                // Close previous department section if exists
                if ($current_dept !== null) {
                    echo '</tbody></table></div>';
                    echo '<div class="action-buttons">';
                    
                    // Store teacher data in a data attribute for safe passing
                    $teachers_json = htmlspecialchars(json_encode($dept_teachers), ENT_QUOTES, 'UTF-8');
                    $dept_name_safe = htmlspecialchars($current_dept, ENT_QUOTES, 'UTF-8');
                    $hod_name_safe = htmlspecialchars($dept_hod_name ?? 'Not Assigned', ENT_QUOTES, 'UTF-8');
                    
                    echo '<button class="btn btn-success" ';
                    echo 'data-hod-id="' . $dept_hod_id . '" ';
                    echo 'data-hod-name="' . $hod_name_safe . '" ';
                    echo 'data-dept-name="' . $dept_name_safe . '" ';
                    echo 'data-teachers=\'' . $teachers_json . '\' ';
                    echo 'onclick="openMessageModal(this)">';
                    echo '<i class="fas fa-envelope"></i> Send Message to HOD';
                    echo '</button>';
                    echo '</div></div>';
                    $dept_teachers = [];
                }
                
                $current_dept = $load['dept_name'];
                $dept_hod_id = $load['hod_id'];
                $dept_hod_name = $load['hod_name'];
                ?>
                
                <div class="department-section">
                    <div class="department-header">
                        <div>
                            <h3><i class="fas fa-building"></i> <?php echo htmlspecialchars($load['dept_name']); ?> (<?php echo htmlspecialchars($load['dept_code']); ?>)</h3>
                            <div class="hod-info">
                                <i class="fas fa-user-tie"></i> HOD: <?php echo htmlspecialchars($load['hod_name'] ?? 'Not Assigned'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Name of the Faculty</th>
                                    <th>Email</th>
                                    <th>Theory</th>
                                    <th>Practical</th>
                                    <th>Other Load</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
            <?php
            }
            
            $total = $load['total'];
            $status_class = $total < 10 ? 'load-light' : ($total > 15 ? 'load-heavy' : 'load-medium');
            $status_text = $total < 10 ? 'Light Load' : ($total > 15 ? 'Heavy Load' : 'Medium Load');
            
            // Collect light load teachers for message
            if ($total < 10) {
                $dept_teachers[] = [
                    'id' => $load['teacher_id'],
                    'name' => $load['full_name'],
                    'load' => $total
                ];
            }
            ?>
            
            <tr>
                <td><?php echo $sr_no++; ?></td>
                <td><strong><?php echo htmlspecialchars($load['full_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($load['email']); ?></td>
                <td><?php echo $load['theory']; ?></td>
                <td><?php echo $load['practical']; ?></td>
                <td><?php echo $load['other_load']; ?></td>
                <td><strong><?php echo $total; ?></strong></td>
                <td><span class="load-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
            </tr>
        
        <?php endwhile; ?>
        
        <?php if ($current_dept !== null): ?>
            </tbody></table></div>
            <div class="action-buttons">
                <?php
                $teachers_json = htmlspecialchars(json_encode($dept_teachers), ENT_QUOTES, 'UTF-8');
                $dept_name_safe = htmlspecialchars($current_dept, ENT_QUOTES, 'UTF-8');
                $hod_name_safe = htmlspecialchars($dept_hod_name ?? 'Not Assigned', ENT_QUOTES, 'UTF-8');
                ?>
                <button class="btn btn-success" 
                    data-hod-id="<?php echo $dept_hod_id; ?>" 
                    data-hod-name="<?php echo $hod_name_safe; ?>" 
                    data-dept-name="<?php echo $dept_name_safe; ?>" 
                    data-teachers='<?php echo $teachers_json; ?>' 
                    onclick="openMessageModal(this)">
                    <i class="fas fa-envelope"></i> Send Message to HOD
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($total_faculty === 0): ?>
            <div style="text-align: center; padding: 60px; color: #999; background: #f8f9fa; border-radius: 15px;">
                <i class="fas fa-inbox" style="font-size: 64px; margin-bottom: 20px; display: block;"></i>
                <h3>No Faculty Load Data Available</h3>
                <p style="margin-top: 10px;">Please select a different academic year, semester, or department.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeMessageModal()">&times;</span>
                <h2><i class="fas fa-envelope"></i> Send Message to HOD</h2>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="hod_id" id="hod_id">
                    <input type="hidden" name="teacher_ids" id="teacher_ids">
                    
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Department:</label>
                        <input type="text" id="dept_name" readonly style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user-tie"></i> HOD Name:</label>
                        <input type="text" id="hod_name" readonly style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; background: #f8f9fa;">
                    </div>
                    
                    <div id="teachers_info"></div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-comment-alt"></i> Message: *</label>
                        <textarea name="message" required placeholder="Enter your message to the HOD about free faculty members..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeMessageModal()" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openMessageModal(buttonElement) {
            // Get data from button's data attributes
            const hodId = buttonElement.getAttribute('data-hod-id');
            const hodName = buttonElement.getAttribute('data-hod-name');
            const deptName = buttonElement.getAttribute('data-dept-name');
            const teachersJson = buttonElement.getAttribute('data-teachers');
            
            // Parse teachers data
            let teachers = [];
            try {
                teachers = JSON.parse(teachersJson);
            } catch (e) {
                console.error('Error parsing teachers data:', e);
                teachers = [];
            }
            
            // Set form values
            document.getElementById('hod_id').value = hodId || '';
            document.getElementById('hod_name').value = hodName || 'Not Assigned';
            document.getElementById('dept_name').value = deptName || '';
            
            // Set teacher IDs for form submission
            const teacherIds = teachers.map(t => t.id);
            document.getElementById('teacher_ids').value = JSON.stringify(teacherIds);
            
            // Build teachers display HTML
            let teachersHtml = '<div class="selected-teachers"><h4><i class="fas fa-users"></i> Faculty Members with Light Load (Available for Additional Classes):</h4>';
            
            if (teachers.length > 0) {
                teachers.forEach(teacher => {
                    teachersHtml += `<span class="teacher-chip">${teacher.name} (Load: ${teacher.load})</span>`;
                });
            } else {
                teachersHtml += '<p style="color: #666; font-style: italic;">No faculty members with light load in this department.</p>';
            }
            
            teachersHtml += '</div>';
            document.getElementById('teachers_info').innerHTML = teachersHtml;
            
            // Show modal
            document.getElementById('messageModal').style.display = 'block';
        }
        
        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMessageModal();
            }
        });
    </script>
</body>
</html>