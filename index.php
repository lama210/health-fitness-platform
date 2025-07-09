<?php
session_start();
require_once("connection.php");

// Check if connection was successful
try {
    // Fetch feedback data with receiver names using PDO
    $feedback_query = "SELECT 
                        f.*, 
                        u.name as user_name,
                        CASE 
                            WHEN f.receiver_type = 'coach' THEN c.user_id
                            WHEN f.receiver_type = 'nutrition' THEN n.user_id
                        END as receiver_user_id,
                        CASE 
                            WHEN f.receiver_type = 'coach' THEN uc.name
                            WHEN f.receiver_type = 'nutrition' THEN un.name
                        END as receiver_name
                      FROM feedback f 
                      JOIN user u ON f.user_id = u.id
                      LEFT JOIN coach c ON f.receiver_type = 'coach' AND f.receiver_id = c.id
                      LEFT JOIN nutrition n ON f.receiver_type = 'nutrition' AND f.receiver_id = n.id
                      LEFT JOIN user uc ON c.user_id = uc.id
                      LEFT JOIN user un ON n.user_id = un.id
                      ORDER BY f.created_at DESC";
    
    $stmt = $pdo->query($feedback_query);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error fetching feedback: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCore | Nutrition & Fitness Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <!-- Elfsight WhatsApp Chat | Untitled WhatsApp Chat -->
<script src="https://static.elfsight.com/platform/platform.js" async></script>
<div class="elfsight-app-fef3ed2c-7463-44c3-ab0a-025e7d748f71" data-elfsight-app-lazy></div>
    <style>
   .feedback-slideshow {
    position: relative;
    max-width: 1000px;
    margin: 0 auto;
    padding: 3rem 0;
    overflow: hidden;
}

.feedback-track {
    display: flex;
    transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.feedback-slide {
    min-width: 100%;
    padding: 0 2rem;
    box-sizing: border-box;
}

.feedback-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    text-align: center;
    transform: scale(0.95);
    opacity: 0.6;
    transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.feedback-card.active {
    transform: scale(1);
    opacity: 1;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
}

/* Add subtle gradient background for active card */
.feedback-card.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
}

.feedback-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    box-shadow: 0 10px 20px rgba(0, 201, 167, 0.3);
    transition: all 0.4s ease;
    position: relative;
    z-index: 1;
}

/* Add subtle pattern to avatar background */
.feedback-avatar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.2) 0%, transparent 50%);
    border-radius: 50%;
}

.active .feedback-avatar {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 30px rgba(0, 201, 167, 0.4);
}

.feedback-rating {
    color: var(--accent);
    font-size: 1.5rem;
    margin: 1rem 0;
    position: relative;
    display: inline-block;
}

/* Add numeric rating after stars */
.feedback-rating::after {
    content: attr(data-rating);
    font-size: 1rem;
    margin-left: 8px;
    color: #777;
    vertical-align: middle;
}

.feedback-comment {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    position: relative;
    padding: 0 2rem;
    font-style: italic;
    color: #555;
}

.feedback-comment:before,
.feedback-comment:after {
    content: '"';
    font-size: 4rem;
    color: rgba(0, 201, 167, 0.15);
    position: absolute;
    font-family: Georgia, serif;
    line-height: 1;
    transition: all 0.3s ease;
}

.feedback-comment:before {
    top: -20px;
    left: -10px;
}

.feedback-comment:after {
    bottom: -40px;
    right: -10px;
}

.active .feedback-comment:before,
.active .feedback-comment:after {
    color: rgba(0, 201, 167, 0.3);
}

.feedback-user {
    margin-top: 1.5rem;
}

.feedback-user h5 {
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
    color: var(--dark);
    position: relative;
    display: inline-block;
}

/* Add decorative element under name */
.feedback-user h5::after {
    content: '';
    display: block;
    width: 40px;
    height: 2px;
    background: var(--primary);
    margin: 8px auto 0;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.active .feedback-user h5::after {
    width: 60px;
    opacity: 1;
}

.feedback-user p {
    color: #777;
    font-size: 1rem;
    margin: 0.3rem 0;
}

.feedback-user .receiver-type {
    display: inline-block;
    background: rgba(0, 201, 167, 0.1);
    color: var(--primary);
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.feedback-user .receiver-name {
    font-style: italic;
    color: #666;
    margin-top: 10px;
    font-size: 0.95em;
    position: relative;
    padding-top: 10px;
}

.feedback-user .receiver-name::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 1px;
    background: rgba(0, 0, 0, 0.1);
}

.feedback-user .receiver-name::after {
    content: "★";
    color: var(--accent);
    margin-right: 5px;
}

/* Navigation arrows */
.slideshow-nav button {
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.slideshow-nav button:hover {
    background: var(--primary);
    color: white;
    box-shadow: 0 5px 20px rgba(0, 201, 167, 0.3);
}

#prevBtn {
    left: 0;
}

#nextBtn {
    right: 0;
}

