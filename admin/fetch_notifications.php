<?php
session_start();
include('../LogReg/database.php');
require_once 'notifications.php';

header('Content-Type: application/json');

$count = getUnreadNotificationCount($conn);
echo json_encode(['count' => $count]);
?>