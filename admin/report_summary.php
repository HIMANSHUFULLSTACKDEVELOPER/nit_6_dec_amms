<?php
require_once '../db.php';
checkRole(['admin']);

$user = getCurrentUser();

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter_department = isset($_GET['department']) ? intval($_GET['department']) : '';
$filter_class = isset($_GET['class']) ? intval($_GET['class']) : '';

$where_clauses = ["sa.attendance_date = '$filter_date'"];
if ($filter_department) $where_clauses[] = "d.id = $filter_department";
if ($filter_class) $where_clauses[] = "c.id = $filter_class";
$where_sql = implode(' AND ', $where_clauses);

$attendance_query = "SELECT sa.*, s.roll_number, s.full_name as student_name,
                     c.class_name, d.dept_name, u.full_name as teacher_name
                     FROM student_attendance sa
                     JOIN students s ON sa.student_id = s.id
                     JOIN classes c ON sa.class_id = c.id
                     JOIN departments d ON c.department_id = d.id
                     JOIN users u ON sa.marked_by = u.id
                     WHERE $where_sql ORDER BY c.class_name, s.roll_number";
$attendance_records = $conn->query($attendance_query);

$section_stats_query = "SELECT c.id as class_id, c.class_name, d.dept_name,
                COUNT(DISTINCT sa.student_id) as total_students,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late
                FROM student_attendance sa
                JOIN classes c ON sa.class_id = c.id
                JOIN departments d ON c.department_id = d.id
                WHERE sa.attendance_date = '$filter_date'
                " . ($filter_department ? "AND d.id = $filter_department" : "") . "
                " . ($filter_class ? "AND c.id = $filter_class" : "") . "
                GROUP BY c.id, c.class_name, d.dept_name ORDER BY d.dept_name, c.class_name";
$section_stats = $conn->query($section_stats_query);

$stats_query = "SELECT COUNT(DISTINCT sa.student_id) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                FROM student_attendance sa
                JOIN classes c ON sa.class_id = c.id
                JOIN departments d ON c.department_id = d.id WHERE $where_sql";
$stats = $conn->query($stats_query)->fetch_assoc();

$unique_students = $stats['total'];
$attendance_percentage = $unique_students > 0 ? round(($stats['present'] / $unique_students) * 100, 1) : 0;

$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name");
$classes = $conn->query("SELECT c.*, d.dept_name FROM classes c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.class_name");
$is_today = ($filter_date === date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
<style>
    /* ===================================
   NIT AMMS - Enhanced CSS Stylesheet
   Complete styling with smooth animations
   =================================== */

/* ============= RESET & BASE ============= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    /* Primary Colors */
    --primary-color: #2563eb;
    --primary-dark: #1e40af;
    --primary-light: #3b82f6;
    
    /* Status Colors */
    --success-color: #10b981;
    --success-light: #34d399;
    --danger-color: #ef4444;
    --danger-light: #f87171;
    --warning-color: #f59e0b;
    --warning-light: #fbbf24;
    --info-color: #3b82f6;
    
    /* Background Colors */
    --dark-bg: #1f2937;
    --light-bg: #f9fafb;
    --card-bg: #ffffff;
    --border-color: #e5e7eb;
    --hover-bg: #f3f4f6;
    
    /* Text Colors */
    --text-primary: #111827;
    --text-secondary: #6b7280;
    --text-light: #9ca3af;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    
    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-base: 0.3s ease;
    --transition-slow: 0.5s ease;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: var(--text-primary);
    line-height: 1.6;
}

/* ============= NAVBAR / HEADER ============= */
.navbar {
    background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
    color: white;
    padding: 1.5rem 2rem;
    box-shadow: var(--shadow-xl);
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    backdrop-filter: blur(10px);
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.navbar h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition-base);
}

.navbar h1:hover {
    transform: scale(1.02);
    text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
}

.user-info {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.user-info span {
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    font-weight: 500;
    transition: var(--transition-base);
}

.user-info span:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

/* ============= BUTTON STYLES ============= */
.btn, .btn-filter, .view-btn {
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-base);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}

