<?php
session_start();
require_once 'admin_login.php';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_notifications.php');
    exit();
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['uname'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (handleAdminLogin($username, $password)) {
        header('Location: admin_notifications.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form action="index.php" method="post">
        <h2>EZR ADMIN LOGIN</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <label>User Name</label>
        <input type="text" name="uname" placeholder="Username" required><br>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required><br>

        <button type="submit">LOGIN</button>
    </form>   
</body>
</html>