<?php
require_once '../db.php';
checkRole(['hod']);

$user = getCurrentUser();
$department_id = $_SESSION['department_id'];

// Get current academic year
$current_year = isset($_GET['year']) ? $_GET['year'] : "2025-2026";
$current_semester = isset($_GET['semester']) ? $_GET['semester'] : "I";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_load') {
        $teacher_id = $_POST['teacher_id'];
        $theory = $_POST['theory'];
        $practical = $_POST['practical'];
        $other_load = $_POST['other_load'];
        $total = $theory + $practical + $other_load;
        
        // Check if load already exists
        $check_query = "SELECT id FROM faculty_load WHERE teacher_id = ? AND academic_year = ? AND semester = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("iss", $teacher_id, $current_year, $current_semester);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing load
            $update_query = "UPDATE faculty_load SET theory = ?, practical = ?, other_load = ?, total = ?, updated_at = NOW() WHERE teacher_id = ? AND academic_year = ? AND semester = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("iiiiiss", $theory, $practical, $other_load, $total, $teacher_id, $current_year, $current_semester);
            
            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Faculty load updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating faculty load!";
            }
        } else {
            // Insert new load
            $insert_query = "INSERT INTO faculty_load (teacher_id, department_id, academic_year, semester, theory, practical, other_load, total, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iissiiiii", $teacher_id, $department_id, $current_year, $current_semester, $theory, $practical, $other_load, $total, $user['id']);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Faculty load added successfully!";
            } else {
                $_SESSION['error_message'] = "Error adding faculty load!";
            }
        }
        
        header("Location: hod_manage_faculty_load.php?year=$current_year&semester=$current_semester");
        exit();
    }
    
    if ($_POST['action'] === 'delete_load') {
        $load_id = $_POST['load_id'];
        $delete_query = "DELETE FROM faculty_load WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $load_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Faculty load deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting faculty load!";
        }
        
        header("Location: hod_manage_faculty_load.php?year=$current_year&semester=$current_semester");
        exit();
    }
}

// Get department teachers
$teachers_query = "SELECT id, full_name, email FROM users WHERE role = 'teacher' AND department_id = ? AND is_active = 1 ORDER BY full_name";
$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->bind_param("i", $department_id);
$teachers_stmt->execute();
$teachers = $teachers_stmt->get_result();

// Get faculty loads for current semester
$loads_query = "SELECT fl.*, u.full_name, u.email 
                FROM faculty_load fl 
                JOIN users u ON fl.teacher_id = u.id 
                WHERE fl.department_id = ? AND fl.academic_year = ? AND fl.semester = ?
                ORDER BY u.full_name";
$loads_stmt = $conn->prepare($loads_query);
$loads_stmt->bind_param("iss", $department_id, $current_year, $current_semester);
$loads_stmt->execute();
$loads = $loads_stmt->get_result();

