<?php
session_start();
include('../LogReg/database.php');

// Get the user's email from session
$userEmail = $_SESSION['email'];

// Get user's last login time
$lastLoginQuery = $conn->prepare("SELECT last_login FROM users WHERE email = ?");
$lastLoginQuery->bind_param("s", $userEmail);
$lastLoginQuery->execute();
$result = $lastLoginQuery->get_result();
$userData = $result->fetch_assoc();
$lastLoginTime = $userData['last_login'];

// If this is their first login, use current time
if (!$lastLoginTime) {
    $lastLoginTime = date('Y-m-d H:i:s');
}

// Check for new products
$productQuery = $conn->prepare("SELECT COUNT(*) as count FROM product WHERE created_at > ?");
$productQuery->bind_param("s", $lastLoginTime);
$productQuery->execute();
$productResult = $productQuery->get_result();
$newProducts = $productResult->fetch_assoc()['count'];

// Check for new events
$eventQuery = $conn->prepare("SELECT COUNT(*) as count FROM esports_events WHERE created_at > ?");
$eventQuery->bind_param("s", $lastLoginTime);
$eventQuery->execute();
$eventResult = $eventQuery->get_result();
$newEvents = $eventResult->fetch_assoc()['count'];

// Update last login time to current time
$updateQuery = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE email = ?");
$updateQuery->bind_param("s", $userEmail);
$updateQuery->execute();

// Send response
header('Content-Type: application/json');
echo json_encode([
    'newProducts' => (int)$newProducts,
    'newEvents' => (int)$newEvents
]);
?>