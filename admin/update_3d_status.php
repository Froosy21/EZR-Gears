<?php
session_start();
include('../LogReg/database.php');

require '../vendor/autoload.php';
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$order_id = $_POST['order_id'];
$status = $_POST['status'];

// First, get the user's email and order details
$stmt = $conn->prepare("
    SELECT p.email, p.created_at, p.quantity 
    FROM 3d_prod p
    WHERE p.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_data = $result->fetch_assoc();

// Update the status
$update_stmt = $conn->prepare("UPDATE 3dorder_status SET status = ? WHERE order_id = ?");
$update_stmt->bind_param("si", $status, $order_id);

if ($update_stmt->execute()) {
    // Send email notification if status is processing or delivering
    if (in_array($status, ['processing', 'delivering'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.ezr-gears.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'support@ezr-gears.com';
            $mail->Password = 'EZRsupport21';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('support@ezr-gears.com', 'EZR Gears');
            $mail->addAddress($order_data['email']);

            $mail->isHTML(true);
            $mail->Subject = '3D Custom Order Status Update';

            // Format the created date
            $order_date = date("F j, Y", strtotime($order_data['created_at']));

            // Prepare email body based on status
            if ($status === 'processing') {
                $mail->Body = "
                    <h2>Your 3D Custom Order is Being Processed</h2>
                    <p>Dear Valued Customer,</p>
                    <p>We're pleased to inform you that we have started processing your custom 3D order placed on {$order_date}.</p>
                    <p><strong>Order Details:</strong></p>
                    <ul>
                        <li>Order Date: {$order_date}</li>
                        <li>Quantity: {$order_data['quantity']}</li>
                        <li>Current Status: Processing</li>
                    </ul>
                    <p>We will notify you again once your order is ready for delivery. Have a good day!</p>
                    <p>Best regards,<br>EZR Gears Team</p>
                ";
            } else if ($status === 'delivering') {
                $mail->Body = "
                    <h2>Your 3D Custom Order is Out for Delivery</h2>
                    <p>Dear Valued Customer,</p>
                    <p>Great news! Your custom 3D order from {$order_date} is now out for delivery.</p>
                    <p><strong>Order Details:</strong></p>
                    <ul>
                        <li>Order Date: {$order_date}</li>
                        <li>Quantity: {$order_data['quantity']}</li>
                        <li>Current Status: Out for Delivery</li>
                    </ul>
                    <p>You will receive your order soon, please check your sms for the tracking code and visit https://www.jtexpress.ph/trajectoryQuery?flag=1. Thank you for purchasing at EZR Gears!</p>
                    <p>Best regards,<br>EZR Gears Team</p>
                ";
            }

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Status updated and email sent']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Status updated but email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$conn->close();
?>