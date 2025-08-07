<?php
include('../LogReg/database.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO notifications (type, message, created_at, is_read) 
        VALUES ('order', 'Test notification: New order received at ' || NOW(), NOW(), 0)
    ");
    
    $success = $stmt->execute();
    
    echo json_encode([
        'success' => $success,
        'message' => 'Test notification created'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}