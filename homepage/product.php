<?php 
session_start();
include('../LogReg/database.php');

$product_id = (int)$_GET['id'];

// Fetch product details
$sql = "SELECT id, name, price, quantity, imagePath, description FROM product WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header('Location: shop.php');
    exit();
}

// Fetch more products
$more_products_sql = "SELECT id, name, price, imagePath FROM product WHERE id != $product_id LIMIT 4";
$more_products_result = $conn->query($more_products_sql);

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header('Location: product.php?id=' . $product_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product - EZReborn</title>
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
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-description {
            margin-top: 20px;
        }

        .product-description h3 {
            margin-bottom: 10px;
        }

        .product-description p {
            line-height: 1.6;
            text-align: justify;
        }
        .product-image img {
            width: 100%;
            height: auto;
        }
        .product-info h2, .product-info p {
            text-align: left;
        }
        /* Hamburger Menu */
            /* Sliding Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            right: -500px;
            width: 444px;
            height: 100%;
            background-color: #fff;
            border-left: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            overflow-y: auto;
            transition: right 0.3s ease;
            z-index: 1000;
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar img {
            max-width: 100%;
            height: auto;
        }

        .sidebar .content {
            margin-top: 10px;
        }

        .sidebar a {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #aa1345;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }

        .sidebar a:hover {
            background-color: #0056b3;
        }

        /* Close button */
        .close-btn {
            position: absolute;
            top: 15px;
            left: 10px;
            background-color:rgb(223, 5, 5);
            color: #fff;
            border: 2px;
            border-radius: 30%;
            padding: 6px;
            cursor: pointer;
        }

        #navigation {
            text-align: center;
            margin-bottom: 20px;
        }
        #navigation button {
            margin: 0 10px;
            padding: 10px 15px;
            background-color: #aa1345;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #navigation span {
            font-size: 1.2em;
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
         .product-detail {
        display: flex;
        align-items: flex-start; /* Align items at the top */
        gap: 20px; /* Space between image and info */
        padding: 20px;
        background: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        margin: 20px;
    }

    .product-image {
        flex: 1; /* Image takes up 40% of width */
        max-width: 40%;
    }

    .product-image img {
        width: 100%;
        height: auto;
        border-radius: 10px; /* Optional: rounded corners */
    }

    .product-info {
        flex: 2; /* Info takes up 60% of width */
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-info h2 {
        font-size: 1.8rem;
        color: #333;
    }

    .product-info p {
        font-size: 1.2rem;
        color: #555;
    }

    .product-info form {
        margin-top: 10px;
    }

    .product-info form input[type="number"] {
        padding: 5px;
        width: 60px;
        margin-right: 10px;
    }

    .product-info form button {
        padding: 5px 15px;
        background-color: #aa1345;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .product-info form button:hover {
        background-color: #ff5733;
    }

    .product-description {
        margin-top: 20px;
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .product-description h3 {
        margin-bottom: 10px;
        font-size: 1.5rem;
        color: #aa1345;
    }

    .product-description p {
        font-size: 1rem;
        line-height: 1.6;
        text-align: justify;
    }
           body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #c21212;
            color: white;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
       .featured-products-scroll {
        overflow-x: auto;
        white-space: nowrap;
        margin: 20px;
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
    }

    .featured-products {
        display: inline-flex;
        gap: 10px;
    }

    .product-card {
        flex: none; /* Prevents shrinking */
        width: 200px;
        background: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 10px;
        border-radius: 5px;
    }

    .product-card img {
        max-width: 100%;
        height: auto;
    }

    .product-card h3 {
        font-size: 1rem;
        margin: 10px 0;
    }

    .product-card p {
        color: #555;
        margin: 5px 0;
    }

    .product-card a {
        text-decoration: none;
        background-color: #aa1345;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        display: inline-block;
        margin-top: 10px;
    }

    .product-card a:hover {
        background-color: #ff5733;
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

   <main>
    <div class="product-detail">
        <div class="product-image">
            <img src="../admin/<?php echo htmlspecialchars($product['imagePath']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
            <p>Available: <?php echo $product['quantity']; ?></p>
            <form action="product.php?id=<?php echo $product_id; ?>" method="post">
                <input type="number" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" required>
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            </div>
        </div>
    </div>

    <h2 style="margin: 20px;"></h2>
<div style="display: flex; justify-content: space-between; align-items: center; margin: 20px;">
    <h2>More Products</h2>
    <a href="shop.php" style="text-decoration: none; background-color: #aa1345; color: white; padding: 10px 20px; border-radius: 5px; font-size: 1rem;">View All Products</a>
</div>
<div class="featured-products-scroll">
    <div class="featured-products">
        <?php while ($row = $more_products_result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="../admin/<?php echo htmlspecialchars($row['imagePath']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p>₱<?php echo number_format($row['price'], 2); ?></p>
                <a href="product.php?id=<?php echo $row['id']; ?>">View Product</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</main>
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
<?php $conn->close(); ?>
