<?php
session_start();
include('../LogReg/database.php');  

$message = '';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); 


    $product_sql = "SELECT * FROM product WHERE id = ?";
    $stmt = $conn->prepare($product_sql);

    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
        } else {
            echo "Product not found.";
            exit();
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
}

elseif (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']); 
    $product_name = $_POST['name'];
    $product_price = $_POST['price'];
    $product_quantity = $_POST['quantity'];


    $update_sql = "UPDATE product SET 
                   name = ?,
                   price = ?,
                   quantity = ?
                   WHERE id = ?";
    $stmt = $conn->prepare($update_sql);

    if ($stmt) {
        $stmt->bind_param("sdii", $product_name, $product_price, $product_quantity, $product_id);

        if ($stmt->execute()) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating product: " . $stmt->error;
        }

        header("Location: home.php?message=" . urlencode($message));
        exit();
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<nav class="sidebar">
    <ul>
      <li><a href="home.php">Home</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('dashboard')">Dashboard</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('orders')">Orders</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('users')">User Management</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('events')">Events</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('products')">Products</a></li>
      <li><a href="javascript:void(0)" onclick="loadPage('inventory')">Inventory</a></li>
      <li><a href="logout.php">Log Out</a></li>
    </ul>
  </nav>
    <nav class="content">
    <h1>Edit Product</h1>

    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="edit_products.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>
        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br>
        <label for="quantity">Quantity:</label>
        <input type="text" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required><br>
        <button type="submit" name="edit_product">Update</button>
        <button type="button" onclick="window.location.href='inventory.php';">Cancel</button>
    </form>
    </nav>
</body>
</html>