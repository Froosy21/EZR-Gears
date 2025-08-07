<?php
session_start();
include('../LogReg/database.php');

// Get the last login time from the request
$data = json_decode(file_get_contents('php://input'), true);
$lastLoginTime = $data['lastLoginTime'];

// Query for new products
$productQuery = "SELECT COUNT(*) as count FROM product WHERE created_at > ?";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param("s", $lastLoginTime);
$stmt->execute();
$productResult = $stmt->get_result();
$newProducts = $productResult->fetch_assoc()['count'];

// Query for new events
$eventQuery = "SELECT COUNT(*) as count FROM esports_events WHERE created_at > ?";
$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("s", $lastLoginTime);
$stmt->execute();
$eventResult = $stmt->get_result();
$newEvents = $eventResult->fetch_assoc()['count'];

// Return the results
header('Content-Type: application/json');
echo json_encode([
    'newProducts' => (int)$newProducts,
    'newEvents' => (int)$newEvents
]);