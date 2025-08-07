<?php
session_start();
include('../LogReg/database.php');

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM users WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "User deleted successfully";
        header('Location: home.php');
    } else {
        echo "Error deleting user: " . $conn->error;
    }
}

$conn->close();
?>