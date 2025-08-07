<?php
session_start();
include('../LogReg/database.php');

// Only fetch approved registrations
$query = "SELECT r.*, e.event_title, e.event_date, r.attendance_status 
          FROM event_registrations r 
          JOIN esports_events e 
          ON r.event_id = e.id 
          WHERE r.approval_status = 'approved'
          ORDER BY e.event_date, r.first_name";
$result = mysqli_query($conn, $query);

// Initialize array to organize events and users
$events = [];

// Organize data by event
while ($row = mysqli_fetch_assoc($result)) {
    $event_date = date("F j, Y", strtotime($row['event_date']));
    $events[$row['event_title'] . " (" . $event_date . ")"][] = [
        'user_id'     => $row['id'], 
        'first_name'  => $row['first_name'],
        'last_name'   => $row['last_name'],
        'user_email'  => $row['user_email'],
        'contact_no'  => $row['contact_no'],
        'social_media'=> $row['social_media'],
        'discord_tag' => $row['discord_tag'],
        'attendance_status' => $row['attendance_status'] ?? 'pending'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="home.css">
    <style>
        /* Keep your existing styles and add: */
        .attendance-dropdown {
            width: 120px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            cursor: pointer;
        }

        .attendance-dropdown[data-status="attended"] {
            background-color: #e6ffe6;
            border-color: #4CAF50;
        }

        .attendance-dropdown[data-status="not_attended"] {
            background-color: #ffe6e6;
            border-color: #FF5733;
        }
          
        body {
            background-color: #f9f9f9; /* Light background */
            color: #333; /* Text color */
            margin: 0;
            font-family: Arial, sans-serif;
        }

        #content {
            margin-left: 270px; /* Space for the sidebar */
            padding: 20px; /* Padding for the content */
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 400px;
            margin-left: 100px; /* Shift table to the right */
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #34495e; /* Darker header background */
            color: white; /* White text for headers */
        }

        .remove-btn {
            background-color: #c21212; /* Red remove button */
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background-color: #a00; /* Darker red on hover */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="img/ezrlogo.png" alt="Admin Logo">
        </div>
        <nav class="menu">
            <nav class="menu">
            <a href="admin_notifications.php">Notifications</a>
            <hr>
            <a href="orders.php">Orders</a>
            <hr>
            <a href="users.php">User Management</a>
            <hr>
            <a href="admin_events.php">Events</a>
            <hr>
            <a href="reg_approval.php">Registered user events</a>
            <hr>
            <a href="attendreg.php">Event Attendance Checker</a>
            <hr>
            <a href="products.php">Products</a>
            <hr>
            <a href="admin_3d.php">3D Customs</a>
            <hr>
            <a href="inventory.php">Inventory</a>
            <hr>
            <a href="login.php">Logout</a>
        </nav>
    </div>

    <div id="content">
        <h1>Attendance Management</h1>
        <a href="admin_reg.php" class="btn btn-primary">Go to Registration Approval</a>

        <?php foreach ($events as $event_title => $users): ?>
            <h2><?php echo htmlspecialchars($event_title); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Contact No.</th>
                        <th>Social Media</th>
                        <th>Discord Tag</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($user['social_media']); ?>" target="_blank">Social Media</a></td>
                            <td><?php echo htmlspecialchars($user['discord_tag']); ?></td>
                            <td>
                                <select 
                                    class="attendance-dropdown" 
                                    onchange="updateAttendance(this, <?php echo htmlspecialchars($user['user_id']); ?>)"
                                    data-status="<?php echo htmlspecialchars($user['attendance_status']); ?>">
                                    <option value="pending" <?php echo $user['attendance_status'] === 'pending' ? 'selected' : ''; ?>>
                                        Pending
                                    </option>
                                    <option value="attended" <?php echo $user['attendance_status'] === 'attended' ? 'selected' : ''; ?>>
                                        Attended
                                    </option>
                                    <option value="not_attended" <?php echo $user['attendance_status'] === 'not_attended' ? 'selected' : ''; ?>>
                                        Not Attended
                                    </option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

    <script>
    function updateAttendance(selectElement, userId) {
        const status = selectElement.value;
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('status', status);

        selectElement.disabled = true;
        
        fetch('update_attendance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectElement.dataset.status = status;
                showSuccessMessage('Attendance status updated!');
            } else {
                alert('Failed to update attendance status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating attendance status');
        })
        .finally(() => {
            selectElement.disabled = false;
        });
    }

    function showSuccessMessage(message) {
        const successBox = document.createElement('div');
        successBox.id = 'successBox';
        successBox.textContent = message;
        document.body.appendChild(successBox);

        setTimeout(() => {
            successBox.classList.add('visible');
        }, 100);

        setTimeout(() => {
            successBox.classList.remove('visible');
            setTimeout(() => {
                successBox.remove();
            }, 500);
        }, 3000);
    }
    </script>
</body>
</html>
