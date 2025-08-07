<?php
session_start();
require_once('../LogReg/database.php');

function handleAdminLogin($username, $password) {
    global $conn;
    
    try {
        // Get admin credentials
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, last_login FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && $password === $admin['password']) { // Note: In production, use password_verify()
            // Store last login time before updating it
            $lastLogin = $admin['last_login'];
            
            // Update last login time
            $updateStmt = mysqli_prepare($conn, "UPDATE admin SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "i", $admin['id']);
            mysqli_stmt_execute($updateStmt);
            
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_login'] = $lastLogin;
            
            // Fetch new notifications since last login
            fetchNewNotifications($conn, $lastLogin);
            
            mysqli_stmt_close($stmt);
            mysqli_stmt_close($updateStmt);
            return true;
        }
        mysqli_stmt_close($stmt);
        return false;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function fetchNewNotifications($conn, $lastLogin) {
    try {
        // Only proceed if we have a last login time
        if ($lastLogin) {
            // Fetch new orders
            $orderStmt = mysqli_prepare($conn, "
                SELECT 'order' as type, 
                       CONCAT('New order from ', email, ' for ', product_name) as message,
                       order_date as created_at
                FROM orders 
                WHERE order_date > ?
            ");
            mysqli_stmt_bind_param($orderStmt, "s", $lastLogin);
            mysqli_stmt_execute($orderStmt);
            $newOrders = mysqli_stmt_get_result($orderStmt);
            $newOrders = mysqli_fetch_all($newOrders, MYSQLI_ASSOC);

            // Fetch new event registrations
            $eventStmt = mysqli_prepare($conn, "
                SELECT 'event' as type,
                       CONCAT(first_name, ' ', last_name, ' registered for an event') as message,
                       created_at
                FROM event_registrations 
                WHERE created_at > ?
            ");
            mysqli_stmt_bind_param($eventStmt, "s", $lastLogin);
            mysqli_stmt_execute($eventStmt);
            $newRegistrations = mysqli_stmt_get_result($eventStmt);
            $newRegistrations = mysqli_fetch_all($newRegistrations, MYSQLI_ASSOC);

            // Fetch new 3D orders
            $threeDStmt = mysqli_prepare($conn, "
                SELECT '3d_order' as type,
                       CONCAT('New 3D print order from ', email) as message,
                       created_at
                FROM 3d_prod 
                WHERE created_at > ?
            ");
            mysqli_stmt_bind_param($threeDStmt, "s", $lastLogin);
            mysqli_stmt_execute($threeDStmt);
            $new3DOrders = mysqli_stmt_get_result($threeDStmt);
            $new3DOrders = mysqli_fetch_all($new3DOrders, MYSQLI_ASSOC);

            // Combine all notifications
            $allNotifications = array_merge($newOrders, $newRegistrations, $new3DOrders);

            // Insert notifications into the notifications table
            $insertStmt = mysqli_prepare($conn, "
                INSERT INTO notifications (type, message, created_at) 
                VALUES (?, ?, ?)
            ");

            foreach ($allNotifications as $notification) {
                mysqli_stmt_bind_param($insertStmt, "sss", 
                    $notification['type'],
                    $notification['message'],
                    $notification['created_at']
                );
                mysqli_stmt_execute($insertStmt);
            }

            // Close all statements
            mysqli_stmt_close($orderStmt);
            mysqli_stmt_close($eventStmt);
            mysqli_stmt_close($threeDStmt);
            mysqli_stmt_close($insertStmt);
        }
    } catch (Exception $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
    }
}
?>