// Get department info
$dept_query = "SELECT dept_name FROM departments WHERE id = ?";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $department_id);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();
$department = $dept_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty Load - HOD Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #667eea; }
        .header h1 { font-size: 28px; color: #2c3e50; }
        .header .back-btn { padding: 12px 24px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; }
        
        .filter-section { background: #f8f9fa; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .filter-section label { font-weight: 600; color: #2c3e50; }
        .filter-section select { padding: 10px 15px; border: 2px solid #667eea; border-radius: 8px; font-size: 14px; min-width: 150px; }
        
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        .add-load-form { background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #667eea; }
        .add-load-form h3 { color: #2c3e50; margin-bottom: 20px; font-size: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; color: #2c3e50; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select { padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        .btn { padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 14px; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-danger { background: linear-gradient(135deg, #ff6b6b, #ee5a5a); color: white; }
        .btn-sm { padding: 8px 16px; font-size: 12px; }
        
        .table-container { overflow-x: auto; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #667eea, #764ba2); }
        thead th { padding: 15px; color: white; text-align: left; font-weight: 600; font-size: 14px; }
        tbody tr { border-bottom: 1px solid #f0f0f0; transition: all 0.3s; }
        tbody tr:hover { background: rgba(102, 126, 234, 0.05); }
        tbody td { padding: 15px; color: #2c3e50; font-size: 14px; }
        
        .load-badge { padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 12px; display: inline-block; }
        .load-light { background: #d4edda; color: #155724; }
        .load-medium { background: #fff3cd; color: #856404; }
        .load-heavy { background: #f8d7da; color: #721c24; }
        
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); }
        .summary-card h4 { font-size: 14px; margin-bottom: 10px; opacity: 0.9; }
        .summary-card .value { font-size: 36px; font-weight: 800; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .form-grid { grid-template-columns: 1fr; }
            .filter-section { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-users-cog"></i> Manage Faculty Load</h1>
                <p style="color: #666; margin-top: 5px;"><?php echo htmlspecialchars($department['dept_name']); ?> Department</p>
            </div>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="hod_view_messages.php" class="back-btn"><i class="fas fa-message"></i> Message</a>
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
            <select onchange="window.location.href='?year='+this.value+'&semester=<?php echo $current_semester; ?>'">
                <option value="2025-2026" <?php echo $current_year === '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                <option value="2024-2025" <?php echo $current_year === '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                <option value="2023-2024" <?php echo $current_year === '2023-2024' ? 'selected' : ''; ?>>2023-2024</option>
            </select>
            
            <label><i class="fas fa-book"></i> Semester:</label>
            <select onchange="window.location.href='?year=<?php echo $current_year; ?>&semester='+this.value">
                <option value="I" <?php echo $current_semester === 'I' ? 'selected' : ''; ?>>I Semester</option>
                <option value="II" <?php echo $current_semester === 'II' ? 'selected' : ''; ?>>II Semester</option>
            </select>
        </div>

        <!-- Summary Cards -->
        <?php
        $loads->data_seek(0);
        $total_teachers = 0;
        $avg_load = 0;
        $light_load = 0;
        $heavy_load = 0;
        
        while ($load = $loads->fetch_assoc()) {
            $total_teachers++;
            $avg_load += $load['total'];
            if ($load['total'] < 11) $light_load++;
            if ($load['total'] > 18) $heavy_load++;
        }
        
        $avg_load = $total_teachers > 0 ? round($avg_load / $total_teachers, 1) : 0;
        $loads->data_seek(0);
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <h4><i class="fas fa-users"></i> Total Faculty</h4>
                <div class="value"><?php echo $total_teachers; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-chart-line"></i> Average Load</h4>
                <div class="value"><?php echo $avg_load; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-battery-quarter"></i> Light Load</h4>
                <div class="value"><?php echo $light_load; ?></div>
            </div>
            <div class="summary-card">
                <h4><i class="fas fa-battery-full"></i> Heavy Load</h4>
                <div class="value"><?php echo $heavy_load; ?></div>
            </div>
        </div>

        <!-- Add Load Form -->
        <div class="add-load-form">
            <h3><i class="fas fa-plus-circle"></i> Add/Update Faculty Load</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_load">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Select Faculty *</label>
                        <select name="teacher_id" required>
                            <option value="">-- Select Faculty --</option>
                            <?php 
                            $teachers->data_seek(0);
                            while ($teacher = $teachers->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-book"></i> Theory Hours *</label>
                        <input type="number" name="theory" min="0" max="50" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-flask"></i> Practical Hours *</label>
                        <input type="number" name="practical" min="0" max="50" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tasks"></i> Other Load *</label>
                        <input type="number" name="other_load" min="0" max="50" value="0" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Faculty Load
                </button>
            </form>
        </div>

        <!-- Faculty Load Table -->
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($loads->num_rows > 0):
                        $sr_no = 1;
                        while ($load = $loads->fetch_assoc()): 
                            $total = $load['total'];
                            $status_class = $total < 10 ? 'load-light' : ($total > 15 ? 'load-heavy' : 'load-medium');
                            $status_text = $total < 10 ? 'Light' : ($total > 15 ? 'Heavy' : 'Medium');
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
                        <td>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this load?');">
                                <input type="hidden" name="action" value="delete_load">
                                <input type="hidden" name="load_id" value="<?php echo $load['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            No faculty load data available for this semester
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>