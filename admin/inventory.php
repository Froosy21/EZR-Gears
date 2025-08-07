<?php
session_start();
include('../LogReg/database.php');

// SQL Query to fetch inventory details
$sql = "
SELECT 
    p.id, p.name, p.price,
    COALESCE(i.in_stock, p.quantity) AS in_stock, 
    COALESCE(i.out_stock, 0) AS out_stock, 
    CASE 
        WHEN COALESCE(i.in_stock, p.quantity) > 0 THEN 'In Stock'
        ELSE 'Sold Out'
    END AS status 
FROM product p
LEFT JOIN inventory i ON p.id = i.product_id
";

$result = $conn->query($sql);

$products = [];
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $message = "No products found.";
    }
} else {
    $message = "Error fetching products: " . $conn->error;
}

// Only process cart updates if valid session exists
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $update_sql = "UPDATE inventory 
                      SET in_stock = in_stock - ?,
                          out_stock = out_stock + ?
                      WHERE product_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iii", $quantity, $quantity, $product_id);
        $stmt->execute();
        $stmt->close();
    }
    unset($_SESSION['cart']); // Clear cart after processing
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="home.css">
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
    <div class="content">
        <h1>Inventory</h1>

        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>In</th>
                    <th>Out </th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['in_stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['out_stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: auto;
    padding: auto;
}



.content {
    margin-left: 10%; /* Shift content to the right of the sidebar */
    padding: 20px;
    display: flex;
    justify-content: left; /* Aligns content to the right */
    flex-direction: column; /* Stack content vertically */
    align-items: center; /* Align items to the right */
}

h1 {
    text-align: left;
    width: 10%;
}

table {
    width: 60%; /* Reduce table size */
    margin: 10px 0; /* Add space around the table */
    border-collapse: collapse; /* Collapse table borders */
    border: 1px solid #ccc; /* Add border to the table */
    background-color: #fff;
}

th, td {
    border: 1px solid #ccc; /* Add borders between cells */
    padding: 8px; /* Add padding to cells */
    text-align: center; /* Center align text */
}

th {
    background-color: #f2f2f2; /* Light gray for header */
    font-weight: bold; /* Bold header text */
}

.logo img {
    max-width: 100px; /* Resize the logo */
    height: auto; /* Maintain aspect ratio */
    display: block;
    margin: 0 auto; /* Center the logo */
}

    </style>
</body>
</html>
