// File: payment_failed.php
<?php
session_start();

// You might want to log the failed payment attempt here
// Example: error_log("Payment failed for order: " . $_SESSION['order_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - EZReborn</title>
    <style>
        .failed-container {
            padding: 20px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .error-message {
            color: red;
            font-size: 24px;
            margin-top: 20px;
        }
        .retry-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            border: none;
            cursor: pointer;
        }
        .retry-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <h1>Payment Failed</h1>
        <p class="error-message">Unfortunately, your payment could not be processed.</p>
        <p>Please try again or contact support if the issue persists.</p>
        <a href="cart.php"><button class="retry-btn">Retry Payment</button></a>
    </div>
</body>
</html>
