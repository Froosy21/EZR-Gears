<?php
include('../LogReg/database.php'); // Ensure this file provides $host, $db, $user, and $pass

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $message = "New order received from user ID " . $_POST['user_id'];
        $stmt = $pdo->prepare("INSERT INTO notifications (message, type, is_read, created_at) VALUES (:message, 'order', 0, NOW())");
        $stmt->bindParam(':message', $message);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