.btn::before, .btn-filter::before, .view-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before, .btn-filter:hover::before, .view-btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn-primary, .btn-filter {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover, .btn-filter:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-primary:active, .btn-filter:active {
    transform: translateY(0);
    box-shadow: var(--shadow-sm);
}

.btn-secondary {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
}

.btn-success {
    background: var(--success-color);
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
}

.view-btn {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    margin-top: 0.5rem;
    width: 100%;
    justify-content: center;
}

.view-btn:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-md);
}

/* ============= CONTAINER & LAYOUT ============= */
.main-content {
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1.5rem;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============= LIVE INDICATOR ============= */
.today-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--danger-color);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
}

.today-badge::before {
    content: '🔴';
    animation: blink 1s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.9;
        transform: scale(1.05);
    }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* ============= FILTER SECTION ============= */
.filter-container {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: var(--shadow-lg);
    margin-bottom: 2rem;
    transition: var(--transition-base);
}

.filter-container:hover {
    box-shadow: var(--shadow-xl);
    transform: translateY(-3px);
}

.filter-container h3 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group input,
.form-group select {
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all var(--transition-base);
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    transform: translateY(-2px);
}

.form-group input:hover,
.form-group select:hover {
    border-color: var(--primary-light);
}

/* ============= STATS GRID ============= */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.75rem;
    border-radius: 1rem;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
    border-left: 4px solid var(--primary-color);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, transparent, rgba(37, 99, 235, 0.05));
    border-radius: 0 0 0 100%;
    transition: all var(--transition-base);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-2xl);
}

.stat-card:hover::before {
    width: 150px;
    height: 150px;
}

.stat-card h3 {
    font-size: 0.95rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-weight: 500;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    transition: var(--transition-base);
}

.stat-card:hover .stat-value {
    transform: scale(1.1);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-card.present {
    border-left-color: var(--success-color);
}

.stat-card.present .stat-value {
    color: var(--success-color);
}

.stat-card.present::before {
    background: linear-gradient(135deg, transparent, rgba(16, 185, 129, 0.05));
}

.stat-card.absent {
    border-left-color: var(--danger-color);
}

.stat-card.absent .stat-value {
    color: var(--danger-color);
}

.stat-card.absent::before {
    background: linear-gradient(135deg, transparent, rgba(239, 68, 68, 0.05));
}

.stat-card.late {
    border-left-color: var(--warning-color);
}

.stat-card.late .stat-value {
    color: var(--warning-color);
}

.stat-card.late::before {
    background: linear-gradient(135deg, transparent, rgba(245, 158, 11, 0.05));
}

/* ============= SECTION CARDS ============= */
.section-container {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: var(--shadow-lg);
    margin-bottom: 2rem;
    transition: var(--transition-base);
}

.section-container:hover {
    box-shadow: var(--shadow-xl);
}

.section-header-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.section-card {
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.section-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(37, 99, 235, 0.03), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
}

.section-card:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow-lg);
    transform: translateY(-5px);
}

.section-card:hover::after {
    transform: rotate(45deg) translate(50%, 50%);
}

.section-card.high {
    border-color: var(--success-color);
}

.section-card.medium {
    border-color: var(--warning-color);
}

.section-card.low {
    border-color: var(--danger-color);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
}

.class-info {
    flex: 1;
}

.class-name {
    font-size: 1.25rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition-base);
}

.section-card:hover .class-name {
    color: var(--primary-color);
    transform: translateX(5px);
}

.class-icon {
    font-size: 1.5rem;
}

