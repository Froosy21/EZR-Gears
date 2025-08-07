<?php
session_start();
require_once('../LogReg/database.php');
require_once 'notifications.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$notifications = getNotifications($conn);

if (isset($_POST['mark_read'])) {
    $notification_id = (int)$_POST['notification_id'];
    markNotificationAsRead($conn, $notification_id);
    header('Location: admin_notifications.php');
    exit();
}

try {
    // Fetch all unread notifications grouped by type
    $query = "
        SELECT * FROM notifications 
        WHERE is_read = 0 
        ORDER BY created_at DESC
    ";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Group notifications by type
    $groupedNotifications = [
        'order' => [],
        'event' => [],
        '3d_order' => []
    ];

    foreach ($notifications as $notification) {
        $groupedNotifications[$notification['type']][] = $notification;
    }

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $notifications = [];
    $groupedNotifications = [
        'order' => [],
        'event' => [],
        '3d_order' => []
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - EZReborn</title>
    <link rel="stylesheet" type="text/css" href="home.css">
    <style>
        body {
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        #content {
            margin-left: 270px;
            padding: 20px;
        }

        /* New notification styles */
        .notifications-container {
            margin-left: 270px;
            padding: 20px;
            max-width: 800px;
        }

        .notification-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .notification-item.unread {
            background-color: #e8f4fd;
            border-left: 4px solid #3498db;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-time {
            color: #666;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .notification-actions {
            display: flex;
            gap: 10px;
        }

        .mark-read-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .mark-read-btn:hover {
            background-color: #2980b9;
        }

        .notification-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-count {
            background-color: #34495e;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .no-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="img/ezrlogo.png" alt="Admin Logo">
        </div>
        <nav class="menu">
            <a href="admin_notifications.php">Notifications</a>
            <hr>
            <a href="orders.php">Orders</a>
            <hr>
            <a href="users.php">User Management</a>
            <hr>
            <a href="admin_events.php">Events</a>
            <hr>
            <a href="reg_approval.php">Registered user events</a>
            <hr>
            <a href="attendreg.php">Event Attendance Checker</a>
            <hr>
            <a href="products.php">Products</a>
            <hr>
            <a href="admin_3d.php">3D Customs</a>
            <hr>
            <a href="inventory.php">Inventory</a>
            <hr>
            <a href="login.php">Logout</a>
        </nav>
    </div>

       <div class="notifications-container">
        <header class="notification-header">
            <h2>Notifications</h2>
            <span class="notification-count" id="unread-count">
                <?php echo count($notifications); ?> unread
            </span>
        </header>

        <?php foreach ($groupedNotifications as $type => $typeNotifications): ?>
            <?php if (!empty($typeNotifications)): ?>
                <section class="notification-section">
                    <h3>
                        <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                        <span class="notification-badge"><?php echo count($typeNotifications); ?></span>
                    </h3>
                    <?php foreach ($typeNotifications as $notification): ?>
                        <article class="notification-item unread" data-id="<?php echo $notification['id']; ?>">
                            <div class="notification-content">
                                <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <time class="notification-time">
                                    <?php echo date('F j, Y g:i a', strtotime($notification['created_at'])); ?>
                                </time>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-read-btn" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                    Mark as read
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (empty($notifications)): ?>
            <div class="no-notifications">
                <h3>No new notifications</h3>
                <p>You're all caught up!</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function markAsRead(notificationId) {
            fetch(`mark_as_read.php?id=${notificationId}`, { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notification = document.querySelector(`article[data-id="${notificationId}"]`);
                    if (notification) {
                        notification.classList.remove('unread');
                        const actionDiv = notification.querySelector('.notification-actions');
                        if (actionDiv) {
                            actionDiv.remove();
                        }
                        
                        // Update section and total counts
                        updateNotificationCount();
                    }
                }
            })
            .catch(error => console.error('Error marking as read:', error));
        }

        function updateNotificationCount() {
            const sections = document.querySelectorAll('.notification-section');
            let totalCount = 0;
            
            sections.forEach(section => {
                const items = section.querySelectorAll('.notification-item.unread');
                const badge = section.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = items.length;
                }
                totalCount += items.length;
            });
            
            const countElement = document.getElementById('unread-count');
            if (countElement) {
                countElement.textContent = `${totalCount} unread`;
            }

            // If no notifications left, show the "no notifications" message
            const notificationsContainer = document.querySelector('.notifications-container');
            if (totalCount === 0 && !document.querySelector('.no-notifications')) {
                const noNotificationsDiv = document.createElement('div');
                noNotificationsDiv.className = 'no-notifications';
                noNotificationsDiv.innerHTML = `
                    <h3>No new notifications</h3>
                    <p>You're all caught up!</p>
                `;
                notificationsContainer.appendChild(noNotificationsDiv);
            }
        }

    </script>
</body>
</html>