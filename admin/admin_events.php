<?php  
session_start();
include('../LogReg/database.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
   
    $event_date = $_POST['event_date'];
    $event_title = $_POST['event_title'];
    $event_description = $_POST['event_description'];
    $hover_text = $_POST['hover_text'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $image_name = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO esports_events (event_date, event_title, event_description, hover_text, image_url, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->bind_param("sssss", $event_date, $event_title, $event_description, $hover_text, $target_file);

            if ($stmt->execute()) {
                $success = true;
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Failed to upload image.";
        }
    } else {
        echo "Please upload a valid image file.";
    }
}

// Fetch events
$query = "SELECT * FROM esports_events ORDER BY event_date";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link rel="stylesheet" href="home.css">

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-left: 300px;
            margin-right: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea, input[type="file"], input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: rgb(211, 20, 20);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: rgb(238, 49, 16);
        }
        table {
         width: 80%; /* Table width remains reasonable */
          margin-left: 30px; /* Push the table to the right */
          margin-right: 20px; /* Add space between the table and the right edge */
           border-collapse: collapse; /* Collapse borders */
           border: 1px solid black; /* Add a border to the table */
                }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: rgb(223, 141, 141);
        }
        img {
            max-width: 100%;
            height: auto;
            display: 5px;
            margin: 2px;
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

    <form action="admin_events.php" method="POST" enctype="multipart/form-data">
        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" required>

        <label for="event_title">Event Title:</label>
        <input type="text" name="event_title" required>

        <label for="event_description">Event Description:</label>
        <textarea name="event_description" required></textarea>

        <label for="hover_text">Hover Text:</label>
        <input type="text" name="hover_text" required>

        <label for="image">Event Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit" name="add_event">Add Event</button>
    </form>

    <table>
        <tr>
            <th>Date</th>
            <th>Title</th>
            <th>Description</th>
            <th>Image</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
            <td><?php echo htmlspecialchars($row['event_description']); ?></td>
            <td>
                <?php if (!empty($row['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Event Image">
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Mini box overlay -->
    <div id="successBox" class="hidden">Event Added Successfully!</div>

    <script>
        function showSuccessBox() {
            const successBox = document.getElementById('successBox');
            successBox.classList.add('visible');

            // Hide the success box after 3 seconds
            setTimeout(() => {
                successBox.classList.remove('visible');
            }, 3000);
        }

        // Trigger the overlay when the PHP logic successfully adds the event
        <?php if (isset($success) && $success): ?>
        showSuccessBox();
        <?php endif; ?>
    </script>
</body>
</html>
