<?php
session_start();
include('database.php');

require '../vendor/autoload.php';
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function validate($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if (isset($_POST['register'])) {
    $fname = validate($_POST['fname']);
    $lname = validate($_POST['lname']);
    $phonenum = validate($_POST['number']);
    $email = validate($_POST['email']);
    $userpass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirmpassword = $_POST['confirmpassword'];

    if (empty($fname) || empty($lname) || empty($phonenum) || empty($email) || empty($_POST['password'])) {
        header("Location: registration.php?error=All fields are required");
        exit();
    }

    if ($_POST['password'] != $confirmpassword) {
        header("Location: registration.php?error=Passwords do not match");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $verificationToken = bin2hex(random_bytes(16));
        
        $stmt = $conn->prepare("INSERT INTO users (fname, lname, phonenum, email, userpass, verifytoken) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fname, $lname, $phonenum, $email, $userpass, $verificationToken);
        $stmt->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.ezr-gears.com'; //SMTP server cpanel atong gamit email acc
            $mail->SMTPAuth = true;
            $mail->Username = 'support@ezr-gears.com'; 
            $mail->Password = 'EZRsupport21'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port = 465;

            $mail->setFrom('support@ezr-gears.com', 'EZR Gears'); 
            $mail->addAddress($email, $fname . ' ' . $lname);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $verificationLink = "ezr-gears.com/EzRebornProgram/LogReg/verify.php?token=" . $verificationToken;
            $mail->Body = "Click the link to verify your email: <a href='$verificationLink'>$verificationLink</a>";
            
            $mail->send();
            header("Location: registration.php?success=Check your email to verify your account");
            exit();
        } catch (Exception $e) {
            header("Location: registration.php?error=Mailer Error: {$mail->ErrorInfo}");
            exit();
        }
    } else {
        header("Location: registration.php?error=Email already exists");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form action="registration.php" method="post" autocomplete="off">
        <h1>REGISTER</h1>
        <?php if(isset($_GET['error'])) { ?>
            <p class="error"><?php echo $_GET['error']; ?></p>
        <?php } elseif(isset($_GET['success'])) { ?>
            <p class="success"><?php echo $_GET['success']; ?></p>
            <button><a href="index.php">Continue to Login</a></button>
        <?php } ?>
        <label for="fname">First Name</label>
        <input type="text" name="fname" id="fname" placeholder="Enter Your First Name" required><br>
        <label for="lname">Last Name</label>
        <input type="text" name="lname" id="lname" placeholder="Enter Your Last Name" required><br>
        <label for="number">Phone Number</label>
        <input type="text" name="number" id="phonenum" maxlength="11" placeholder="Enter Your Phone Number" required><br>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter Your Email" required><br>
        <label for="password">Password</label>
        <input type="password" name="password" id="userpass" maxlength="16" placeholder="Enter Your Password" required><br>
        <label for="confirmpassword">Confirm Password</label>
        <input type="password" name="confirmpassword" id="confirmpassword" maxlength="16" placeholder="Please Confirm Your Password" required><br>
        <button type="submit" name="register">Register</button>
        <a href="index.php">Already have a registered account?</a>
    </form>
</body>
</html>
