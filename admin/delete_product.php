<?php
session_start();
include('../LogReg/database.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "DELETE FROM product WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Product deleted successfully";
            header('Location: home.php');
        } else {
            echo "Error deleting product: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
