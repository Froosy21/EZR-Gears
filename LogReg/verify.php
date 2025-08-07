<?php
session_start();
include('database.php'); 

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE verifytoken = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET verified = 1 WHERE verifytoken = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        header("Location: index.php?success=Your email has been verified. You can now log in.");
        exit();
    } else {
        header("Location: index.php?error=Invalid verification token.");
        exit();
    }
} else {
    header("Location: index.php?error=Verification token missing.");
    exit();
}
?>