/* Navigation Arrows */
.slideshow-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    z-index: 10;
}

.slideshow-nav button {
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--primary);
}

.slideshow-nav button:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

/* Indicators */
.slideshow-indicators {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    gap: 10px;
}

.slideshow-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(0, 201, 167, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
}

.slideshow-indicator.active {
    background: var(--primary);
    transform: scale(1.2);
}

/* Responsive */
@media (max-width: 768px) {
    .feedback-card {
        padding: 2rem 1.5rem;
    }
    
    .feedback-comment {
        font-size: 1rem;
        padding: 0 1rem;
    }
    
    .slideshow-nav button {
        width: 40px;
        height: 40px;
    }
}
        :root {
            --primary: #00C9A7;
            --secondary: #845EC2;
            --dark: #1E1E2A;
            --light: #F7F7FF;
            --accent: #FF6F91;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Modern Navigation Bar */
        .navbar-modern {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.08);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .navbar-brand img {
            height: 50px;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand img:hover {
            transform: none !important;
            cursor: default;
        }

        .nav-item {
            margin: 0 0.5rem;
            position: relative;
        }
        
        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
            background: rgba(0, 201, 167, 0.1);
        }
        
        .nav-link.active {
            color: var(--primary) !important;
            font-weight: 600;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: var(--primary);
            color: white !important;
        }
        
        .auth-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        
        .btn-primary:hover {
            background: #00b495;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 201, 167, 0.3);
        }
        
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 5rem 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 3rem;
        }
        
        /* Carousel */
        .carousel-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
        }
        
        .carousel-item img {
            height: 60vh;
            object-fit: cover;
            width: 100%;
        }
        
        .carousel-control-prev, .carousel-control-next {
            width: 5%;
        }
        
        /* Content Sections */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Location Section */
        .location-section {
            background: linear-gradient(135deg, rgba(0,201,167,0.05), rgba(132,94,194,0.05));
        }

        .location-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
        }

        .map-container {
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .map-container:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .contact-info h5 {
            color: var(--dark);
            font-size: 1.1rem;
        }

        .contact-info p {
            color: var(--dark);
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        /* About Section */
        .about-section {
            background-color: var(--light);
        }

        .icon-circle {
            background-color: rgba(0, 201, 167, 0.1);
        }

        .stat-item {
            flex: 1;
        }

        .stat-number {
            color: var(--primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--dark);
            opacity: 0.8;
        }

        .avatar-group {
            display: flex;
        }

        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
            margin-left: -15px;
            object-fit: cover;
        }

        .avatar-more {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: -15px;
            border: 3px solid white;
            font-weight: bold;
        }

        /* FAQ Section */
        .accordion-button {
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            color: var(--primary);
            background-color: white;
        }

        .accordion-body {
            padding: 0 1.5rem 1.5rem 3.5rem;
        }

        .faq-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(245, 245, 255, 0.9));
            background-size: cover;
        }

        /* Newsletter */
        .newsletter {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .newsletter::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="1" fill="white"/></svg>') center/contain;
            opacity: 0.05;
            z-index: 0;
        }

        .newsletter .form-control {
            height: 56px;
            padding: 0 1.5rem;
        }

        .newsletter .btn {
            height: 56px;
            transition: all 0.3s ease;
        }

        .newsletter .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Footer Styles */
        footer {
            background-color: var(--dark);
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="1" fill="white"/></svg>') center/contain;
            opacity: 0.03;
            z-index: 0;
        }

        footer .container {
            position: relative;
            z-index: 1;
        }

        footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        footer a {
            text-decoration: none;
            transition: all 0.3s ease;
        }

        footer a:hover {
            color: var(--primary) !important;
            padding-left: 5px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        footer .input-group {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        footer .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
        }

        footer .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        footer hr {
            opacity: 0.1;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .navbar-collapse {
                background: white;
                padding: 1rem;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                margin-top: 1rem;
            }
            
            .carousel-item img {
                height: 40vh;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .stat-item {
                border-bottom: 1px solid rgba(0,0,0,0.1);
                padding-bottom: 1rem;
            }
            
            .stat-item:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }
            
            .newsletter {
                text-align: center;
            }
            
            .newsletter .form-control,
            .newsletter .btn {
                width: 100%;
            }
            
            footer .col-lg-3:last-child {
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- Modern Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container">
        <a class="navbar-brand" href="javascript:void(0)" style="pointer-events: none; cursor: default;">
            <img src="images/NutriC.png" alt="NutriCore Logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown">
                        Services
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="classes.php">Classes</a></li>
                        <li><a class="dropdown-item" href="nutrtionMealsHome.php">Nutrition Meals</a></li>
                        <li><a class="dropdown-item" href="bookNutritionist.php">Diet Consulting</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="shop.php">Shop</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="feedback.php">Feedback</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="team.php">Our Team</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="#our-story">About us</a>
                </li>
            </ul>
            
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    // Get the email from session
                    $user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

                    // Extract the part before the @ symbol
                    $username = explode('@', $user_email)[0];
                    ?>
                    <div class="dropdown">
                        <a class="btn btn-outline dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($username) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                          
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.html" class="btn btn-primary ms-2">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Rest of your HTML content remains exactly the same -->
<!-- ... -->

</body>
</html>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-4">Transform Your Health Journey</h1>
        <p class="lead mb-5">Personalized nutrition and fitness plans tailored to your unique goals</p>
    </div>
</section>

<div class="container my-5">
    <!-- Carousel Section -->
    <div class="carousel-container">
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="3"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/fit.jpg" class="d-block w-100" alt="Nutrition Plans">
                </div>
                <div class="carousel-item">
                    <img src="images/sport.jpg" class="d-block w-100" alt="Fitness Training">
                </div>
                <div class="carousel-item">
                    <img src="images/det.jpg" class="d-block w-100" alt="Expert Coaches">
                </div>
                <div class="carousel-item">
                    <img src="images/diet.jpg" class="d-block w-100" alt="Community Support">
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="row">
        <div class="col-lg-6">
            <div class="content-card">
                <h2 class="section-title">Welcome to NutriCore</h2>
                <p class="lead">
                    Welcome to NutriCore, where wellness meets strength! Our professionally created 
                    workouts and nutrition programs are designed to help you succeed, whether you're 
                    just starting out or pushing yourself to new limits. Because being your strongest 
                    self is a journey we build together rather than a destination, join a community that 
                    celebrates every rep, meal, and milestone. Come on in, unleash your potential, and 
                    let's make every drop of sweat count!
                </p>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="content-card">
                <h2 class="mb-4">Our Services</h2>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start">
                        <div class="me-3 text-primary">
                            <i class="fas fa-dumbbell fa-2x"></i>
                        </div>
                        <div>
                            <h5>Custom Workout Plans</h5>
                            <p>Tailored exercise programs designed for your fitness level and goals.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <div class="me-3 text-primary">
                            <i class="fas fa-utensils fa-2x"></i>
                        </div>
                        <div>
                            <h5>Personalized Nutrition</h5>
                            <p>Meal plans and dietary guidance based on your preferences and needs.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <div class="me-3 text-primary">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <h5>Group Classes</h5>
                            <p>Interactive sessions with our expert coaches and community support.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- Feedback Slideshow HTML -->
<section class="feedback-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Client Testimonials</h2>
                <p class="lead">Hear what people say about us</p>
                <div class="divider mx-auto" style="width: 80px; height: 4px; background: var(--primary);"></div>
            </div>
        </div>
        
     <div class="feedback-slideshow">
    <div class="feedback-track" id="feedbackTrack">
        <?php foreach($feedbacks as $index => $feedback): ?>
        <div class="feedback-slide">
            <div class="feedback-card <?= $index === 0 ? 'active' : '' ?>">
                <div class="feedback-avatar">
                    <?= strtoupper(substr($feedback['user_name'], 0, 1)) ?>
                </div>
                
                <div class="feedback-rating">
                    <?= str_repeat('★', $feedback['rating']) . str_repeat('☆', 5 - $feedback['rating']) ?>
                </div>
                
                <div class="feedback-comment">
                    <?= nl2br(htmlspecialchars($feedback['comment'])) ?>
                </div>
                
                <div class="feedback-user">
                    <h5><?= htmlspecialchars($feedback['user_name']) ?></h5>
                    <p><?= ucfirst(htmlspecialchars($feedback['receiver_type'])) ?></p>
                    <?php if(!empty($feedback['receiver_name'])): ?>
                        <p class="receiver-name">About <?= htmlspecialchars($feedback['receiver_name']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="slideshow-nav">
        <button id="prevBtn"><i class="fas fa-chevron-left"></i></button>
        <button id="nextBtn"><i class="fas fa-chevron-right"></i></button>
    </div>
    
    <div class="slideshow-indicators" id="indicators">
        <?php foreach($feedbacks as $index => $feedback): ?>
        <div class="slideshow-indicator <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></div>
        <?php endforeach; ?>
    </div>
</div>
</section>
<!-- Google Maps Location Section -->
<section class="location-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="location-card p-4 rounded-3 shadow-sm bg-white">
                    <h2 class="mb-4"><i class="fas fa-map-marker-alt text-primary me-2"></i> Find Us in Zahle</h2>
                    <div class="contact-info mb-4">
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">NutriCore Zahle</h5>
                                <p class="mb-0">Main Street<br>Zahle, Lebanon</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Opening Hours</h5>
                                <p class="mb-0">Monday-saturday: <br>
                                                8:00 AM - 11:00 PM<br>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-3 text-primary">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Contact</h5>
                                <p class="mb-0">+961 8 123 456<br>zahle.nutricore@gmail.com</p>
                            </div>
                        </div>
                    </div>
                    <a href="https://maps.google.com?q=NutriCore+Zahle+Main+Street+Zahle+Lebanon" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-directions me-2"></i> Get Directions
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="map-container rounded-3 overflow-hidden shadow-sm">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3310.041574477086!2d35.90478731521292!3d33.84977308063915!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x151f5f1b6e0984e5%3A0x3e9440c3cf0a8f0e!2sZahle%2C+Lebanon!5e0!3m2!1sen!2sus!4v1715100000000!5m2!1sen!2sus&markers=color:red%7C33.849773,35.904787" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="our-story" class="about-section py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">About Us</h2>
                <div class="divider mx-auto" style="width: 80px; height: 4px; background: var(--primary);"></div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="about-card p-4 p-lg-5 rounded-4 shadow-sm bg-white h-100">
                    <div class="icon-box mb-4">
                        <div class="icon-circle bg-primary-light d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 70px; height: 70px;">
                            <i class="fas fa-heartbeat fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h3 class="mb-3">Our Mission</h3>
                    <p class="mb-4">At NutriCore, we believe that optimal health is achieved through a balanced combination of nutrition and fitness. Our mission is to empower individuals to take control of their wellbeing through science-backed programs, personalized coaching, and a supportive community.</p>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-check-circle text-primary"></i>
                        </div>
                        <div>Evidence-based nutrition plans</div>
                    </div>
                    <div class="d-flex align-items-center mt-2">
                        <div class="me-3">
                            <i class="fas fa-check-circle text-primary"></i>
                        </div>
                        <div>Customized fitness programs</div>
                    </div>
                    <div class="d-flex align-items-center mt-2">
                        <div class="me-3">
                            <i class="fas fa-check-circle text-primary"></i>
                        </div>
                        <div>Holistic approach to wellness</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="about-card p-4 p-lg-5 rounded-4 shadow-sm bg-white h-100">
                    <div class="icon-box mb-4">
                        <div class="icon-circle bg-primary-light d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 70px; height: 70px;">
                            <i class="fas fa-trophy fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h3 class="mb-3">Our Approach</h3>
                    <p class="mb-4">We combine cutting-edge nutritional science with practical fitness strategies to create sustainable lifestyle changes. Our team of certified nutritionists and fitness experts work together to deliver comprehensive programs tailored to your unique needs and goals.</p>
                    <div class="stats-container d-flex justify-content-between">
                        <div class="stat-item text-center">
                            <div class="stat-number fw-bold display-6">10+</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item text-center">
                            <div class="stat-number fw-bold display-6">5K+</div>
                            <div class="stat-label">Clients Served</div>
                        </div>
                        <div class="stat-item text-center">
                            <div class="stat-number fw-bold display-6">98%</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions</h2>
                <p class="lead">Find answers to common questions about our programs and services</p>
                <div class="divider mx-auto" style="width: 80px; height: 4px; background: var(--primary);"></div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ Item 1 -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                <i class="fas fa-question-circle text-primary me-3"></i> How do I get started with NutriCore?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body pt-0">
                                Getting started is easy! Simply sign up for an account, complete our health assessment questionnaire, and schedule your initial consultation with one of our experts. We'll create a personalized plan based on your goals, preferences, and lifestyle.
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 2 -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                <i class="fas fa-question-circle text-primary me-3"></i> What makes NutriCore different from other fitness programs?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body pt-0">
                                Unlike generic programs, we combine personalized nutrition with customized fitness plans. Our holistic approach addresses diet, exercise, sleep, and stress management. Plus, you get ongoing support from our team of experts and access to our community for motivation and accountability.
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 3 -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                <i class="fas fa-question-circle text-primary me-3"></i> Do I need any special equipment for the fitness programs?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body pt-0">
                                Our programs are designed to be flexible. Many workouts require minimal or no equipment, using bodyweight exercises. For more advanced programs, we'll recommend affordable basics like dumbbells or resistance bands. We can adapt any program based on the equipment you have available.
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 4 -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                <i class="fas fa-question-circle text-primary me-3"></i> How often will I meet with my nutritionist or trainer?
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body pt-0">
                                The frequency depends on your chosen program and personal needs. Most clients start with weekly check-ins, then transition to biweekly or monthly as they progress. You'll always have access to our platform for questions between sessions, and we offer various communication channels (video calls, messaging) for convenience.
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 5 -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                <i class="fas fa-question-circle text-primary me-3"></i> Can I cancel or change my plan if it's not working for me?
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body pt-0">
                                Absolutely! We want you to have the best possible experience. You can modify or switch programs at any time. If something isn't working, we'll work with you to adjust your plan. We offer flexible cancellation policies because we're confident you'll love our approach once you experience the results.
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="images/NutriC.png" alt="NutriCore Logo" width="180" class="mb-3">
                <p>Empowering your health journey through personalized nutrition and fitness solutions.</p>
                <div class="social-icons mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                <h5 class="mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-white-50">Home</a></li>
                    <li class="mb-2"><a href="classes.php" class="text-white-50">Classes</a></li>
                    <li class="mb-2"><a href="shop.php" class="text-white-50">Shop</a></li>
                    <li class="mb-2"><a href="team.php" class="text-white-50">Our Team</a></li>
                    <li><a href="#our-story" class="text-white-50">About Us</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                <h5 class="mb-4">Contact Info</h5>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Main Street, Zahle, Lebanon</li>
                    <li class="mb-3"><i class="fas fa-phone-alt me-2 text-primary"></i> +961 8 123 456</li>
                    <li class="mb-3"><i class="fas fa-envelope me-2 text-primary"></i> zahle.nutricore@gmail.com</li>
                    <li><i class="fas fa-clock me-2 text-primary"></i> Mon-Sat: 8:00 AM - 11:00 PM</li>
                </ul>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white-50">© 2025 NutriCore. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                <a href="#" class="text-white-50">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Activate the current nav link based on scroll position
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
    // Newsletter form submission
    document.addEventListener('DOMContentLoaded', function() {
        const newsletterForms = document.querySelectorAll('.newsletter form, footer form');
        
        newsletterForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                if (emailInput.value) {
                    // Here you would typically send the data to your server
                    alert('Thank you for subscribing! You\'ll receive our next newsletter.');
                    emailInput.value = '';
                } else {
                    alert('Please enter your email address');
                }
            });
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('feedbackTrack');
    const slides = document.querySelectorAll('.feedback-slide');
    const cards = document.querySelectorAll('.feedback-card');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicators = document.querySelectorAll('.slideshow-indicator');
    let currentIndex = 0;
    
    function updateSlide() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Update active states
        cards.forEach(card => card.classList.remove('active'));
        cards[currentIndex].classList.add('active');
        
        indicators.forEach(ind => ind.classList.remove('active'));
        indicators[currentIndex].classList.add('active');
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlide();
    }
    
    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlide();
    }
    
    // Auto-rotate every 5 seconds
    let slideInterval = setInterval(nextSlide, 5000);
    
    // Pause on hover
    track.addEventListener('mouseenter', () => clearInterval(slideInterval));
    track.addEventListener('mouseleave', () => {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    // Navigation controls
    nextBtn.addEventListener('click', () => {
        clearInterval(slideInterval);
        nextSlide();
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    prevBtn.addEventListener('click', () => {
        clearInterval(slideInterval);
        prevSlide();
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    // Indicator clicks
    indicators.forEach(indicator => {
        indicator.addEventListener('click', () => {
            clearInterval(slideInterval);
            currentIndex = parseInt(indicator.dataset.index);
            updateSlide();
            slideInterval = setInterval(nextSlide, 5000);
        });
    });
});
</script>

</body>
</html>