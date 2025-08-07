<?php  
session_start();
include ('../LogReg/database.php');


$user_name = 'Guest'; 
if (isset($_SESSION['id']) && isset($_SESSION['fname'])) {
    $user_name = $_SESSION['fname'];
    $user_id = $_SESSION['id'];
    $user_sql = "SELECT fname, lname FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_name = $row['fname'] . ' ' . $row['lname'];
        }
        $stmt->close();
    }
}


$sql = "SELECT id, name, price, imagePath, created_at FROM product ORDER BY created_at DESC LIMIT 4"; // Latest 4 products
$result = $conn->query($sql);

$featured_products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => '../admin/' . $row['imagePath'],
            'created_at' => $row['created_at']
        ];
    }
}

$events_sql = "SELECT * FROM esports_events 
               WHERE event_date >= CURDATE() 
               ORDER BY event_date ASC 
               LIMIT 5"; 
$events_result = $conn->query($events_sql);

$upcoming_events = [];
if ($events_result->num_rows > 0) {
    while ($row = $events_result->fetch_assoc()) {
        $upcoming_events[] = [
            'title' => $row['event_title'],
            'date' => $row['event_date'],
            'description' => $row['event_description'],
            'image_url' => $row['image_url']
        ];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZReborn Home Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
       .featured-products {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px 0;
        }

        .product-card {
            border: 1px solid #ddd;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin: 10px;
            text-align: center;
            width: calc(25% - 20px); /* 4 products in a row */
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            object-fit: cover;
        }

        .product-card h3 {
            font-size: 1.2rem;
            margin: 10px 0;
        }

        .product-card p {
            font-size: 1rem;
            color: #555;
        }
        /* Navbar Styles */
        nav {
            display: flex;
            justify-content: center; /* Center the navigation items */
            background-color: #c21212; /* Match the header background */
            padding: 10px 0; /* Vertical padding */
            }

        nav ul {
            list-style: none; /* Remove bullet points */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove default margin */
            display: flex; /* Display items in a row */
            }

        nav ul li {
            padding: 15px 20px; /* Space between items */
            }

        nav ul li a {
            color: white; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make links bold */
            transition: color 0.3s; /* Smooth color transition */
            }

        nav ul li a:hover {
            color: #ff5733; /* Change color on hover */
            }
            /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
            justify-content: center; /* Center the navigation items */
            background-color: #c21212; /* Match the header background */
            padding: 10px 0; /* Vertical padding */
            }

        nav ul {
            list-style: none; /* Remove bullet points */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove default margin */
            display: flex; /* Display items in a row */
            }

        nav ul li {
            padding: 15px 20px; /* Space between items */
            }

        nav ul li a {
            color: white; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make links bold */
            transition: color 0.3s; /* Smooth color transition */
            }

        nav ul li a:hover {
            color: #ff5733; /* Change color on hover */
            }
            /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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

        /* Navbar Styles */
 nav {
            display: flex;
            justify-content: center; /* Center the navigation items */
            background-color: #c21212; /* Match the header background */
            padding: 10px 0; /* Vertical padding */
            }

        nav ul {
            list-style: none; /* Remove bullet points */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove default margin */
            display: flex; /* Display items in a row */
            }

        nav ul li {
            padding: 15px 20px; /* Space between items */
            }

        nav ul li a {
            color: white; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make links bold */
            transition: color 0.3s; /* Smooth color transition */
            }

        nav ul li a:hover {
            color: #ff5733; /* Change color on hover */
            }
            /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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

        h3 {
            color: rgb(88, 33, 33);
            font-weight: bold;
        }
        /* Style for the product card */
        .product-card {
            background-color: rgba(34, 3, 3, 0.14);
            /* Light gray background color */
            border: 1px solid #ddd;
            /* Light border */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
            border-radius: 10px;
            /* Rounded corners */
            padding: 15px;
            /* Space inside the card */
            margin: 10px;
            /* Space between cards */
            text-align: center;
            /* Center-align content */
            width: calc(25% - 20px);
            /* 4 cards per row */
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            /* Smooth transition */
            text-decoration: none;
            /* Removes the underline */
            color: inherit;
            /* Keeps the text color consistent with parent */

        }

        /* Add hover effect for product card */
        .product-card:hover {
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
            /* Stronger shadow on hover */
            transform: translateY(-5px);
            /* Slight lift effect */
        }



        .product-card a:hover h3,
        .product-card a:hover p {
            text-decoration: none !important;
            /* Ensure no underline on hover or active */
            color: rgb(219, 64, 29);
            /* Optional hover color */
        }

        /*bay e lg di ah */
        .product-card a {
            text-decoration: none !important;
            /* Force removal of underline */
            color: inherit !important;
            /* Ensure the link uses the parent color */
        }

        .product-card a:visited {
            color: inherit !important;
            /* Remove visited link color */
        }
        .slider {
            width: 100%;
            max-width: 600px;
            /* Reduced width for a more moderate size */
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #ddd;
            /* Light border */
            
        }

        .slides {
            display: flex;
            transition: transform 0.5s ease;
        }

        .slide {
            min-width: 100%;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: #grey;
        }

        .event-image {
            width: 100%;
            height: 200px;
            /* Moderate image height */
            object-fit: cover;
            border-radius: 30px;
            margin-bottom: 15px;
            /* Remove underline on links inside event slides */
.event-slide-link {
    text-decoration: none; /* Removes underline from links */
    color: inherit; /* Ensures the text takes the parent element's color */
}

.event-slide-link:hover {
    text-decoration: none; /* Ensures no underline appears on hover */
    color: #d9534f; /* Optional: Changes text color on hover */
}
        }
.event-date {
    font-weight: bold; /* Makes the text bold */
    color: #333; /* Sets a dark text color */
    font-size: 1.1rem; /* Adjust font size if needed */
    margin-top: 5px; /* Adds a bit of spacing above */
            
    
    
        }
        .event-description {
             font-weight: bold; /* Makes the text bold */
    color: #555; /* Sets a slightly lighter text color */
    font-size: 1rem; /* Adjust font size if needed */
    line-height: 1.5; /* Improves readability */
    margin-top: 5px; /* Adds a bit of spacing above */
    text-decoration: none; /* Removes underline */
        }
a {
    text-decoration: none; /* Removes underline */
    color: inherit; /* Inherits text color */
}

/* Ensure underline is removed on hover and focus */
a:hover, a:focus {
    text-decoration: none; /* Removes underline on hover or focus */
}

        /* Notification Icon Styles */
        .notification-icon {
            position: relative;
            display: inline-block;
            margin-left: 20px;
            cursor: pointer;
        }

        .notification-icon i {
            font-size: 30px;
            color: #3498db;
        }

        .notification-bubble {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Dropdown for Notifications */
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 35px;
            right: 0;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
            border: 1px solid #ddd;
            border-radius: 5px;
            z-index: 100;
        }

        .notification-dropdown ul {
            list-style-type: none;
            padding: 10px;
            margin: 0;
        }

        .notification-dropdown ul li {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .notification-dropdown ul li:last-child {
            border-bottom: none;
        }

        .notification-dropdown ul li a {
            text-decoration: none;
            color: #333;
        }

        .notification-dropdown ul li:hover {
            background-color: #f1f1f1;
        }

        /* Show the dropdown when clicked */
        .notification-icon:hover .notification-dropdown {
            display: block;
        }

        /* New notification styles */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            animation: fadeIn 0.5s ease-in;
        }

        .notification-item.new {
            background-color: #f0f8ff;
        }

        .notification-item .timestamp {
            font-size: 0.8em;
            color: #666;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
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
        <!-- Hamburger menu -->
        <div class="hamburger-menu">
            <div class="hamburger-icon">&#9776;</div> <!-- Hamburger icon -->
            <div class="dropdown-menu">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="user_orders.php">Your Orders</a></li>
                    <li><a href="../LogReg/logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div id="notification"></div>

    <main>
        <section class="welcome">
            <h2>Featured Products</h2>
        </section>

        <section class="featured-products">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <a href="shop.php?id=<?php echo $product['id']; ?>">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>₱<?php echo number_format($product['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No featured products available.</p>
            <?php endif; ?>
        </section>

        <!-- Replace the current news section with this -->
        <section class="news">
            <h2>News & Upcoming Events</h2>
            <div class="slider">
                <div class="slides">
                    <?php if (!empty($upcoming_events)): ?>
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="slide">
                                <a href="calendar.php" class="event-slide-link">
                                    <?php if ($event['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                                    <?php endif; ?>
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="event-date"><?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                                    <p class="event-description">
                                        <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                                    </p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="slide">
                            <h3>Latest Events</h3>
                            <p>Visit our events page for more info.</p>
                        </div>
                    <?php endif; ?>
                            </div>
                        </div>
        </section>
    </main>






    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const slider = document.querySelector('.slider');
            const slides = document.querySelector('.slides');
            const totalSlides = slides.children.length;
            let currentIndex = 0;

            function updateSlider() {
                slides.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Update dots
                const dots = document.querySelectorAll('.dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                updateSlider();
            }

            // Add navigation dots
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'slider-dots';

            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.className = `dot ${i === 0 ? 'active' : ''}`;
                dot.onclick = () => {
                    currentIndex = i;
                    updateSlider();
                };
                dotsContainer.appendChild(dot);
            }

            slider.appendChild(dotsContainer);

            // Auto-advance slides
            setInterval(nextSlide, 5000);
        });
        
        
            const hamburgerMenu = document.querySelector('.hamburger-menu');
    const hamburgerIcon = document.querySelector('.hamburger-icon');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    hamburgerIcon.addEventListener('click', () => {
        hamburgerMenu.classList.toggle('active');
    });
    </script>
</body>

</html>