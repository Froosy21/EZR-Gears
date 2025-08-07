<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../LogReg/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Print all POST data
    var_dump($_POST);
    
    // Sanitize and get form data
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $user_email = htmlspecialchars(trim($_POST['user_email']));
    $contact_no = htmlspecialchars(trim($_POST['contact_no']));
    $social_media = htmlspecialchars(trim($_POST['social_media'] ?? ''));
    $discord_tag = htmlspecialchars(trim($_POST['discord_tag'] ?? ''));

    // Debug: Print processed variables
    echo "Event ID: " . $event_id . "<br>";
    echo "First Name: " . $first_name . "<br>";
    echo "Last Name: " . $last_name . "<br>";
    echo "Email: " . $user_email . "<br>";
    echo "Contact: " . $contact_no . "<br>";
    echo "Social Media: " . $social_media . "<br>";
    echo "Discord Tag: " . $discord_tag . "<br>";

    // Validate event ID
    if ($event_id <= 0) {
        $_SESSION['error_message'] = "Invalid event ID.";
        header('Location: calendar.php');
        exit();
    }

    // Validate email
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header('Location: calendar.php');
        exit();
    }

    try {
        // Check if user exists and get user_id
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Email is not registered. Please register first.";
            header('Location: calendar.php');
            exit();
        }

        $user_row = $result->fetch_assoc();
        $user_id = $user_row['id'];

        // Check if already registered
        $check_stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_email = ?");
        if (!$check_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $check_stmt->bind_param("is", $event_id, $user_email);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['error_message'] = "You are already registered for this event.";
            header('Location: calendar.php');
            exit();
        }

        // Insert registration
        $insert_stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id, user_email, first_name, last_name, contact_no, social_media, discord_tag) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $insert_stmt->bind_param("iissssss", $event_id, $user_id, $user_email, $first_name, $last_name, $contact_no, $social_media, $discord_tag);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Execute failed: " . $insert_stmt->error);
        }

        // Regenerate session ID for security
        session_regenerate_id();
        $_SESSION['success_message'] = "Successfully registered for the event!";
        header('Location: calendar.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred while processing your registration: " . $e->getMessage();
        header('Location: calendar.php');
        exit();
    }
}
