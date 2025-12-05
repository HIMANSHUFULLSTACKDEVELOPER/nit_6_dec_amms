<?php
// get_leave_details.php - Place this in the hod folder
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors for JSON response

require_once '../db.php';
checkRole(['hod']);

header('Content-Type: application/json');

// Check if department_id exists in session
if (!isset($_SESSION['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID not found in session']);
    exit;
}

$department_id = $_SESSION['department_id'];

// Get and validate leave_id
$leave_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Debug: Log the received ID
error_log("Received leave_id: " . $leave_id);

if ($leave_id <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid leave application ID',
        'debug' => [
            'received_id' => $_GET['id'] ?? 'not set',
            'parsed_id' => $leave_id
        ]
    ]);
    exit;
}

// Fetch leave application details with all related information
$query = "SELECT 
    la.*,
    s.full_name as student_name,
    s.roll_number,
    s.email as student_email,
    s.phone as student_phone,
    c.class_name,
    c.section,
    c.year,
    c.semester,
    t.full_name as teacher_name,
    t.email as teacher_email,
    DATEDIFF(la.end_date, la.start_date) + 1 as total_days
    FROM leave_applications la
    JOIN students s ON la.student_id = s.id
    JOIN classes c ON la.class_id = c.id
    LEFT JOIN users t ON la.teacher_id = t.id
    WHERE la.id = ? AND s.department_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $leave_id, $department_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Leave application not found or access denied']);
    exit;
}

$application = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'application' => $application
]);
?>