.dept-name {
    font-size: 0.9rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.percentage-circle {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.section-card:hover .percentage-circle {
    transform: rotate(360deg) scale(1.1);
    box-shadow: var(--shadow-lg);
}

.percentage-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.percentage-label {
    font-size: 0.7rem;
    opacity: 0.9;
}

.card-body {
    position: relative;
    z-index: 1;
}

.stats-row {
    display: flex;
    justify-content: space-around;
    margin-bottom: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    border: 2px solid var(--border-color);
    flex: 1;
    margin: 0 0.25rem;
    transition: var(--transition-base);
}

.stat-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-item.late {
    border-color: var(--warning-color);
}

.stat-num {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-text {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.progress-container {
    margin-bottom: 1rem;
}

.progress-labels {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.progress-labels span:first-child {
    font-weight: 600;
    color: var(--text-primary);
}

.progress-labels span:last-child {
    color: var(--text-secondary);
}

.progress-bar {
    height: 0.75rem;
    background: var(--border-color);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success-color), var(--success-light));
    border-radius: 1rem;
    transition: width var(--transition-slow);
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* ============= TABLE STYLES ============= */
.table-container {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    transition: var(--transition-base);
}

.table-container:hover {
    box-shadow: var(--shadow-xl);
}

.table-header-with-search {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.table-header-with-search h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

#searchInput {
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 1rem;
    width: 300px;
    transition: all var(--transition-base);
}

#searchInput:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    width: 350px;
}

#searchInput:hover {
    border-color: var(--primary-light);
}

.table-wrapper {
    overflow-x: auto;
}

#attendanceTable {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

#attendanceTable thead {
    background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
    color: white;
}

#attendanceTable th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

#attendanceTable tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all var(--transition-fast);
}

#attendanceTable tbody tr:hover {
    background: var(--light-bg);
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

#attendanceTable td {
    padding: 1rem;
    color: var(--text-primary);
}

/* ============= BADGE STYLES ============= */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
    transition: all var(--transition-base);
}

.badge:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-md);
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-success:hover {
    background: #a7f3d0;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-danger:hover {
    background: #fecaca;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-warning:hover {
    background: #fde68a;
}

/* ============= NO DATA STATE ============= */
.no-data {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-secondary);
}

.no-data h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.no-data p {
    font-size: 1rem;
}

/* ============= RESPONSIVE DESIGN ============= */
@media (max-width: 1024px) {
    .section-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .navbar h1 {
        font-size: 1.5rem;
    }

    .user-info {
        flex-direction: column;
        width: 100%;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .filter-form {
        grid-template-columns: 1fr;
    }

    .section-grid {
        grid-template-columns: 1fr;
    }

    .table-header-with-search {
        flex-direction: column;
        align-items: stretch;
    }

    #searchInput {
        width: 100%;
    }

    #searchInput:focus {
        width: 100%;
    }

    #attendanceTable {
        min-width: 800px;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 0 1rem;
    }

    .filter-container,
    .section-container,
    .table-container {
        padding: 1.5rem;
    }

    .stat-value {
        font-size: 2rem;
    }

    .section-stats {
        grid-template-columns: 1fr;
    }

    .card-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .percentage-circle {
        margin-top: 1rem;
    }
}

/* ============= PRINT STYLES ============= */
@media print {
    body {
        background: white;
    }

    .navbar .user-info,
    .filter-container,
    .btn,
    .view-btn,
    #searchInput {
        display: none !important;
    }

    .section-card,
    .stat-card,
    .table-container {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid var(--border-color);
    }

    .section-card:hover,
    .stat-card:hover {
        transform: none;
    }
}

/* ============= SMOOTH SCROLLING ============= */
html {
    scroll-behavior: smooth;
}

/* ============= CUSTOM SCROLLBAR ============= */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: var(--light-bg);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}

