<?php
session_start();
include('../LogReg/database.php');

// First, verify if the email exists in users table
function verifyUserEmail($conn, $email) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    
    $stmt->close();
    return true;
}

// Define the directory for ZIP files
$target_dir = "../admin/3dimage/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Validate email first
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    die("Invalid email format.");
}

// Check if email exists in users table
if (!verifyUserEmail($conn, $email)) {
    die("Email is not registered.");
}

// Generate a unique customer ID for naming the ZIP file
$customerId = "customer" . rand(100000, 999999);

// Handle ZIP file upload
if (isset($_FILES['product_zip']) && $_FILES['product_zip']['error'] === UPLOAD_ERR_OK) {
    $zipFileType = strtolower(pathinfo($_FILES['product_zip']['name'], PATHINFO_EXTENSION));
    $zipFileName = $customerId . "." . $zipFileType;
    $zipFilePath = $target_dir . $zipFileName;
    
    // Validate the file
    if ($zipFileType !== 'zip') {
        die("Invalid file format. Only ZIP files are allowed.");
    }
    
    if ($_FILES['product_zip']["size"] > 50000000) {
        die("File is too large.");
    }
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['product_zip']["tmp_name"], $zipFilePath)) {
        die("Failed to upload ZIP file.");
    }
} else {
    die("Missing ZIP file.");
}

// Fixed values
$fixed_price = 750;
$fixed_weight = 250;


$product_quantity = filter_var($_POST['product_quantity'], FILTER_VALIDATE_INT);
$product_description = htmlspecialchars($_POST['product_description']);

if (!$product_quantity || !$product_description) {
    die("Missing or invalid input. Please fill all required fields.");
}


$total_price = $fixed_price * $product_quantity;


// Prepare SQL query for 3d_prod table
$stmt = $conn->prepare(
    "INSERT INTO 3d_prod (email, price, quantity, zipFilePath, description, weight) 
    VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "sdissd", 
    $email, 
    $total_price, 
    $product_quantity, 
    $zipFilePath, 
    $product_description, 
    $fixed_weight
);

// Execute and check for errors
if ($stmt->execute()) {
    $_SESSION['success_message'] = "Product uploaded successfully!";
    header('Location: 3d_upload.php');
    exit();
} else {
    die("Error inserting record into database: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>