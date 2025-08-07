<?php
session_start();
include ('../LogReg/database.php');

function saveNotification($message, $conn) {
    $stmt = $conn->prepare("INSERT INTO notifications (message, created_at, is_read, type) VALUES (?, CURRENT_TIMESTAMP, 0, 'product')");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();
}

// Define the directory for images
$target_dir = "prodimage/";
$image_name = basename($_FILES["product_image"]["name"]);
$target_file = $target_dir . $image_name;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Validate if file is an image
$check = getimagesize($_FILES["product_image"]["tmp_name"]);
if ($check !== false) {
    $uploadOk = 1;
} else {
    echo "File is not an image.";
    $uploadOk = 0;
}

// Append unique ID if the file exists
if (file_exists($target_file)) {
    $target_file = $target_dir . uniqid() . '.' . $imageFileType;
}

// Validate file size
if ($_FILES["product_image"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow specific file formats
if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory if needed
    }

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars($image_name) . " has been uploaded.";

        // Sanitize and validate inputs
        $product_name = htmlspecialchars($_POST['product_name']);
        $product_price = filter_var($_POST['product_price'], FILTER_VALIDATE_FLOAT);
        $product_quantity = filter_var($_POST['product_quantity'], FILTER_VALIDATE_INT);
        $product_description = htmlspecialchars($_POST['product_description']);
        $product_weight = filter_var($_POST['product_weight'], FILTER_VALIDATE_FLOAT);

        if (!$product_name || !$product_price || !$product_quantity || !$product_weight) {
            die("Missing or invalid input. Please fill all required fields.");
        }

        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO product (name, price, quantity, imagePath, description, weight, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sdissd", $product_name, $product_price, $product_quantity, $target_file, $product_description, $product_weight);

        // Execute and check for errors
        if ($stmt->execute()) {
            echo "New record created successfully!";
            header('Location: products.php');
            exit();
        } else {
            echo "Error inserting record into database: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
