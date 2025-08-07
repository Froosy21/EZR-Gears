<?php
session_start();
if (isset($_SESSION['payment_url'])) {
    $payment_url = $_SESSION['payment_url'];

    header("Location: $payment_url");
    exit();
} else {
    echo "No payment URL found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - EZReborn</title>
</head>
<body>
    <div class="payment-container">
        <h1>Payment</h1>
        <p>If you are not redirected automatically, <a href="<?php echo htmlspecialchars($payment_url); ?>">click here</a>.</p>
    </div>
</body>
</html>