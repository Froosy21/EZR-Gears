<?php
function createNotification($conn, $type, $message, $order_id = null, $status = 'unread') {
    $stmt = $conn->prepare("
        INSERT INTO notifications (type, message, order_id, status, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssis", $type, $message, $order_id, $status);
    return $stmt->execute();
}

function getUnreadNotificationCount($conn) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE status = 'unread'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

function markNotificationAsRead($conn, $notification_id) {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

function getNotifications($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>