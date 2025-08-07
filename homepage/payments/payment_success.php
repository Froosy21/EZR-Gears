<?php
session_start();
include ('../LogReg/database.php');

if (isset($_SESSION['order_id'])) {
    // Fetch order details from the session
    $order_id = $_SESSION['order_id'];
    $email = $_SESSION['email'] ?? 'Unknown';  // Default if not set
    $amount = $_SESSION['amount'] ?? 0;        // Default if not set

    // Update order status in the database
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
    } catch (Exception $e) {
        // Log error and redirect to an error page
        error_log("Failed to update order: " . $e->getMessage());
        header('Location: error.php?error=payment_update_failed');
        exit();
    }

    // Clear session data related to the order and cart
    unset($_SESSION['cart'], $_SESSION['order_id'], $_SESSION['amount']);

} else {
    // If session data is missing, redirect to cart or home page
    header('Location: cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - EZReborn</title>
    <style>
        .success-container {
            padding: 20px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .success-message {
            color: green;
            font-size: 24px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>Payment Successful</h1>
        <p class="success-message">Thank you for your payment of ₱<?php echo number_format($amount / 100, 2); ?>.</p>
        <p>Your order (ID: <?php echo htmlspecialchars($order_id); ?>) has been processed successfully.</p>
        <a href="index.php">Return to Homepage</a>
    </div>
</body>
</html>
