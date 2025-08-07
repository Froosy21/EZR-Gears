<?php  
session_start();
include('../LogReg/database.php');



// Default to the current month and year if not set in the URL
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Handling navigation for next/prev month
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'prev') {
        if ($current_month == 1) {
            $current_month = 12;
            $current_year--;
        } else {
            $current_month--;
        }
    } elseif ($_GET['action'] == 'next') {
        if ($current_month == 12) {
            $current_month = 1;
            $current_year++;
        } else {
            $current_month++;
        }
    }
}


// SQL query to fetch events for the selected month and year
$query = "SELECT * FROM esports_events WHERE MONTH(event_date) = $current_month AND YEAR(event_date) = $current_year ORDER BY event_date";
$result = mysqli_query($conn, $query);

$events_by_date = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = date('Y-m-d', strtotime($row['event_date'])); 
    $events_by_date[$date][] = [
        'id' => $row['id'],
        'title' => $row['event_title'],
        'description' => $row['event_description'],
        'hover_text' => $row['hover_text'],
        'image_url' => $row['image_url'],
    ];
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$first_day_of_month = strtotime("$current_year-$current_month-01");
$day_of_week = date('w', $first_day_of_month);





if (isset($_SESSION['error'])) {
    echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin: 20px;
        }
        .day {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 100px;
            position: relative;
            cursor: pointer;
        }
        .day:hover {
            background-color: #f0f0f0;
        }
        .event-cell {
            position: relative;
        }

        /* Sliding Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            right: -500px;
            width: 444px;
            height: 100%;
            background-color: #fff;
            border-left: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            overflow-y: auto;
            transition: right 0.3s ease;
            z-index: 1000;
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar img {
            max-width: 400px;
            height: 150px;
        }

        .sidebar .content {
            margin-top: 10px;
        }

        .sidebar a {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #aa1345;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }

        .sidebar a:hover {
            background-color: #0056b3;
        }

        /* Close button */
        .close-btn {
            position: absolute;
            top: 15px;
            left: 10px;
            background-color:rgb(223, 5, 5);
            color: #fff;
            border: 2px;
            border-radius: 30%;
            padding: 6px;
            cursor: pointer;
        }

        #navigation {
            text-align: center;
            margin-bottom: 20px;
        }
        #navigation button {
            margin: 0 10px;
            padding: 10px 15px;
            background-color: #aa1345;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #navigation span {
            font-size: 1.2em;
        }
        header {
            background-color: #c21212;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Ensure body content is pushed below the fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on your header height */
        }

        header img.logo {
            width: 80px;
            height: auto;
            vertical-align: middle;
        }

        header h1 {
            display: inline;
            margin: 0;
            padding: 0;
            vertical-align: middle;
        }

        nav {
            display: flex;
            justify-content: center;
            background-color: #c21212;
            padding: 10px 0;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        nav ul li {
            padding: 15px 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #ff5733;
        }

        .hamburger-menu {
            position: absolute;
            right: 20px; /* Adjust this value to your liking */
            top: 50%;
            transform: translateY(-50%);
        }

        .hamburger-icon {
            font-size: 24px;
            cursor: pointer;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .dropdown-menu ul li {
            padding: 10px 20px;
        }

        .dropdown-menu ul li a {
            text-decoration: none;
            color: black;
            display: block;
        }

        .dropdown-menu ul li:hover {
            background-color: #f1f1f1;
        }

        /* Show dropdown when active */
        .hamburger-menu.active .dropdown-menu {
            display: block;
        }

        .h3 {
            color: rgb(88, 33, 33);
            font-weight: bold;
        }
         .p {
            color: rgb(88, 33, 33);
            font-weight: bold;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 1000px;
            width: 100%;
            overflow-x: auto;
        }

        .form-container h1 {
            color: #d18d8d;
            margin-bottom: 20px;
            text-align: center;
        }
        
         li a svg {
    width: 16px;
    height: 16px;
  }
    </style>
</head>
<body>
    <header>
        <img src="img/EzR Logo.png" alt="Logo" class="logo">
        <nav>
            <ul>
                <li>
  <a href="home.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M8.543 2.232a.75.75 0 0 0-1.085 0l-5.25 5.5A.75.75 0 0 0 2.75 9H4v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 1 1 2 0v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1V9h1.25a.75.75 0 0 0 .543-1.268l-5.25-5.5Z" />
</svg>

    Home
  </a>
</li>

                <li><a href="shop.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M4.5 7c.681 0 1.3-.273 1.75-.715C6.7 6.727 7.319 7 8 7s1.3-.273 1.75-.715A2.5 2.5 0 1 0 11.5 2h-7a2.5 2.5 0 0 0 0 5ZM6.25 8.097A3.986 3.986 0 0 1 4.5 8.5c-.53 0-1.037-.103-1.5-.29v4.29h-.25a.75.75 0 0 0 0 1.5h.5a.754.754 0 0 0 .138-.013A.5.5 0 0 0 3.5 14H6a.5.5 0 0 0 .5-.5v-3A.5.5 0 0 1 7 10h2a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h2.5a.5.5 0 0 0 .112-.013c.045.009.09.013.138.013h.5a.75.75 0 1 0 0-1.5H13V8.21c-.463.187-.97.29-1.5.29a3.986 3.986 0 0 1-1.75-.403A3.986 3.986 0 0 1 8 8.5a3.986 3.986 0 0 1-1.75-.403Z" />
</svg>
Shop</a></li>
                <li><a href="calendar.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a1 1 0 0 0-1 1v4.5a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-7Z" clip-rule="evenodd" />
</svg>

Events</a></li>
                <li><a href="about.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0ZM9 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM6.75 8a.75.75 0 0 0 0 1.5h.75v1.75a.75.75 0 0 0 1.5 0v-2.5A.75.75 0 0 0 8.25 8h-1.5Z" clip-rule="evenodd" />
</svg>

About Us</a></li>
                <li>
  <a href="cart.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
  <path d="M1.75 1.002a.75.75 0 1 0 0 1.5h1.835l1.24 5.113A3.752 3.752 0 0 0 2 11.25c0 .414.336.75.75.75h10.5a.75.75 0 0 0 0-1.5H3.628A2.25 2.25 0 0 1 5.75 9h6.5a.75.75 0 0 0 .73-.578l.846-3.595a.75.75 0 0 0-.578-.906 44.118 44.118 0 0 0-7.996-.91l-.348-1.436a.75.75 0 0 0-.73-.573H1.75ZM5 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM13 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" />
</svg>

    Cart
  </a>
</li>

            </ul>
        </nav>
        <div class="hamburger-menu">
            <div class="hamburger-icon">&#9776;</div>
            <div class="dropdown-menu">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="user_orders.php">Your Orders</a></li>
                    <li><a href="../LogReg/logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div id="content">
        <h1>Event Calendar</h1>
        <div id="navigation">
            <form method="GET" style="display: inline;">
                <input type="hidden" name="month" value="<?php echo $current_month; ?>">
                <input type="hidden" name="year" value="<?php echo $current_year; ?>">
                <button type="submit" name="action" value="prev">&lt; Previous</button>
            </form>
            <span><?php echo date('F Y', strtotime("$current_year-$current_month-01")); ?></span>
            <form method="GET" style="display: inline;">
                <input type="hidden" name="month" value="<?php echo $current_month; ?>">
                <input type="hidden" name="year" value="<?php echo $current_year; ?>">
                <button type="submit" name="action" value="next">Next &gt;</button>
            </form>
        </div>
        <div id="calendar">
            <?php
            // Display empty cells for days before the 1st of the month
            for ($i = 0; $i < $day_of_week; $i++) {
                echo '<div class="day"></div>';
            }

            // Display the days of the month
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                echo '<div class="day" onclick="openSidebar(\'' . $date . '\')">';
                echo "<strong>$day</strong><br>";

                if (isset($events_by_date[$date])) {
                    foreach ($events_by_date[$date] as $event) {
                        echo '<div class="event-cell">';
                        echo htmlspecialchars($event['title']);
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Sidebar for displaying event details and registration form -->
    <div id="sidebar" class="sidebar">
        <!-- Close button -->
        <button class="close-btn" onclick="closeSidebar()">Close</button>

        <div class="content">
            <h2 id="event-title"></h2>
            <img id="event-image" src="" alt="Event Image">
            <p id="event-description"></p>
            <div id="event-register-form">
                <!-- Registration form will be dynamically added here -->
            </div>
        </div>
    </div>

    <script>
        
        function openSidebar(date) {
            var events = <?php echo json_encode($events_by_date); ?>;
            if (events[date]) {
                var event = events[date][0]; // Assuming one event per day
                document.getElementById('event-title').innerText = event.title;
                document.getElementById('event-image').src = event.image_url || 'default-image.jpg';
                document.getElementById('event-description').innerText = event.description;
                document.getElementById('event-register-form').innerHTML = `
                    <h3>Register for ${event.title}</h3>
                    <form method="POST" action="eventreg.php">
                        <input type="hidden" name="event_id" value="${event.id}">
                        
                        <label for="first_name">First Name:</label>
                        <input type="text" name="first_name" required><br><br>
                
                        <label for="last_name">Last Name:</label>
                        <input type="text" name="last_name" required><br><br>
                
                        <label for="user_email">Email:</label>
                        <input type="email" name="user_email" required><br><br>
                
                        <label for="contact_no">Contact Number:</label>
                        <input type="text" name="contact_no" required><br><br>
                
                        <label for="social_media">Social Media URL:</label>
                        <input type="url" name="social_media"><br><br>
                
                        <button type="submit">Register</button>
                    </form>
                `;
                document.getElementById('sidebar').classList.add('open');
            }
        }

        // Function to close the sidebar
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
        }

        // Hamburger menu toggle
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const hamburgerIcon = document.querySelector('.hamburger-icon');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        hamburgerIcon.addEventListener('click', () => {
            // Toggle the 'active' class on the dropdown menu
            dropdownMenu.classList.toggle('active');

            // Toggle the 'active' class on the hamburger menu to animate icon
            hamburgerMenu.classList.toggle('active');
        });
    </script>
</body>
</html>
