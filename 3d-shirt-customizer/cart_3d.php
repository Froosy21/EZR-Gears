<?php
session_start();
include('../LogReg/database.php');

$price = 750;
$email_error = '';
$email_verified = isset($_SESSION['verified_email']);

// If the email form is submitted, verify the email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Check if the email exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $email_verified = true;
        $_SESSION['verified_email'] = $email;
    } else {
        $email_error = "No account found with this email. Please try again.";
    }
    $stmt->close();
}

// If the form is submitted and email is verified, process the checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout']) && isset($_SESSION['verified_email'])) {
    $email = $_SESSION['verified_email'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $total_price = $price * $quantity;

    // Fetch snapshot data from session instead of model_snapshots table
    if (isset($_SESSION['snapshots']) && !empty($_SESSION['snapshots'])) {
        $front_image = $_SESSION['snapshots']['front'];
        $back_image = $_SESSION['snapshots']['back'];
        $left_image = $_SESSION['snapshots']['left'];
        $right_image = $_SESSION['snapshots']['right'];
        
        // Insert order into the database
        $stmt = $conn->prepare("INSERT INTO orders (user_email, front_image_path, back_image_path, left_image_path, right_image_path, quantity, total_price, address, contact_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiiss", $email, $front_image, $back_image, $left_image, $right_image, $quantity, $total_price, $address, $contact);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            echo "<p>Order successfully placed! Your order ID is: $order_id</p>";
            echo "<p>Email: $email</p>";
            echo "<p>Address: $address</p>";
            echo "<p>Contact: $contact</p>";
            echo "<p>Total Price: PHP " . number_format($total_price, 2) . "</p>";
            
            // Clear the verified email from session after successful checkout
            unset($_SESSION['verified_email']);
            $email_verified = false;
        } else {
            echo "<p>Error placing order. Please try again.</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error: No snapshot data found for this user.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <style>
        .snapshot-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .snapshot-preview img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .checkout-section {
            margin-top: 30px;
        }
        header {
            background-color: #800000; /* Reddish maroon */
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 24px;
        }

        header h1 {
            margin: 0;
            font-size: large;
            font-style: oblique;
            display: flex;
            align-items: center;
        }

        /* Add styling for the SVG logo */
        header svg {
            width: 150px; /* Adjust the width as needed */
            height: auto; /* Maintain aspect ratio */
            margin-right: 20px; /* Space between the logo and the h1 text */
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        header nav ul li {
            margin: 0 15px;
        }

        header nav a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        header nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60">
            <rect width="200" height="60" fill="transparent"/>
            <text x="10" y="45" font-family="Arial Black, sans-serif" font-size="40" fill="#EF4444" font-weight="bold">
                EZR
            </text>
            <text x="105" y="30" font-family="Arial, sans-serif" font-size="18" fill="#374151">
                DESIGNER
            </text>
            <text x="105" y="50" font-family="Arial Black, sans-serif" font-size="24" fill="#3B82F6" font-weight="bold">
                3D
            </text>
            <path d="M170,15 l10,-5 l10,5 l-10,5 z" fill="#3B82F6"/>
            <path d="M170,15 l10,5 l0,10 l-10,-5 z" fill="#2563EB"/>
            <path d="M180,20 l10,-5 l0,10 l-10,5 z" fill="#1D4ED8"/>
        </svg>
        <nav>
            <ul>
                <li><a href="index.html">Back to Designer</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="title">Your Cart</h1>
        
         <!-- Modified snapshot preview section -->
        <div class="snapshot-preview">
            <?php 
            if (isset($_SESSION['snapshot_paths']) && !empty($_SESSION['snapshot_paths'])):
                foreach ($_FILES as $key => $file) {
    if (strpos($key, 'snapshot_') !== false) {
        // Debugging: Log the snapshot path and file details
        error_log("Snapshot received: " . $file['name']);
        
        $snapshotName = $file['name'];
        $snapshotPath = "uploads/" . $snapshotName;
        if (move_uploaded_file($file['tmp_name'], $snapshotPath)) {
            error_log("Snapshot saved successfully: " . $snapshotPath);
        } else {
            error_log("Error saving snapshot: " . $file['name']);
        }

        // Insert snapshot into 3d_orders table
        $stmt2 = $conn->prepare("INSERT INTO 3d_orderstst (order_id, view, image_path) VALUES (?, ?, ?)");
        $view = str_replace('snapshot_', '', $key); // Extract view from the key
        $stmt2->bind_param("iss", $orderId, $view, $snapshotPath);
        $stmt2->execute();
    }
}
            else:
            ?>
                <p class="has-text-centered">No snapshots available</p>
            <?php endif; ?>
        </div>
        
        <!-- Display price -->
        <div class="box">
            <h2 class="subtitle">Total Price: PHP <?= number_format($price, 2) ?></h2>
        </div>

        <!-- Email verification form -->
        <?php if (!$email_verified): ?>
        <div class="box">
            <h2 class="subtitle">Verify Your Email</h2>
            <form method="POST" action="">
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" required placeholder="Enter your email">
                    </div>
                </div>
                <?php if ($email_error): ?>
                    <p class="has-text-danger"><?= $email_error ?></p>
                <?php endif; ?>
                <div class="control">
                    <button class="button is-info" type="submit" name="verify_email">Verify Email</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Address and contact info form -->
        <?php if ($email_verified): ?>
        <div class="checkout-section">
            <h2 class="subtitle">Enter Your Details</h2>
            <form method="POST" action="">
                <div class="field">
                    <label class="label">Address</label>
                    <div class="control">
                        <textarea class="textarea" name="address" required placeholder="Enter your address"></textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Contact Number</label>
                    <div class="control">
                        <input class="input" type="text" name="contact" required placeholder="Enter your contact number">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Quantity</label>
                    <div class="control">
                        <input class="input" type="number" name="quantity" required min="1" value="1">
                    </div>
                </div>

                <!-- Cash on delivery payment option -->
                <div class="field">
                    <label class="label">Payment Method</label>
                    <div class="control">
                        <p class="is-size-5">Cash on Delivery (COD)</p>
                    </div>
                </div>

                <!-- Checkout Button -->
                <div class="control">
                    <button class="button is-primary" type="submit" name="checkout">Proceed to Checkout</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
