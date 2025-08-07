<?php
session_start();
include('../LogReg/database.php');

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../LogReg/login.php');
    exit();
}

// Fetch logged-in user's email
$email = $_SESSION['email'];

// Fetch orders for the logged-in user including payment status
$sql = "SELECT product_name, quantity, price, address, phonenum, order_date, status FROM orders WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - EZReborn</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hamburger Menu */
        .hamburger-menu {
            position: relative;
            display: inline-block;
        }

        .hamburger-icon {
            font-size: 24px;
            cursor: pointer;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .dropdown-menu ul li {
            padding: 10px 20px;
        }

        .dropdown-menu ul li a {
            text-decoration: none;
            color: black;
            display: block;
        }

        .dropdown-menu ul li:hover {
            background-color: #f1f1f1;
        }

        /* Show dropdown when active */
        .hamburger-menu.active .dropdown-menu {
            display: block;
        }

    </style>
</head>
<body>
<header>
<img src="img/EzR Logo.png" alt="Logo" class="logo">
        <h1>EZ reborn gears</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="calendar.php">Events</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
        <!-- Hamburger menu -->
        <div class="hamburger-menu">
            <div class="hamburger-icon">&#9776;</div>
            <div class="dropdown-menu">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="user_orders.php">Your Orders</a></li>
                    <li><a href="../LogReg/logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
</header>

<main class="orders-container">
    <p class="tracking-notice">
        If you want to track your orders, kindly copy the tracking number sent to you by J&T and paste it onto this 
        <a href="https://www.jtexpress.ph/trajectoryQuery" target="_blank">link</a>. Thank you.
    </p>

    <h2>Order History</h2>
    
    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-item">
                <h3>Product: <?php echo htmlspecialchars($order['product_name']); ?></h3>
                <p>Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
                <p>Price: ₱<?php echo number_format($order['price'], 2); ?></p>
                <p>Shipping Address: <?php echo htmlspecialchars($order['address']); ?></p>
                <p>Phone Number: <?php echo htmlspecialchars($order['phonenum']); ?></p>
                <p>Order Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                
                <?php if ($order['status'] === 'unpaid'): ?>
                    <p>Status: <span style="color: red;">Unpaid</span></p>
                    <p><a class="payment-link" href="<?php echo htmlspecialchars($order['payment_link']); ?>">Complete Payment</a></p>
                <?php else: ?>
                    <p>Status: <span style="color: green;">Paid</span></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<script>
        // Hamburger menu toggle
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const hamburgerIcon = document.querySelector('.hamburger-icon');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        hamburgerIcon.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
        });
    </script>
</body>
</html>
