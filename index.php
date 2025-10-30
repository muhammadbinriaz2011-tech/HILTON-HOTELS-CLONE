<?php
// Hilton Hotels Booking Platform - Single File Implementation
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'ukrfhh29eellf');
define('DB_PASS', 'jua2ursxz7gb');
define('DB_NAME', 'dbaognfqzuem2o');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'book') {
        // Process booking
        $name = $_POST['name'];
        $email = $_POST['email'];
        $hotel_id = (int)$_POST['hotel_id'];
        $room_id = (int)$_POST['room_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $total_price = (float)$_POST['total_price'];
        
        // Insert booking into database
        $sql = "INSERT INTO bookings (user_name, user_email, hotel_id, room_id, check_in, check_out, total_price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiisd", $name, $email, $hotel_id, $room_id, $check_in, $check_out, $total_price);
        
        if ($stmt->execute()) {
            $_SESSION['booking_id'] = $stmt->insert_id;
            $_SESSION['booking_details'] = [
                'name' => $name,
                'email' => $email,
                'hotel_id' => $hotel_id,
                'room_id' => $room_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_price' => $total_price
            ];
            header("Location: " . $_SERVER['PHP_SELF'] . "?page=confirmation");
            exit();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'contact') {
        // Process contact form
        $contact_name = $_POST['contact_name'];
        $contact_email = $_POST['contact_email'];
        $contact_subject = $_POST['contact_subject'];
        $contact_message = $_POST['contact_message'];
        
        // In a real system, you would send an email here
        // For now, we'll just set a success message
        $_SESSION['contact_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=contact");
        exit();
    }
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Fetch hotel details if needed
$hotel = null;
if (isset($_GET['hotel_id']) && !in_array($page, ['home', 'destinations', 'offers', 'contact'])) {
    $hotel_id = (int)$_GET['hotel_id'];
    $sql = "SELECT * FROM hotels WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();
}

// Fetch room details if needed
$room = null;
if (isset($_GET['room_id']) && !in_array($page, ['home', 'destinations', 'offers', 'contact'])) {
    $room_id = (int)$_GET['room_id'];
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
}

// Calculate nights if dates provided
$nights = 1;
if (isset($_GET['check_in']) && isset($_GET['check_out'])) {
    $check_in_date = new DateTime($_GET['check_in']);
    $check_out_date = new DateTime($_GET['check_out']);
    $interval = $check_in_date->diff($check_out_date);
    $nights = $interval->days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilton Hotels & Resorts</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        /* Header Styles */
        .main-header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000;
        }

        .logo h1 span {
            color: #d32f2f;
        }

        .main-nav ul {
            display: flex;
            gap: 25px;
        }

        .main-nav a {
            font-weight: 500;
            transition: color 0.3s;
        }

        .main-nav a:hover {
            color: #d32f2f;
        }

        .main-nav .active {
            color: #d32f2f;
            font-weight: 700;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            height: 500px;
            display: flex;
            align-items: center;
            color: #fff;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .hero h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Search Form */
        .search-form {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .search-form h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group > div {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .search-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .search-btn:hover {
            background: #b71c1c;
        }

        /* Featured Hotels */
        .section-title {
            text-align: center;
            margin: 50px 0 30px;
            font-size: 2rem;
            color: #333;
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #d32f2f;
        }

        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .hotel-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .hotel-image {
            height: 200px;
            overflow: hidden;
        }

        .hotel-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .hotel-card:hover .hotel-image img {
            transform: scale(1.05);
        }

        .hotel-info {
            padding: 20px;
        }

        .hotel-info h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .hotel-location i {
            margin-right: 8px;
            color: #d32f2f;
        }

        .hotel-rating {
            color: #d32f2f;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .hotel-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #d32f2f;
        }

        .view-btn {
            background: #f5f5f5;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .view-btn:hover {
            background: #e0e0e0;
        }

        /* Filters */
        .filters {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filters h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .filter-group {
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-option {
            display: flex;
            align-items: center;
        }

        .filter-option input {
            margin-right: 8px;
        }

        /* Hotel Details Page */
        .hotel-details {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }

        .hotel-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            height: 400px;
        }

        .main-image {
            grid-row: 1 / span 2;
            overflow: hidden;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail {
            overflow: hidden;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s;
        }

        .thumbnail:hover img {
            opacity: 0.8;
        }

        .hotel-content {
            padding: 30px;
        }

        .hotel-content h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .hotel-content .hotel-location {
            margin-bottom: 15px;
        }

        .hotel-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .amenity {
            background: #f0f0f0;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .rooms-container {
            margin-top: 40px;
        }

        .room-card {
            display: flex;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .room-image {
            width: 200px;
            height: 150px;
            overflow: hidden;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-info {
            padding: 20px;
            flex: 1;
        }

        .room-info h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .room-details {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }

        .room-detail {
            display: flex;
            flex-direction: column;
        }

        .room-detail span:first-child {
            color: #666;
            font-size: 0.9rem;
        }

        .room-detail span:last-child {
            font-weight: 600;
        }

        .book-btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            align-self: flex-end;
        }

        .book-btn:hover {
            background: #b71c1c;
        }

        /* Booking Form */
        .booking-form {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 0 auto 50px;
        }

        .booking-form h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row > div {
            flex: 1;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .booking-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #d32f2f;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        /* Confirmation Page */
        .confirmation {
            text-align: center;
            padding: 50px 20px;
        }

        .confirmation i {
            font-size: 5rem;
            color: #4caf50;
            margin-bottom: 20px;
        }

        .confirmation h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        .confirmation p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .booking-details {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        /* Destinations Page */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .destination-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .destination-card:hover {
            transform: translateY(-5px);
        }

        .destination-image {
            height: 200px;
            overflow: hidden;
        }

        .destination-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .destination-info {
            padding: 20px;
        }

        .destination-info h3 {
            margin-bottom: 10px;
        }

        .destination-info p {
            color: #666;
            margin-bottom: 15px;
        }

        /* Offers Page */
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .offer-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .offer-badge {
            position: absolute;
            top: 20px;
            right: -30px;
            background: #d32f2f;
            color: white;
            padding: 5px 30px;
            transform: rotate(45deg);
            font-weight: 600;
        }

        .offer-card h3 {
            margin-bottom: 15px;
            color: #d32f2f;
        }

        .offer-card p {
            margin-bottom: 20px;
            color: #666;
        }

        .offer-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
        }

        .discounted-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #d32f2f;
        }

        /* Contact Page */
        .contact-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin: 50px 0;
        }

        .contact-info {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .contact-info h3 {
            margin-bottom: 20px;
            color: #d32f2f;
        }

        .contact-detail {
            display: flex;
            margin-bottom: 20px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #d32f2f;
            font-size: 1.2rem;
        }

        .contact-text h4 {
            margin-bottom: 5px;
        }

        .contact-form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .contact-form h3 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Footer */
        .main-footer {
            background: #222;
            color: #fff;
            padding: 50px 0 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-logo h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .footer-logo h3 span {
            color: #d32f2f;
        }

        .footer-links h4,
        .footer-contact h4 {
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .footer-links ul {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links a,
        .footer-contact a {
            color: #aaa;
            transition: color 0.3s;
        }

        .footer-links a:hover,
        .footer-contact a:hover {
            color: #d32f2f;
        }

        .footer-contact p {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header .container {
                flex-direction: column;
                gap: 15px;
            }
            
            .main-nav ul {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
            
            .form-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group > div {
                min-width: 100%;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hotel-gallery {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .room-card {
                flex-direction: column;
            }
            
            .room-image {
                width: 100%;
                height: 200px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .contact-section {
                grid-template-columns: 1fr;
            }
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .mt-30 {
            margin-top: 30px;
        }

        .mb-30 {
            margin-bottom: 30px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <h1>Hilton<span>Hotels</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="?page=home" class="<?php echo ($page === 'home') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="?page=destinations" class="<?php echo ($page === 'destinations') ? 'active' : ''; ?>">Destinations</a></li>
                    <li><a href="?page=offers" class="<?php echo ($page === 'offers') ? 'active' : ''; ?>">Offers</a></li>
                    <li><a href="?page=contact" class="<?php echo ($page === 'contact') ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <?php if ($page === 'home'): ?>
    <section class="hero">
        <div class="hero-content">
            <h2>Find Your Perfect Stay</h2>
            <p>Discover luxury accommodations at Hilton Hotels worldwide</p>
        </div>
    </section>

    <section class="search-section">
        <div class="container">
            <form action="?page=search" method="GET" class="search-form">
                <input type="hidden" name="page" value="search">
                <h3>Search Hotels</h3>
                <div class="form-group">
                    <div>
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" placeholder="City, hotel name, or attraction" required>
                    </div>
                    <div>
                        <label for="check-in">Check-in Date</label>
                        <input type="date" id="check-in" name="check_in" required>
                    </div>
                    <div>
                        <label for="check-out">Check-out Date</label>
                        <input type="date" id="check-out" name="check_out" required>
                    </div>
                </div>
                <button type="submit" class="search-btn">Search Hotels</button>
            </form>
        </div>
    </section>

    <section class="featured-hotels">
        <div class="container">
            <h2 class="section-title">Featured Hotels</h2>
            <div class="hotels-grid">
                <?php
                // Fetch featured hotels
                $sql = "SELECT * FROM hotels ORDER BY rating DESC LIMIT 4";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $rating = str_repeat('<i class="fas fa-star"></i>', floor($row['rating'])) . 
                                 str_repeat('<i class="far fa-star"></i>', 5 - floor($row['rating']));
                        echo '
                        <div class="hotel-card">
                            <div class="hotel-image">
                                <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="' . $row['name'] . '">
                            </div>
                            <div class="hotel-info">
                                <h3>' . $row['name'] . '</h3>
                                <div class="hotel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ' . $row['location'] . '
                                </div>
                                <div class="hotel-rating">
                                    ' . $rating . ' (' . $row['rating'] . ')
                                </div>
                                <p>' . substr($row['description'], 0, 100) . '...</p>
                                <div class="hotel-price">
                                    <div class="price">$' . number_format($row['price_per_night'], 2) . ' <span>/night</span></div>
                                    <a href="?page=hotel&hotel_id=' . $row['id'] . '" class="view-btn">View Details</a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo "<p>No hotels found.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <section class="top-rated">
        <div class="container">
            <h2 class="section-title">Top Rated Stays</h2>
            <div class="hotels-grid">
                <?php
                // Fetch top-rated hotels
                $sql = "SELECT * FROM hotels WHERE rating >= 4.7 ORDER BY rating DESC LIMIT 4";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $rating = str_repeat('<i class="fas fa-star"></i>', floor($row['rating'])) . 
                                 str_repeat('<i class="far fa-star"></i>', 5 - floor($row['rating']));
                        echo '
                        <div class="hotel-card">
                            <div class="hotel-image">
                                <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="' . $row['name'] . '">
                            </div>
                            <div class="hotel-info">
                                <h3>' . $row['name'] . '</h3>
                                <div class="hotel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ' . $row['location'] . '
                                </div>
                                <div class="hotel-rating">
                                    ' . $rating . ' (' . $row['rating'] . ')
                                </div>
                                <p>' . substr($row['description'], 0, 100) . '...</p>
                                <div class="hotel-price">
                                    <div class="price">$' . number_format($row['price_per_night'], 2) . ' <span>/night</span></div>
                                    <a href="?page=hotel&hotel_id=' . $row['id'] . '" class="view-btn">View Details</a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo "<p>No top-rated hotels found.</p>";
                }
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'search'): ?>
    <section class="search-results">
        <div class="container">
            <div class="search-summary">
                <h1>Search Results</h1>
                <?php
                // Get search parameters
                $destination = isset($_GET['destination']) ? $_GET['destination'] : '';
                $check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
                $check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
                
                // Build query
                $sql = "SELECT * FROM hotels WHERE 1=1";
                $params = [];
                $types = "";
                
                if (!empty($destination)) {
                    $sql .= " AND (name LIKE ? OR location LIKE ?)";
                    $params[] = "%$destination%";
                    $params[] = "%$destination%";
                    $types .= "ss";
                }
                
                // Apply filters if they exist
                $price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
                $price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
                $rating = isset($_GET['rating']) ? $_GET['rating'] : '';
                
                if (!empty($price_min)) {
                    $sql .= " AND price_per_night >= ?";
                    $params[] = $price_min;
                    $types .= "s";
                }
                
                if (!empty($price_max)) {
                    $sql .= " AND price_per_night <= ?";
                    $params[] = $price_max;
                    $types .= "s";
                }
                
                if (!empty($rating)) {
                    $sql .= " AND rating >= ?";
                    $params[] = $rating;
                    $types .= "s";
                }
                
                // Sorting
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
                switch($sort) {
                    case 'price_low':
                        $sql .= " ORDER BY price_per_night ASC";
                        break;
                    case 'price_high':
                        $sql .= " ORDER BY price_per_night DESC";
                        break;
                    case 'rating':
                        $sql .= " ORDER BY rating DESC";
                        break;
                    default:
                        $sql .= " ORDER BY id ASC";
                }
                
                // Prepare and execute statement
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <p>Found <?php echo $result->num_rows; ?> hotels for your stay</p>
            </div>
            
            <div class="filters">
                <h3>Filter Results</h3>
                <form method="GET" id="filter-form">
                    <input type="hidden" name="page" value="search">
                    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    
                    <div class="filter-group">
                        <label>Price Range</label>
                        <div class="form-row">
                            <div>
                                <input type="number" name="price_min" placeholder="Min price" value="<?php echo htmlspecialchars($price_min); ?>">
                            </div>
                            <div>
                                <input type="number" name="price_max" placeholder="Max price" value="<?php echo htmlspecialchars($price_max); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Minimum Rating</label>
                        <select name="rating">
                            <option value="">Any Rating</option>
                            <option value="4" <?php echo ($rating == '4') ? 'selected' : ''; ?>>4+ Stars</option>
                            <option value="4.5" <?php echo ($rating == '4.5') ? 'selected' : ''; ?>>4.5+ Stars</option>
                            <option value="4.7" <?php echo ($rating == '4.7') ? 'selected' : ''; ?>>4.7+ Stars</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>>Default</option>
                            <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo ($sort == 'rating') ? 'selected' : ''; ?>>Best Rated</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="search-btn">Apply Filters</button>
                </form>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
            <div class="hotels-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $rating_stars = str_repeat('<i class="fas fa-star"></i>', floor($row['rating'])) . 
                                   str_repeat('<i class="far fa-star"></i>', 5 - floor($row['rating']));
                    ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="<?php echo $row['name']; ?>">
                        </div>
                        <div class="hotel-info">
                            <h3><?php echo $row['name']; ?></h3>
                            <div class="hotel-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $row['location']; ?>
                            </div>
                            <div class="hotel-rating">
                                <?php echo $rating_stars; ?> (<?php echo $row['rating']; ?>)
                            </div>
                            <p><?php echo substr($row['description'], 0, 100); ?>...</p>
                            <div class="hotel-price">
                                <div class="price">$<?php echo number_format($row['price_per_night'], 2); ?> <span>/night</span></div>
                                <a href="?page=hotel&hotel_id=<?php echo $row['id']; ?>&check_in=<?php echo $check_in; ?>&check_out=<?php echo $check_out; ?>" class="view-btn">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <div class="text-center mt-30">
                    <h3>No hotels found matching your criteria.</h3>
                    <p>Try adjusting your search filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'hotel' && $hotel): ?>
    <section class="hotel-details">
        <div class="hotel-gallery">
            <div class="main-image">
                <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80" alt="<?php echo $hotel['name']; ?>">
            </div>
            <div class="thumbnail">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Hotel room">
            </div>
            <div class="thumbnail">
                <img src="https://images.unsplash.com/photo-1584132967334-10e028bd69f7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Hotel lobby">
            </div>
        </div>
        
        <div class="hotel-content">
            <h2><?php echo $hotel['name']; ?></h2>
            <div class="hotel-location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo $hotel['location']; ?>
            </div>
            <div class="hotel-rating">
                <?php 
                $rating_stars = str_repeat('<i class="fas fa-star"></i>', floor($hotel['rating'])) . 
                               str_repeat('<i class="far fa-star"></i>', 5 - floor($hotel['rating']));
                echo $rating_stars; 
                ?> (<?php echo $hotel['rating']; ?>)
            </div>
            <p><?php echo $hotel['description']; ?></p>
            
            <div class="hotel-amenities">
                <?php 
                $amenities = explode(',', $hotel['amenities']);
                foreach ($amenities as $amenity): ?>
                    <div class="amenity"><?php echo trim($amenity); ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="rooms-container">
                <h3>Available Rooms</h3>
                <?php
                // Fetch rooms for this hotel
                $sql = "SELECT * FROM rooms WHERE hotel_id = ? AND available = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $hotel['id']);
                $stmt->execute();
                $rooms_result = $stmt->get_result();
                
                if ($rooms_result->num_rows > 0):
                    while($room = $rooms_result->fetch_assoc()):
                        $total_price = $room['price'] * $nights;
                ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="<?php echo $room['room_type']; ?>">
                    </div>
                    <div class="room-info">
                        <h3><?php echo $room['room_type']; ?></h3>
                        <p>Comfortable <?php echo $room['room_type']; ?> for up to <?php echo $room['capacity']; ?> guests</p>
                        
                        <div class="room-details">
                            <div class="room-detail">
                                <span>Capacity</span>
                                <span><?php echo $room['capacity']; ?> guests</span>
                            </div>
                            <div class="room-detail">
                                <span>Price per night</span>
                                <span>$<?php echo number_format($room['price'], 2); ?></span>
                            </div>
                            <?php if ($nights > 1): ?>
                            <div class="room-detail">
                                <span>Total for <?php echo $nights; ?> nights</span>
                                <span>$<?php echo number_format($total_price, 2); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <form action="?page=booking" method="GET">
                            <input type="hidden" name="page" value="booking">
                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                            <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                            <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                            <button type="submit" class="book-btn">Book Now</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <p>No rooms available for this hotel.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'booking' && $hotel && $room): ?>
    <section class="booking-section">
        <div class="container">
            <div class="booking-form">
                <h2>Complete Your Booking</h2>
                
                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    <div class="summary-item">
                        <span>Hotel</span>
                        <span><?php echo $hotel['name']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Room Type</span>
                        <span><?php echo $room['room_type']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Check-in</span>
                        <span><?php echo date('M j, Y', strtotime($check_in)); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Check-out</span>
                        <span><?php echo date('M j, Y', strtotime($check_out)); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Nights</span>
                        <span><?php echo $nights; ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Total Price</span>
                        <span>$<?php echo number_format($total_price, 2); ?></span>
                    </div>
                </div>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                    <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                    <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                    
                    <div class="form-row">
                        <div>
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="special_requests">Special Requests</label>
                                <input type="text" id="special_requests" name="special_requests">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="search-btn">Confirm Booking</button>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'confirmation' && isset($_SESSION['booking_details'])): ?>
    <section class="confirmation">
        <div class="container">
            <i class="fas fa-check-circle"></i>
            <h2>Booking Confirmed!</h2>
            <p>Thank you for booking with Hilton Hotels. A confirmation email has been sent to <?php echo htmlspecialchars($_SESSION['booking_details']['email']); ?>.</p>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <span>Booking ID</span>
                    <span>#<?php echo $_SESSION['booking_id']; ?></span>
                </div>
                <div class="detail-row">
                    <span>Guest Name</span>
                    <span><?php echo htmlspecialchars($_SESSION['booking_details']['name']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Hotel</span>
                    <span>
                        <?php 
                        $sql = "SELECT name FROM hotels WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_SESSION['booking_details']['hotel_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $hotel = $result->fetch_assoc();
                        echo $hotel['name'];
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Room Type</span>
                    <span>
                        <?php 
                        $sql = "SELECT room_type FROM rooms WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_SESSION['booking_details']['room_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $room = $result->fetch_assoc();
                        echo $room['room_type'];
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Check-in</span>
                    <span><?php echo date('M j, Y', strtotime($_SESSION['booking_details']['check_in'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Check-out</span>
                    <span><?php echo date('M j, Y', strtotime($_SESSION['booking_details']['check_out'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Total Price</span>
                    <span>$<?php echo number_format($_SESSION['booking_details']['total_price'], 2); ?></span>
                </div>
            </div>
            
            <div class="mt-30">
                <a href="?page=home" class="search-btn">Back to Home</a>
            </div>
        </div>
    </section>
    <?php 
    // Clear session after displaying confirmation
    unset($_SESSION['booking_details']);
    unset($_SESSION['booking_id']);
    endif; ?>

    <?php if ($page === 'destinations'): ?>
    <section class="destinations-page">
        <div class="container">
            <h1 class="section-title">Explore Our Destinations</h1>
            <p class="text-center mb-30">Discover luxury stays in the world's most exciting locations</p>
            
            <div class="destinations-grid">
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="New York">
                    </div>
                    <div class="destination-info">
                        <h3>New York City</h3>
                        <p>Experience the energy of the Big Apple with our luxury hotels in Manhattan.</p>
                        <a href="?page=search&destination=New York" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1516082681594-73765d6a5c71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Paris">
                    </div>
                    <div class="destination-info">
                        <h3>Paris</h3>
                        <p>Stay in the heart of the City of Light with our elegant Parisian properties.</p>
                        <a href="?page=search&destination=Paris" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1520257328262-9a2e2d6e8c89?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Tokyo">
                    </div>
                    <div class="destination-info">
                        <h3>Tokyo</h3>
                        <p>Experience Japanese hospitality at our modern Tokyo hotels.</p>
                        <a href="?page=search&destination=Tokyo" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1512453958291-75a7f8d6c862?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="London">
                    </div>
                    <div class="destination-info">
                        <h3>London</h3>
                        <p>Discover British charm and luxury at our London properties.</p>
                        <a href="?page=search&destination=London" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1516082681594-73765d6a5c71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Rome">
                    </div>
                    <div class="destination-info">
                        <h3>Rome</h3>
                        <p>Stay near ancient ruins and experience Italian luxury.</p>
                        <a href="?page=search&destination=Rome" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://images.unsplash.com/photo-1512453958291-75a7f8d6c862?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Dubai">
                    </div>
                    <div class="destination-info">
                        <h3>Dubai</h3>
                        <p>Experience modern luxury in the heart of the desert.</p>
                        <a href="?page=search&destination=Dubai" class="view-btn">Explore Hotels</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'offers'): ?>
    <section class="offers-page">
        <div class="container">
            <h1 class="section-title">Special Offers</h1>
            <p class="text-center mb-30">Exclusive deals and packages for your next stay</p>
            
            <div class="offers-grid">
                <div class="offer-card">
                    <div class="offer-badge">20% OFF</div>
                    <h3>Summer Escape</h3>
                    <p>Book 3 nights and get 20% off your entire stay at any Hilton property.</p>
                    <div class="offer-price">
                        <div class="original-price">$1,200</div>
                        <div class="discounted-price">$960</div>
                    </div>
                </div>
                
                <div class="offer-card">
                    <div class="offer-badge">FREE NIGHT</div>
                    <h3>Stay Longer, Save More</h3>
                    <p>Stay 4 nights and get your 5th night free at participating hotels.</p>
                    <div class="offer-price">
                        <div class="original-price">$1,500</div>
                        <div class="discounted-price">$1,200</div>
                    </div>
                </div>
                
                <div class="offer-card">
                    <div class="offer-badge">15% OFF</div>
                    <h3>Advance Purchase</h3>
                    <p>Book 21 days in advance and save 15% on your stay.</p>
                    <div class="offer-price">
                        <div class="original-price">$800</div>
                        <div class="discounted-price">$680</div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-30">
                <a href="?page=search" class="search-btn">Browse All Hotels</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($page === 'contact'): ?>
    <section class="contact-page">
        <div class="container">
            <h1 class="section-title">Contact Us</h1>
            <p class="text-center mb-30">Have questions? Our team is here to help you plan your perfect stay.</p>
            
            <?php if (isset($_SESSION['contact_success']) && $_SESSION['contact_success']): ?>
                <div class="alert alert-success">
                    Thank you for your message! We'll get back to you soon.
                </div>
                <?php unset($_SESSION['contact_success']); ?>
            <?php endif; ?>
            
            <div class="contact-section">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone</h4>
                            <p>1-800-HILTONS (1-800-445-8667)</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p>info@hiltonhotels.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Headquarters</h4>
                            <p>7930 Jones Branch Drive<br>McLean, VA 22102<br>United States</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="contact">
                        
                        <div class="form-group">
                            <label for="contact_name">Full Name</label>
                            <input type="text" id="contact_name" name="contact_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Email Address</label>
                            <input type="email" id="contact_email" name="contact_email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_subject">Subject</label>
                            <input type="text" id="contact_subject" name="contact_subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_message">Message</label>
                            <textarea id="contact_message" name="contact_message" required></textarea>
                        </div>
                        
                        <button type="submit" class="search-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Hilton<span>Hotels</span></h3>
                    <p>Luxury stays for every traveler</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="?page=home">Home</a></li>
                        <li><a href="?page=destinations">Destinations</a></li>
                        <li><a href="?page=offers">Offers</a></li>
                        <li><a href="?page=contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-phone"></i> 1-800-HILTONS</p>
                    <p><i class="fas fa-envelope"></i> info@hiltonhotels.com</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Hilton Hotels & Resorts. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const nav = document.querySelector('.main-nav');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    nav.classList.toggle('active');
                });
            }
            
            // Date picker initialization
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const checkIn = document.getElementById('check-in');
                    const checkOut = document.getElementById('check-out');
                    
                    if (checkIn.value && checkOut.value) {
                        if (new Date(checkOut.value) <= new Date(checkIn.value)) {
                            alert('Check-out date must be after check-in date');
                            checkOut.value = '';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
