<?php
session_start();
include('../LogReg/database.php');

require '../vendor/autoload.php';
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $approval_status = $_POST['approval_status'];
    
    // First, get the user's email and event details
    $stmt = $conn->prepare("
        SELECT r.user_email, r.first_name, r.last_name, e.event_title, e.event_date 
        FROM event_registrations r
        JOIN esports_events e ON r.event_id = e.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Update the approval status
    $update_stmt = $conn->prepare("UPDATE event_registrations SET approval_status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $approval_status, $user_id);
    
    if ($update_stmt->execute()) {
        // Send email notification
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
            $mail->addAddress($user_data['user_email'], $user_data['first_name'] . ' ' . $user_data['last_name']);

            $mail->isHTML(true);
            $mail->Subject = 'Event Registration Status Update';

            // Format the event date
            $event_date = date("F j, Y", strtotime($user_data['event_date']));

            // Prepare email body based on approval status
            if ($approval_status === 'approved') {
                $mail->Body = "
                    <h2>Event Registration Approved!</h2>
                    <p>Dear {$user_data['first_name']},</p>
                    <p>Your registration for the event \"{$user_data['event_title']}\" on {$event_date} has been approved!</p>
                    <p>We look forward to seeing you at the event.</p>
                    <p>Best regards,<br>EZR Gears Team</p>
                ";
            } else if ($approval_status === 'denied') {
                $mail->Body = "
                    <h2>Event Registration Status Update</h2>
                    <p>Dear {$user_data['first_name']},</p>
                    <p>Unfortunately, your registration for the event \"{$user_data['event_title']}\" on {$event_date} has been denied.</p>
                    <p>If you have any questions, please contact our support team.</p>
                    <p>Best regards,<br>EZR Gears Team</p>
                ";
            }

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Status updated and email sent']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>