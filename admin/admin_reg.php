<?php  
session_start();
include('../LogReg/database.php');

// Fetch registrations with event details including contact info and social media
$query = "SELECT r.*, e.event_title, e.event_date, r.approval_status, r.attendance_status 
          FROM event_registrations r 
          JOIN esports_events e 
          ON r.event_id = e.id 
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
        'approval_status' => $row['approval_status'] ?? 'pending',
        'attendance_status' => $row['attendance_status'] ?? 'pending'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users</title>
    <link rel="stylesheet" href="home.css">
    <style>
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
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-left: 20px; /* Shift table to the right */
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
        /*sadasdasdasdasdsa*/
          body {
            font-family: Arial, sans-serif;
        }
        
   
        th {
            background-color: rgb(223, 141, 141);
        }
       
        .menu a:hover {
            color: red;
        }

        /* Mini box overlay styles */
        #successBox {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color:rgb(241, 9, 21);
            color: white;
            font-size: 16px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s, visibility 0.5s;
        }

        #successBox.visible {
            opacity: 1;
            visibility: visible;
        }
        .attendance-dropdown {
    width: 120px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.attendance-dropdown:hover {
    border-color: #c21212;
}

/* Style for different status colors */
.attendance-dropdown[data-status="attended"] {
    background-color: #e6ffe6;
    border-color: #4CAF50;
}

.attendance-dropdown[data-status="not_attended"] {
    background-color: #ffe6e6;
    border-color: #FF5733;
}

/* Add status indicator dot */
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}

.status-pending { background-color: #FFA500; }
.status-attended { background-color: #4CAF50; }
.status-not_attended { background-color: #FF5733; }
.approval-dropdown {
    width: 140px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: #fff8dc;
}

.status-denied {
    color: #c21212;
    font-weight: bold;
}

/* Style for different approval status colors */
.approval-dropdown[data-status="approved"] {
    background-color: #e6ffe6;
    border-color: #4CAF50;
}

.approval-dropdown[data-status="denied"] {
    background-color: #ffe6e6;
    border-color: #FF5733;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="img/ezrlogo.png" alt="Admin Logo">
        </div>
        <nav class="menu">
            <a href="orders.php">Orders</a>
            <hr>
            <a href="users.php">User Management</a>
            <hr>
            <a href="admin_events.php">Events</a>
            <hr>
            <hr>
            <a href="admin_reg.php">Registered user events</a>
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
        <h1>Registered Users for Events</h1>

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
        <th>Attendance</th> <!-- New column -->
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
                            <td><?php echo htmlspecialchars($user['discord_tag']); ?></td> <!-- Display Discord tag -->
                           <td>
    <?php if ($user['approval_status'] === 'pending'): ?>
        <select 
            class="approval-dropdown" 
            onchange="updateApproval(this, <?php echo htmlspecialchars($user['user_id']); ?>)"
            style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
            <option value="pending" selected>Pending Approval</option>
            <option value="approved">Approve</option>
            <option value="denied">Deny</option>
        </select>
    <?php elseif ($user['approval_status'] === 'approved'): ?>
        <select 
            class="attendance-dropdown" 
            onchange="updateAttendance(this, <?php echo htmlspecialchars($user['user_id']); ?>)"
            style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
            <option value="pending" <?php echo ($user['attendance_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>
                Pending
            </option>
            <option value="attended" <?php echo ($user['attendance_status'] ?? '') === 'attended' ? 'selected' : ''; ?>>
                Attended
            </option>
            <option value="not_attended" <?php echo ($user['attendance_status'] ?? '') === 'not_attended' ? 'selected' : ''; ?>>
                Not Attended
            </option>
        </select>
    <?php else: ?>
        <span class="status-denied">Registration Denied</span>
    <?php endif; ?>
</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

    <script>
        function loadPage(page) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', page + '.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('content').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
    
    <script>
    
    function updateApproval(selectElement, userId) {
    const status = selectElement.value;
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('approval_status', status);

    // Show loading state
    selectElement.disabled = true;
    
    fetch('update_approval.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the page to show updated controls
            location.reload();
        } else {
            alert('Failed to update approval status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating approval status');
    })
    .finally(() => {
        selectElement.disabled = false;
    });
}
// Update the query to fetch attendance_status
function updateAttendance(selectElement, userId) {
    const status = selectElement.value;
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', status);

    // Show loading state
    selectElement.disabled = true;
    
    fetch('update_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update dropdown styling
            selectElement.dataset.status = status;
            
            // Show success message
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

    // Show the message
    setTimeout(() => {
        successBox.classList.add('visible');
    }, 100);

    // Hide and remove the message
    setTimeout(() => {
        successBox.classList.remove('visible');
        setTimeout(() => {
            successBox.remove();
        }, 500);
    }, 3000);
}

// Update the query in your PHP section to include attendance_status
<?php
$query = "SELECT r.*, e.event_title, e.event_date, r.attendance_status 
          FROM event_registrations r 
          JOIN esports_events e 
          ON r.event_id = e.id 
          ORDER BY e.event_date, r.first_name";
?>
</script>
</body>
</html>