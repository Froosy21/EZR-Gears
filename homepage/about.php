<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - EZReborn</title>
    <link rel="stylesheet" href="style.css">
    <style>
           .slider {
            width: 100%;
            overflow: hidden;
        }
        .slides {
            display: flex;
            transition: transform 0.5s ease;
        }
        .slide {
            min-width: 100%;
            flex: 0 0 auto;
        }

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
            margin: 1;
            padding: 2px;
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

        /* About Section */
        .about {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
            background-color: #fff;
        }

        .about-content {
            max-width: 600px;
            margin-right: 20px;
        }

        .about h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .about p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .about-image img {
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Social Media Section */
        .social-media {
            text-align: center;
            padding: 50px 0;
            background-color: #222;
            color: white;
        }

        .social-media h2 {
            margin-bottom: 20px;
        }

        .social-media a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-media a:hover {
            color: #ff5733;
        }

       
     h3 {
         color:rgb(88, 33, 33); /* Replace this with your preferred color (e.g., green) */
         font-weight: bold; /* Optional: Ensure the font is bold */
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

        <!-- Hamburger menu -->
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

    <main>
        <section class="about">
            <div class="about-content">
                <h1>About EZReborn E-Sports</h1>
                <p>Welcome to EZReborn E-Sports, your ultimate destination for high-quality jerseys and esports clothing. We are passionate about providing gamers with the best tools and gear to enhance their gaming experience.</p>
                <p>At EZReborn, we understand the importance of performance and reliability in clothes. That's why we carefully select and offer a curated collection of products ranging from gaming peripherals to esports jerseys, ensuring that every gamer finds what they need to succeed.</p>
                <p>Our mission is to support the gaming community by providing top-notch customer service and delivering products that meet the highest standards of quality and innovation.</p>
                <p>Thank you for choosing EZReborn E-Sports. Game on!</p>
        </section>

        <!-- Social Media Section -->
        <section class="social-media">
            <h2>Follow Us on Social Media</h2>
            <a href="https://web.facebook.com/EzReborn" target="_blank">Facebook</a>
            <a href="https://www.instagram.com/ezrebornesportsph/" target="_blank">Instagram</a>
        </section>
    </main>

    <script>
        // Hamburger menu toggle
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const hamburgerIcon = document.querySelector('.hamburger-icon');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        hamburgerIcon.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
        });
    </script>
</body>
</html>