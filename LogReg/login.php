<?php
session_start();
include('database.php');

function validate($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if (isset($_POST['login'])) {
    $email = validate($_POST['email']);
    $userpass = validate($_POST['userpass']); 

    if (empty($email)) {
        header("Location: index.php?error=Email is required");
        exit();
    } elseif (empty($userpass)) {
        header("Location: index.php?error=Password is required");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // verified check sa email
        if ($row['verified'] == 0) {
            header("Location: index.php?error=Account not verified. Please check your email for the verification link.");
            exit();
        }

        // password verification
        if (password_verify($userpass, $row['userpass'])) {
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $row['id'];
            $_SESSION['fname'] = $row['fname']; 
            header("Location: ../homepage/home.php");
            exit();
        } else {
            header("Location: index.php?error=Incorrect email or password (password mismatch)");
            exit();
        }
    } else {
        header("Location: index.php?error=Incorrect email or password (email not found)");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
