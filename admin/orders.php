<?php
session_start();
include('../LogReg/database.php');
require '../vendor/autoload.php';
use GuzzleHttp\Client;

// Function to sync PayMongo payments
function syncPaymongoPayments($conn) {
    $secretKey = 'sk_test_Rq5WQbJcwvAnu4ewEgurmSfz';
    $client = new Client(['base_uri' => 'https://api.paymongo.com/']);

    try {
        // First, fetch all payment links
        $linksResponse = $client->request('GET', 'v1/links', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Accept' => 'application/json',
            ]
        ]);
        
        $links = json_decode($linksResponse->getBody(), true)['data'];
        
        // Create a mapping of link IDs to their payment status
        $linkStatuses = [];
        foreach ($links as $link) {
            $linkId = $link['id'];
            // Check if the link has been paid
            if (isset($link['attributes']['status']) && $link['attributes']['status'] === 'paid') {
                $linkStatuses[$linkId] = 'paid';
            }
        }

        // Update orders based on link statuses
        foreach ($linkStatuses as $linkId => $status) {
            $stmt = $conn->prepare("
                UPDATE orders 
                SET payment_status = ?
                WHERE payment_id = ?
            ");
            
            $stmt->bind_param("ss", $status, $linkId);
            $stmt->execute();
        }

        return true;
    } catch (Exception $e) {
        error_log("Payment sync error: " . $e->getMessage());
        return false;
    }
}

// Handle AJAX request for payment sync
if (isset($_POST['sync_payments'])) {
    $success = syncPaymongoPayments($conn);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to sync payments']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - EZReborn</title>
    <link rel="stylesheet" type="text/css" href="home.css">
    <style>
        /*sadsddddddddddddddddddddddddddddd*/
        
        
        
        
        body {
            background-color: #f9f9f9; /* Light background */
            color: #333; /* Text color */
            margin: 0;
            font-family: Arial, sans-serif;
        }

        #content {
            margin-left: 270px; /* Space for the sidebar */
            padding: 20px; /* Padding for the content */
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 400px;
            margin-left: 238px; /* Shift table to the right */
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #34495e; /* Darker header background */
            color: white; /* White text for headers */
        }

        .remove-btn {
            background-color: #c21212; /* Red remove button */
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background-color: #a00; /* Darker red on hover */
        }
    </style>
</head>
<body>
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
    <nav class="content">
        <h1>Orders</h1>
        <div id="orders-container">
            <?php include 'renderOrders.php'; ?>
        </div>
    </nav>

    <!-- Include jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function syncPayments() {
            $.ajax({
                url: 'orders.php',
                type: 'POST',
                data: { sync_payments: true },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Refresh the orders table
                            $('#orders-container').load('renderOrders.php');
                        } else {
                            console.error('Failed to sync payments:', result.error || 'Unknown error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                },
                error: function() {
                    console.error('Failed to communicate with the server');
                }
            });
        }

        // Sync payments every 5 minutes
        setInterval(syncPayments, 3000);

        // Sync when the page loads
        $(document).ready(function() {
            syncPayments();
        });
    </script>
</body>
</html>