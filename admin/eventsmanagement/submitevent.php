<?php
session_start();
include('../../LogReg/database.php'); // Corrected path

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventName = $_POST['eventName'];
    $eventDate = $_POST['eventDate'];
    $eventDescription = $_POST['eventDescription'];

    $targetDir = "../../uploads/"; // Ensure this directory exists
    $targetFile = $targetDir . basename($_FILES["eventPhoto"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if upload directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($_FILES["eventPhoto"]["tmp_name"], $targetFile)) {
        $eventPhoto = $targetFile;

        $sql = "INSERT INTO events (eventName, eventDate, eventDescription, eventPhoto) VALUES ('$eventName', '$eventDate', '$eventDescription', '$eventPhoto')";

        if ($conn->query($sql) === TRUE) {
            echo "New event created successfully";
            header('Location: ../../admin/home.php');
            exit; 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
