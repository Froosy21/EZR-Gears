<?php
header('Content-Type: application/json');
include_once '../LogReg/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the latest products and events
$query = "SELECT 
    'product' as type,
    p.id,
    p.name as message,
    p.created_at as timestamp,
    COALESCE(n.is_read, 0) as is_read
    FROM product p
    LEFT JOIN notifications n ON n.reference_id = p.id 
        AND n.type = 'product' 
        AND n.user_id = ?
    WHERE p.created_at >= NOW() - INTERVAL 24 HOUR
    
    UNION
    
    SELECT 
    'event' as type,
    e.id,
    e.event_title as message,
    e.created_at as timestamp,
    COALESCE(n.is_read, 0) as is_read
    FROM esports_events e
    LEFT JOIN notifications n ON n.reference_id = e.id 
        AND n.type = 'event' 
        AND n.user_id = ?
    WHERE e.created_at >= NOW() - INTERVAL 24 HOUR
    
    ORDER BY timestamp DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = array();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = array(
            'id' => $row['id'],
            'type' => $row['type'],
            'message' => $row['message'],
            'timestamp' => $row['timestamp'],
            'is_read' => (bool)$row['is_read']
        );
    }
    
    echo json_encode(['newItems' => $notifications]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

$conn->close();
?>