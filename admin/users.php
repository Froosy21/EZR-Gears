<?php
session_start();
include('../LogReg/database.php');

$sql = "SELECT id, fname, lname, phonenum, email FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="home.css">
<style>
    body {
        display: flex;
        margin: 0;
        font-family: Arial, sans-serif;
    }
    .content {
        margin-left: 270px; /* Adjust to match sidebar width + gap */
        padding: 20px;
    }
    table {
        width: 90%; /* Reduced width */
        margin-left: auto; /* Center table */
        margin-right: auto; /* Center table */
        border-collapse: collapse;
    }
    table, th, td {
        border: 2px solid black;
    }
    th, td {
        padding: 10px; /* Reduced padding */
        text-align: center;
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
    
    <table class="content">
        <tr class= "content">
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $name = $row["fname"] . " " . $row["lname"];
                echo "<tr>
                        <td>" . htmlspecialchars($name) . "</td>
                        <td>" . htmlspecialchars($row["email"]) . "</td>
                        <td>" . htmlspecialchars($row["phonenum"]) . "</td>
                        <td>
                            <a href='updateuser.php?id=" . $row['id'] . "'>Update</a>
                            <form action='deleteuser.php' method='POST' style='display:inline;'>
                                <input type='hidden' name='id' value='" . $row['id'] . "'>
                                <input type='submit' value='Delete' onclick='return confirm(\"Are you sure you want to delete this user?\");'>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No users found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>