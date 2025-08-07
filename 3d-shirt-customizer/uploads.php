<?php
session_start();
include('../LogReg/database.php');

header('Content-Type: application/json');

$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/EzRebornProgram/3d-shirt-customizer/custom_img/";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_snapshots') {
    $target_dir = "custom_img/";

    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        if (mkdir($target_dir, 0777, true)) {
            $response['message'] .= " Directory created successfully. ";
        } else {
            $response['errors'][] = "Failed to create directory.";
        }
    }

    // Get the snapshots data
    if (isset($_POST['snapshots'])) {
        $snapshots = json_decode($_POST['snapshots'], true);

        if ($snapshots) {
            foreach ($snapshots as $view => $base64Data) {
                // Extract the actual base64 image data
                $base64_parts = explode(',', $base64Data);
                if (count($base64_parts) > 1) {
                    $base64_decode = base64_decode($base64_parts[1]);

                    // Generate unique filename
                    $filename = uniqid() . '_' . $view . '.png';
                    $filepath = $target_dir . $filename;

                    // Save the file
                    if (file_put_contents($filepath, $base64_decode) !== false) {
                        $saved_paths[$view] = $filepath;

                        // Save to database
                        $stmt = $conn->prepare("INSERT INTO model_snapshots (user_email, view, file_path) VALUES (?, ?, ?)");
                        $user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'guest@example.com';
                        $stmt->bind_param("sss", $user_email, $view, $filepath);

                        if ($stmt->execute()) {
                            $response['success'][] = "Successfully saved $view view";
                        } else {
                            $response['errors'][] = "Database error for $view: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $response['errors'][] = "Failed to save $view view at path: $filepath";
                    }
                } else {
                    $response['errors'][] = "Invalid base64 data for $view view";
                }
            }

            // Store the file paths in session
            $_SESSION['snapshot_paths'] = $saved_paths;

            $response['status'] = 'success';
            $response['message'] = 'All snapshots saved successfully';
            $response['paths'] = $saved_paths;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid snapshot data format';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No snapshot data received';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method or action';
}

echo json_encode($response);
$conn->close();
?>
