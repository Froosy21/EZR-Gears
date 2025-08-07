<?php
// update_attendance.php
session_start();
include('../LogReg/database.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['user_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Validate status
$allowed_statuses = ['pending', 'attended', 'not_attended'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Update the attendance status
$update_query = "UPDATE event_registrations 
                 SET attendance_status = '$status'
                 WHERE id = '$user_id'";

if (mysqli_query($conn, $update_query)) {
    echo json_encode(['success' => true, 'message' => 'Attendance status updated successfully']);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>