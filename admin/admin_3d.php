<?php
session_start();
include('../LogReg/database.php');

// Add a new endpoint for handling status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    // First check if a status exists
    $check_sql = "SELECT order_id FROM 3dorder_status WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing status
        $update_sql = "UPDATE 3dorder_status SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $order_id);
    } else {
        // Insert new status if none exists
        $insert_sql = "INSERT INTO 3dorder_status (order_id, status) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("is", $order_id, $status);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    
    $stmt->close();
    exit;
}

// Fetch all 3D product records from the database
$sql = "SELECT p.*, o.status FROM 3d_prod p 
        LEFT JOIN 3dorder_status o ON p.id = o.order_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Product Management</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }

        .main-content {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #ecf0f1;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 1000px;
            width: 100%;
            overflow-x: auto;
        }

        .form-container h1 {
            color: #d18d8d;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #640f0f;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #640f0f;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        img {
            width: 50px;
            height: auto;
            cursor: pointer;
        }

        a.download-link {
            color: #640f0f;
            text-decoration: none;
            font-weight: bold;
        }

        a.download-link:hover {
            text-decoration: underline;
        }
        /*sadasdasdasdas*/
        
        
        
        body {

   .logo img {
    width: 100%; /* Adjust logo width to fit sidebar */
    height: 100px; /* Maintain aspect ratio */
    max-width: 100px; /* Optionally limit the logo width */
    margin-bottom: 0px; /* Space below the logo */
    }


.product-img {
    width: 50px; /* Specific size for product images */
    height: auto;
    cursor: pointer;
}

.product-img-container a {
    display: block;
    margin-top: 10px;
}
.status-dropdown {
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #640f0f;
        background-color: white;
        cursor: pointer;
        width: 100%;
        max-width: 150px;
    }

    .status-dropdown option {
        padding: 8px;
    }

    .status-processing {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-delivering {
        background-color: #cce5ff;
        color: #004085;
    }

    .status-delivered {
        background-color: #d4edda;
        color: #155724;
    }
    .status-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .confirm-btn {
            padding: 6px 12px;
            background-color: #640f0f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: none;
        }
        
        .confirm-btn:hover {
            background-color: #4a0b0b;
        }
        
        .status-message {
            display: none;
            margin-top: 5px;
            font-size: 0.9em;
        }
        
        .success-message {
            color: #155724;
        }
        
        .error-message {
            color: #721c24;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .status-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .confirm-btn {
            padding: 6px 12px;
            background-color: #640f0f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: none;
        }
        
        .confirm-btn:hover {
            background-color: #4a0b0b;
        }

    </style>
</head>
<body>
 <div id="notification" class="notification"></div>

<div class="sidebar">
        <div class="logo">
            <img src="img/ezrlogo.png" alt="Admin Logo">
        </div>
        <nav class="menu">
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

<div class="main-content">
        <div class="form-container">
            <h1>3D Product Management</h1>
            
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Address</th>
                            <th>Created At</th>
                            <th>Zip File</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($row['zipFilePath']); ?>" class="download-link" download>Download</a>
                                </td>
                                <td>
                                    <div class="status-controls">
                                        <select 
                                            class="status-dropdown" 
                                            data-order-id="<?php echo htmlspecialchars($row['id']); ?>"
                                            data-current-status="<?php echo htmlspecialchars($row['status'] ?? 'pending'); ?>">
                                            <option value="pending" <?php echo ($row['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>
                                            <option value="processing" <?php echo ($row['status'] ?? 'processing') === 'processing' ? 'selected' : ''; ?>>
                                                Processing
                                            </option>
                                            <option value="delivering" <?php echo ($row['status'] ?? '') === 'delivering' ? 'selected' : ''; ?>>
                                                Delivering
                                            </option>
                                            <option value="delivered" <?php echo ($row['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>
                                                Delivered
                                            </option>
                                        </select>
                                        <button class="confirm-btn">Confirm</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No 3D products found in the database.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('notification');
            const statusDropdowns = document.querySelectorAll('.status-dropdown');
            
            function showNotification(message, isSuccess = true) {
                notification.textContent = message;
                notification.style.backgroundColor = isSuccess ? '#d4edda' : '#f8d7da';
                notification.style.color = isSuccess ? '#155724' : '#721c24';
                notification.style.borderColor = isSuccess ? '#c3e6cb' : '#f5c6cb';
                notification.style.display = 'block';
                
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
            
            statusDropdowns.forEach(dropdown => {
                const currentStatus = dropdown.getAttribute('data-current-status');
                const container = dropdown.closest('.status-controls');
                const confirmBtn = container.querySelector('.confirm-btn');
                
                // Show/hide confirm button on dropdown change
                dropdown.addEventListener('change', function() {
                    if (this.value !== currentStatus) {
                        confirmBtn.style.display = 'block';
                    } else {
                        confirmBtn.style.display = 'none';
                    }
                });
                
                // Handle confirm button click
                confirmBtn.addEventListener('click', async function() {
                    const orderId = dropdown.getAttribute('data-order-id');
                    const newStatus = dropdown.value;
                    
                    try {
                        // First update status in database
                        const dbUpdateResponse = await fetch('admin_3d.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=updateStatus&order_id=${orderId}&status=${newStatus}`
                        });
                        
                        const dbResult = await dbUpdateResponse.json();
                        
                        if (dbResult.success) {
                            // If database update successful, send email notification
                            const emailResponse = await fetch('update_3d_status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `order_id=${orderId}&status=${newStatus}`
                            });
                            
                            const emailResult = await emailResponse.json();
                            
                            if (emailResult.success) {
                                showNotification('Status updated and notification sent successfully!');
                            } else {
                                showNotification('Status updated but email notification failed: ' + emailResult.message, false);
                            }
                            
                            dropdown.setAttribute('data-current-status', newStatus);
                            confirmBtn.style.display = 'none';
                        } else {
                            showNotification('Error updating status: ' + (dbResult.error || 'Unknown error'), false);
                        }
                        
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification('Error updating status. Please try again.', false);
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>