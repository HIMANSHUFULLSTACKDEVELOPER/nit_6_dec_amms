<?php
require_once '../db.php';
checkRole(['hod']);

$user = getCurrentUser();
$department_id = $_SESSION['department_id'];

// Get department info
$dept_query = "SELECT * FROM departments WHERE id = $department_id";
$department = $conn->query($dept_query)->fetch_assoc();

// Get department teachers
$teachers_query = "SELECT u.*, 
                   (SELECT COUNT(*) FROM classes WHERE teacher_id = u.id) as class_count
                   FROM users u
                   WHERE u.role = 'teacher' AND u.department_id = $department_id AND u.is_active = 1
                   ORDER BY u.full_name";
$teachers = $conn->query($teachers_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Teachers - HOD</title>
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

        /* Enhanced Navbar with Glass Effect */
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

        /* Modern Tables */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            margin: 30px 0;
            border: 2px solid rgba(255, 255, 255, 0.5);
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

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .alert {
            padding: 20px 24px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
        }

        .alert-info {
            background: #e3f2fd;
            color: #1976d2;
            border: 2px solid #90caf9;
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

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.6);
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

        /* Search Box */
        .search-box {
            position: relative;
            min-width: 300px;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .search-input::placeholder {
            color: #999;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar { 
                padding: 15px 20px; 
                flex-direction: column; 
                gap: 15px; 
            }
            
            .navbar h1 { 
                font-size: 18px; 
            }
            
            .main-content { 
                padding: 20px; 
            }
            
            .table-container { 
                padding: 20px; 
                overflow-x: auto; 
            }
            
            table { 
                font-size: 12px; 
            }
            
            .user-info { 
                flex-direction: column; 
                gap: 10px; 
            }

            .search-box {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Particles -->
    <div class="particles" id="particles"></div>

    <nav class="navbar">
        <div class="navbar-brand">
            <div class="navbar-logo">🎓</div>
            <h1>NIT AMMS <?php echo htmlspecialchars($department['dept_name']); ?> - Teachers</h1>
        </div>
        <div class="user-info">
            <a href="index.php" class="btn btn-secondary">← Back</a>
            <div class="user-profile">
                <div class="user-avatar">👔</div>
                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
            </div>
            <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0;">👨‍🏫 Department Teachers</h3>
                <div class="search-box">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="🔍 Search by name, username, email..." 
                           class="search-input">
                </div>
            </div>
            
            <?php if ($teachers->num_rows > 0): ?>
                <table id="teachersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Assigned Classes</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($teacher = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $teacher['id']; ?></td>
                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                            <td><span class="badge badge-info"><?php echo $teacher['class_count']; ?> Classes</span></td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No teachers found in this department.</div>
            <?php endif; ?>
        </div>
    </div>

    

    <style>
        @keyframes borderMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
    </style>

    <script>
        // Create animated background particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 5 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                
                particlesContainer.appendChild(particle);
            }
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();

            const searchInput = document.getElementById('searchInput');
            const teachersTable = document.getElementById('teachersTable');
            
            if (!searchInput || !teachersTable) return;

            const tbody = teachersTable.querySelector('tbody');
            if (!tbody) return;

            const tableRows = tbody.querySelectorAll('tr');

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

                tableRows.forEach(row => {
                    const cells = row.getElementsByTagName('td');
                    if (cells.length === 0) return;

                    const id = cells[0].textContent.toLowerCase();
                    const name = cells[1].textContent.toLowerCase();
                    const username = cells[2].textContent.toLowerCase();
                    const email = cells[3].textContent.toLowerCase();
                    const phone = cells[4].textContent.toLowerCase();

                    if (id.includes(searchTerm) || 
                        name.includes(searchTerm) || 
                        username.includes(searchTerm) ||
                        email.includes(searchTerm) ||
                        phone.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                let noResultsMsg = document.getElementById('noResultsMsg');
                if (visibleCount === 0 && searchTerm !== '') {
                    if (!noResultsMsg) {
                        noResultsMsg = document.createElement('div');
                        noResultsMsg.id = 'noResultsMsg';
                        noResultsMsg.className = 'no-results';
                        teachersTable.parentElement.appendChild(noResultsMsg);
                    }
                    noResultsMsg.innerHTML = '🔍 No teachers found matching "<strong>' + searchTerm + '</strong>"';
                    noResultsMsg.style.display = 'block';
                    teachersTable.style.display = 'none';
                } else {
                    if (noResultsMsg) {
                        noResultsMsg.style.display = 'none';
                    }
                    teachersTable.style.display = 'table';
                }
            });
        });
    </script>
</body>
</html>