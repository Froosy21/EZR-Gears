<?php
header('Content-Type: application/json');
include('../LogReg/database.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Notification ID is required');
    }

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $success = $stmt->execute([$_GET['id']]);

    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}