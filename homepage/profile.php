<?php
session_start();
include('../LogReg/database.php'); // Include database connection

$email = $_SESSION['email']; // Get the logged-in user's email

// Fetch current user info
$sql = "SELECT fname, lname, phonenum, email FROM users WHERE email = '$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newEmail = $_POST['email'];
    $newPhone = $_POST['phone'];
    $newPassword = $_POST['password'];
    
    // Update email and phone number
    $conn->query("UPDATE users SET email = '$newEmail', phonenum = '$newPhone' WHERE email = '$email'");
    $_SESSION['email'] = $newEmail; // Update session email
    
    // If password is updated, check for cooldown (one week)
    $lastPasswordChange = $_SESSION['last_password_change'] ?? strtotime('-8 days'); // Default to 8 days ago
    if (time() - $lastPasswordChange >= 604800) { // 604800 seconds in one week
        $conn->query("UPDATE users SET userpass = '$newPassword' WHERE email = '$newEmail'");
        $_SESSION['last_password_change'] = time(); // Set the last password change time
        echo "Password updated successfully!";
    } else {
        echo "You can only change your password once a week.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EZReborn</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-detail {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
        }
        .product-image {
            width: 40%;
            margin: 10px;
        }
        .product-info {
            width: 60%;
            margin: 10px;
        }
        .product-image img {
            width: 100%;
            height: auto;
        }
        .product-info h2, .product-info p {
            text-align: left;
        }
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
    <h2>Profile</h2>
    <form method="POST" action="profile.php">
        <label>First Name: <?php echo $user['fname']; ?></label><br>
        <label>Last Name: <?php echo $user['lname']; ?></label><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>
        <label>Phone Number:</label>
        <input type="text" name="phone" value="<?php echo $user['phonenum']; ?>" required><br>
        <label>New Password:</label>
        <input type="password" name="password"><br>
        <button type="submit">Update Profile</button>
    </form>
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
