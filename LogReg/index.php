<!DOCTYPE html>
<html lang="en">
<head>
    <title>LOGIN</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form action="login.php" method="post" autocomplete="off">
        <h1>LOGIN</h1>
        <?php if(isset($_GET['error'])) { ?>
            <p class="error"><?php echo $_GET['error']; ?></p>
        <?php } ?>
        <label>Email</label>
        <input type="text" name="email" required placeholder="Enter Your Email"><br>
        <label>Password</label>
        <input type="password" name="userpass" required placeholder="Enter Your Password"><br>
        <a href="registration.php">Don't have an account?</a>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>

