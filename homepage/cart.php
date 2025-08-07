<?php
session_start();
include('../LogReg/database.php');
require '../vendor/autoload.php';
use GuzzleHttp\Client;

function logPaymentActivity($message, $data = []) {
    $logEntry = date('Y-m-d H:i:s') . " - " . $message . "\n";
    if (!empty($data)) {
        $logEntry .= json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    $logEntry .= "----------------------------------------\n";
    
    file_put_contents(
        __DIR__ . '/payment_logs.txt', 
        $logEntry, 
        FILE_APPEND
    );
}

if (!isset($_SESSION['email'])) {
    header('Location: ../LogReg/login.php');
    exit();
}

$sql = "SELECT id, name, price, quantity, weight, imagePath FROM product";
$result = $conn->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = [
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'weight' => $row['weight'],
            'image' => '../admin/' . $row['imagePath']
        ];
    }
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
$total_weight = 0;

foreach ($cart as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $product = $products[$product_id];
        $total += $product['price'] * $quantity;
        $total_weight += $product['weight'] * $quantity;
    }
}

$shipping_rates = [
    'Luzon' => [100, 180, 200, 300, 400, 500],
    'Visayas' => [105, 195, 220, 330, 440, 550],
    'Mindanao' => [115, 205, 230, 340, 450, 560]
];

function calculateShipping($weight, $region, $rates) {
    $index = min(ceil($weight / 1000), 6) - 1;
    return $rates[$region][$index];
}