/* ============= ACCESSIBILITY ============= */
.btn:focus,
.form-group input:focus,
.form-group select:focus,
#searchInput:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* ============= LOADING ANIMATION ============= */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card,
.section-card {
    animation: fadeInUp 0.5s ease forwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>
    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('attendanceTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let txtValue = '';
                const td = tr[i].getElementsByTagName('td');
                
                // Search through roll no, student name, class, department, teacher columns
                for (let j = 0; j <= 5; j++) {
                    if (td[j]) {
                        txtValue += td[j].textContent || td[j].innerText;
                        txtValue += ' ';
                    }
                }
                
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <nav class="navbar">
        <div><h1>🎓 NIT AMMS - Attendance Reports</h1></div>
        <div class="user-info">
            <a href="index.php" class="btn btn-secondary">← Back</a>
            <span>👨‍💼 <?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="filter-container">
            <h3>🔍 Filter Attendance Records</h3>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>📅 Date</label>
                    <input type="date" name="date" value="<?php echo $filter_date; ?>" required>
                </div>
                <div class="form-group">
                    <label>🏢 Department</label>
                    <select name="department">
                        <option value="">All Departments</option>
                        <?php $departments->data_seek(0); while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>📚 Class</label>
                    <select name="class">
                        <option value="">All Classes</option>
                        <?php $classes->data_seek(0); while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $filter_class == $class['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-filter">🔎 Apply Filter</button>
                </div>
            </form>
        </div>

        <?php if ($is_today): ?>
        <div style="text-align: center;"><span class="today-badge">LIVE - Today's Attendance</span></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>📊 Total Students</h3>
                <div class="stat-value"><?php echo $unique_students; ?></div>
                <div class="stat-label"><?php echo date('d M Y', strtotime($filter_date)); ?></div>
            </div>
            <div class="stat-card present">
                <h3>✅ Present</h3>
                <div class="stat-value"><?php echo $stats['present']; ?></div>
                <div class="stat-label"><?php echo $attendance_percentage; ?>% Attendance</div>
            </div>
            <div class="stat-card absent">
                <h3>❌ Absent</h3>
                <div class="stat-value"><?php echo $stats['absent']; ?></div>
                <div class="stat-label"><?php echo $unique_students > 0 ? round(($stats['absent'] / $unique_students) * 100, 1) : 0; ?>% of total</div>
            </div>
            <div class="stat-card late">
                <h3>⏰ Late Arrival</h3>
                <div class="stat-value"><?php echo $stats['late']; ?></div>
                <div class="stat-label"><?php echo $unique_students > 0 ? round(($stats['late'] / $unique_students) * 100, 1) : 0; ?>% of total</div>
            </div>
        </div>

        <div class="section-container">
            <h2 class="section-header-title">📚 Section-wise Attendance</h2>
            <?php if ($section_stats->num_rows > 0): ?>
            <div class="section-grid">
                <?php while ($section = $section_stats->fetch_assoc()): 
                    $pct = $section['total_students'] > 0 ? round(($section['present'] / $section['total_students']) * 100, 1) : 0;
                    $lvl = $pct >= 75 ? 'high' : ($pct >= 50 ? 'medium' : 'low');
                ?>
                <div class="section-card <?php echo $lvl; ?>">
                    <div class="card-header">
                        <div class="class-info">
                            <h4 class="class-name">
                                <span class="class-icon">📖</span>
                                <?php echo htmlspecialchars($section['class_name']); ?>
                            </h4>
                            <p class="dept-name">🏛️ <?php echo htmlspecialchars($section['dept_name']); ?></p>
                        </div>
                        <div class="percentage-circle">
                            <span class="percentage-value"><?php echo $pct; ?>%</span>
                            <span class="percentage-label">Present</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="stats-row">
                            <div class="stat-item late">
                                <span class="stat-num"><?php echo $section['late']; ?></span>
                                <span class="stat-text">Late</span>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-labels">
                                <span>Attendance Progress</span>
                                <span><?php echo $pct; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                        </div>
                        <a href="?date=<?php echo $filter_date; ?>&class=<?php echo $section['class_id']; ?>" class="view-btn">
                            View Details →
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="no-data"><h3>📭 No Section Data</h3><p>No attendance records found for sections on this date.</p></div>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <div class="table-header-with-search">
                <h3>📝 Detailed Attendance Records</h3>
                <input type="text" id="searchInput" onkeyup="searchTable()" 
                       placeholder="🔍 Search by roll no, name, class, department...">
            </div>
            <?php if ($attendance_records->num_rows > 0): ?>
            <table id="attendanceTable">
                <thead>
                    <tr>
                        <th>Roll No.</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Marked By</th>
                        <th>Time</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $attendance_records->data_seek(0); while ($r = $attendance_records->fetch_assoc()): 
                        $sc = $r['status'] === 'present' ? 'badge-success' : ($r['status'] === 'absent' ? 'badge-danger' : 'badge-warning');
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['roll_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['class_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['dept_name']); ?></td>
                        <td><span class="badge <?php echo $sc; ?>"><?php echo strtoupper($r['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['teacher_name']); ?></td>
                        <td><strong><?php echo date('h:i A', strtotime($r['marked_at'])); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data"><h3>📭 No Records Found</h3><p>No attendance records found for the selected filters.</p></div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>