if (isset($_POST['ajax']) && $_POST['ajax'] == 'calculate_shipping') {
    $selected_region = $_POST['region'];
    $shipping_fee = calculateShipping($total_weight, $selected_region, $shipping_rates);
    $grand_total = $total + $shipping_fee;

    $_SESSION['shipping_fee'] = $shipping_fee;

    echo json_encode([
        'shipping_fee' => number_format($shipping_fee, 2),
        'grand_total' => number_format($grand_total, 2)
    ]);
    exit();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $quantity = max(0, (int)$quantity);
            if ($quantity === 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        header('Location: cart.php');
        exit();
    }

    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['remove_item'];
        unset($_SESSION['cart'][$product_id]);
        header('Location: cart.php');
        exit();
    }
    
    if (isset($_POST['pay'])) {
        $billing_address = trim($_POST['billing_address']);
        $phone_number = trim($_POST['phone_number']);
        $email = $_SESSION['email'];
        $shipping_fee = isset($_SESSION['shipping_fee']) ? $_SESSION['shipping_fee'] : 0;

        if (empty($billing_address) || empty($phone_number)) {
            echo "<script>alert('Billing address and phone number are required!');</script>";
        } else {
            try {
                logPaymentActivity("Starting payment process", [
                    'email' => $email,
                    'amount' => $total + $shipping_fee,
                    'shipping_fee' => $shipping_fee
                ]);

                $curl = curl_init();
                $paymongo_api_key = 'sk_test_Rq5WQbJcwvAnu4ewEgurmSfz';

                $data = [
                    'data' => [
                        'attributes' => [
                            'amount' => ($total + $shipping_fee) * 100,
                            'currency' => 'PHP',
                            'description' => 'Order Payment for ' . $email,
                            'statement_descriptor' => 'EZReborn',
                            'payment_method_allowed' => [
                                'card',
                                'gcash'
                            ],
                            'redirect' => [
                                'success' => 'https://ezr-gears.com/EzRebornProgram/web/home.php',
                                'failed' => 'https://ezr-gears.com/EzRebornProgram/web/payments/payment_failed.php'
                            ]
                        ]
                    ]
                ];

                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.paymongo.com/v1/links",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => [
                        "Authorization: Basic " . base64_encode($paymongo_api_key),
                        "Content-Type: application/json"
                    ]
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                if ($err) {
                    logPaymentActivity("PayMongo cURL Error", ['error' => $err]);
                    error_log("PayMongo cURL Error: " . $err);
                    echo "<script>alert('Payment processing error occurred. Please try again.');</script>";
                } else {
                    $response_data = json_decode($response, true);
                    logPaymentActivity("PayMongo Response", $response_data);
                    
                    if (isset($response_data['data']['id']) && isset($response_data['data']['attributes']['checkout_url'])) {
                        $payment_link_id = $response_data['data']['id'];
                        
                        logPaymentActivity("Payment Link Created", [
                            'link_id' => $payment_link_id,
                            'email' => $email
                        ]);

                        $conn->begin_transaction();
                        
                        try {
                            foreach ($cart as $product_id => $quantity) {
                                // Calculate individual product price
                                $product_price = $products[$product_id]['price'] * $quantity;
                                
                                $stmt = $conn->prepare("
                                    INSERT INTO orders (
                                        email, product_name, quantity, price, 
                                        address, phonenum, order_date, payment_id, payment_status,
                                        shipping_fee
                                    ) 
                                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 'pending', ?)
                                ");
                                
                                $stmt->bind_param(
                                    "ssiisssd", 
                                    $email, 
                                    $products[$product_id]['name'], 
                                    $quantity, 
                                    $product_price,
                                    $billing_address, 
                                    $phone_number,
                                    $payment_link_id,
                                    $shipping_fee
                                    // Add shipping fee to each order
                                );
                                
                                $stmt->execute();
                                $stmt->close();
                                
                                $stmt_inventory = $conn->prepare("
                                    UPDATE inventory 
                                    SET in_stock = in_stock - ?, out_stock = out_stock + ?
                                    WHERE product_id = ?
                                ");
                                $stmt_inventory->bind_param("iii", $quantity, $quantity, $product_id);
                                $stmt_inventory->execute();
                                $stmt_inventory->close();
                            }
                        
                            $conn->commit();
                            unset($_SESSION['cart']);
                            unset($_SESSION['shipping_fee']);
                            
                            header('Location: ' . $response_data['data']['attributes']['checkout_url']);
                            exit();
                            
                        } catch (Exception $e) {
                            $conn->rollback();
                            error_log("Order Creation Error: " . $e->getMessage());
                            echo "<script>alert('Error creating order. Please try again.');</script>";
                        }
                    } else {
                        logPaymentActivity("PayMongo Error: Invalid response data", $response_data);
                        error_log("PayMongo Error: Invalid response data");
                        echo "<script>alert('Payment link creation failed. Please try again.');</script>";
                    }
                }
            } catch (Exception $e) {
                logPaymentActivity("Payment Processing Error", ['error' => $e->getMessage()]);
                error_log("Payment Processing Error: " . $e->getMessage());
                echo "<script>alert('An error occurred. Please try again.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - EZReborn</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Example styles for slider */
        nav {
            display: flex;
            justify-content: center; /* Center the navigation items */
            background-color: #c21212; /* Match the header background */
            padding: 10px 0; /* Vertical padding */
            }

        nav ul {
            list-style: none; /* Remove bullet points */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove default margin */
            display: flex; /* Display items in a row */
            }

        nav ul li {
            padding: 15px 20px; /* Space between items */
            }

        nav ul li a {
            color: white; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make links bold */
            transition: color 0.3s; /* Smooth color transition */
            }

        nav ul li a:hover {
            color: #ff5733; /* Change color on hover */
            }
            /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            }

header {
            background-color: #c21212;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Ensure body content is pushed below the fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on your header height */
        }

        header img.logo {
            width: 80px;
            height: auto;
            vertical-align: middle;
        }

        header h1 {
            display: inline;
            margin: 0;
            padding: 0;
            vertical-align: middle;
        }

 nav {
            display: flex;
            justify-content: center; /* Center the navigation items */
            background-color: #c21212; /* Match the header background */
            padding: 10px 0; /* Vertical padding */
            }

        nav ul {
            list-style: none; /* Remove bullet points */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove default margin */
            display: flex; /* Display items in a row */
            }

        nav ul li {
            padding: 15px 20px; /* Space between items */
            }

        nav ul li a {
            color: white; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make links bold */
            transition: color 0.3s; /* Smooth color transition */
            }

        nav ul li a:hover {
            color: #ff5733; /* Change color on hover */
            }
            /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            }

header {
            background-color: #c21212;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Ensure body content is pushed below the fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on your header height */
        }

        header img.logo {
            width: 80px;
            height: auto;
            vertical-align: middle;
        }

        header h1 {
            display: inline;
            margin: 0;
            padding: 0;
            vertical-align: middle;
        }

        nav {
            display: flex;
            justify-content: center;
            background-color: #c21212;
            padding: 10px 0;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        nav ul li {
            padding: 15px 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #ff5733;
        }

        .hamburger-menu {
            position: absolute;
            right: 20px; /* Adjust this value to your liking */
            top: 50%;
            transform: translateY(-50%);
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

        h3 {
            color: rgb(88, 33, 33);
            font-weight: bold;
        }
         .cart-container {
            padding: 20px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .cart-item img {
            width: 100px;
            height: auto;
        }
        .cart-total {
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
        /* Form Styles */
form {
    max-width: 4000px; /* Adjusts the width of the forms */
    margin: 30px auto; /* Centers the form */
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Input Fields */
form input[type="text"],
form input[type="number"] {
   max-width: 1000px; /* Set a maximum width for the billing address input */
    width: 100%; /* Ensure responsiveness */
    padding: 8px;
    margin: 10px 0;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    
}
.cart-item { background-color: rgba(34, 3, 3, 0.14);
            
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
            border-radius: 0px;
            /* Rounded corners */
            padding: 15px;
            /* Space inside the card */
            margin: 10px;
            /* Space between cards */
            text-align: center;
           
            /* Smooth transition */
            text-decoration: none;
            /* Removes the underline */
            color: inherit;
            /* Keeps the text color consistent with parent */
           max-width:100%;
}
/* Quantity Box */
form input[type="number"] {
    max-width: 100px; /* Limits the width of the number input */
    text-align: center;
    height:30px;
}

/* Submit Button */
form button {
    background-color: #B22222; /* Green color for submit buttons */
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    margin: 10px 0;
    transition: background-color 0.3s ease;
    height:50px;
}

form button:hover {
    background-color: #218838;
}

/* Remove Button */
form .remove-btn {
    background-color: red; /* Red color for remove buttons */
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    height:20px;
    transition: background-color 0.3s ease;
}
        li a svg {
    width: 16px;
    height: 16px;
  }

form .remove-btn:hover {
    background-color: #c82333;
}

/* Align Inputs and Buttons in a Row */
form .form-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

form .form-row > * {
    margin: 5px;
}
        
    </style>
</head>
<body>
<header>
        <img src="img/EzR Logo.png" alt="Logo" class="logo">
        <nav>
            <ul>
                <li>
  <a href="home.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M8.543 2.232a.75.75 0 0 0-1.085 0l-5.25 5.5A.75.75 0 0 0 2.75 9H4v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 1 1 2 0v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1V9h1.25a.75.75 0 0 0 .543-1.268l-5.25-5.5Z" />
</svg>

    Home
  </a>
</li>

                <li><a href="shop.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M4.5 7c.681 0 1.3-.273 1.75-.715C6.7 6.727 7.319 7 8 7s1.3-.273 1.75-.715A2.5 2.5 0 1 0 11.5 2h-7a2.5 2.5 0 0 0 0 5ZM6.25 8.097A3.986 3.986 0 0 1 4.5 8.5c-.53 0-1.037-.103-1.5-.29v4.29h-.25a.75.75 0 0 0 0 1.5h.5a.754.754 0 0 0 .138-.013A.5.5 0 0 0 3.5 14H6a.5.5 0 0 0 .5-.5v-3A.5.5 0 0 1 7 10h2a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h2.5a.5.5 0 0 0 .112-.013c.045.009.09.013.138.013h.5a.75.75 0 1 0 0-1.5H13V8.21c-.463.187-.97.29-1.5.29a3.986 3.986 0 0 1-1.75-.403A3.986 3.986 0 0 1 8 8.5a3.986 3.986 0 0 1-1.75-.403Z" />
</svg>
Shop</a></li>
                <li><a href="calendar.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a1 1 0 0 0-1 1v4.5a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-7Z" clip-rule="evenodd" />
</svg>

Events</a></li>
                <li><a href="about.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0ZM9 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM6.75 8a.75.75 0 0 0 0 1.5h.75v1.75a.75.75 0 0 0 1.5 0v-2.5A.75.75 0 0 0 8.25 8h-1.5Z" clip-rule="evenodd" />
</svg>

About Us</a></li>
                <li>
  <a href="cart.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M1.75 1.002a.75.75 0 1 0 0 1.5h1.835l1.24 5.113A3.752 3.752 0 0 0 2 11.25c0 .414.336.75.75.75h10.5a.75.75 0 0 0 0-1.5H3.628A2.25 2.25 0 0 1 5.75 9h6.5a.75.75 0 0 0 .73-.578l.846-3.595a.75.75 0 0 0-.578-.906 44.118 44.118 0 0 0-7.996-.91l-.348-1.436a.75.75 0 0 0-.73-.573H1.75ZM5 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM13 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" />
</svg>

    Cart
  </a>
</li>

            </ul>
        </nav>
        <div class="hamburger-menu">
            <div class="hamburger-icon">&#9776;</div> <!-- Hamburger icon -->
            <div class="dropdown-menu">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="user_orders.php">Your Orders</a></li>
                    <li><a href="../LogReg/logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </header>

<main class="cart-container">
    <h2>Your Cart</h2>
    <form method="post" action="cart.php">
        <?php if (empty($cart)) : ?>
            <p>Your cart is empty.</p>
        <?php else : ?>
            <?php foreach ($cart as $product_id => $quantity) : ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($products[$product_id]['image']); ?>" alt="<?php echo htmlspecialchars($products[$product_id]['name']); ?>">
                    <span><?php echo htmlspecialchars($products[$product_id]['name']); ?></span>
                    <span>Price: ₱<?php echo number_format($products[$product_id]['price'], 2); ?></span>
                    <input type="number" name="quantities[<?php echo $product_id; ?>]" value="<?php echo $quantity; ?>" min="0" style="width: 60px;">
                    <button type="submit" name="remove_item" value="<?php echo $product_id; ?>">Remove</button>
                </div>
            <?php endforeach; ?>

            <h3>Shipping Region</h3>
            <select name="region" id="region" required>
                <option value="">Select Region</option>
                <option value="Luzon">Luzon</option>
                <option value="Visayas">Visayas</option>
                <option value="Mindanao">Mindanao</option>
            </select>

            <div class="cart-total">
                Total: ₱<?php echo number_format($total, 2); ?><br>
                Shipping: ₱<span id="shipping_fee">0.00</span><br>
                <strong>Grand Total: ₱<span id="grand_total"><?php echo number_format($total, 2); ?></span></strong>
            </div>

            <h3>Billing Information</h3>
            <label for="billing_address">Billing Address:</label><br>
            <input type="text" id="billing_address" name="billing_address" required style="width: 100%; margin-bottom: 10px;"><br>

            <label for="phone_number">Phone Number:</label><br>
            <input type="text" id="phone_number" name="phone_number" required style="width: 100%; margin-bottom: 10px;"><br>

            <button type="submit" name="update_cart">Update Cart</button>
            <button type="submit" name="pay">Pay</button>
        <?php endif; ?>
    </form>
</main>

<script>

document.getElementById('region').addEventListener('change', function() {
    var region = this.value;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'cart.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (this.status === 200) {
            var response = JSON.parse(this.responseText);
            document.getElementById('shipping_fee').textContent = response.shipping_fee;
            document.getElementById('grand_total').textContent = response.grand_total;
        }
    };

    xhr.send('ajax=calculate_shipping&region=' + region);
});

function updateNotificationCount() {
    fetch('fetch_notifications.php')
        .then(response => response.json())
        .then(data => {
            console.log('Notifications updated successfully');
        })
        .catch(error => {
            console.error('Error updating notifications:', error);
        });
}

setInterval(updateNotificationCount, 30000);

</script>

<script>
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const hamburgerIcon = document.querySelector('.hamburger-icon');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    hamburgerIcon.addEventListener('click', () => {
        hamburgerMenu.classList.toggle('active');
    });
</script>

</body>
</html>
