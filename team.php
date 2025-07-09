<?php
session_start();
require_once("connection.php");

// Fetch all coaches
$coaches = $pdo->query("
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.age, 
        u.gender,
        c.picture AS coach_picture,
        c.specialization, 
        c.experience AS coach_experience
    FROM user u
    JOIN coach c ON u.id = c.user_id
    WHERE u.role = 'coach'
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all nutritionists
$nutritionists = $pdo->query("
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.age, 
        u.gender,
        n.picture AS nutritionist_picture,
        n.specification,
        n.experience AS nutritionist_experience
    FROM user u
    JOIN nutrition n ON u.id = n.user_id
    WHERE u.role = 'nutritionist'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Team | FitConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }
        
        .team-header {
            background: var(--gradient);
            color: white;
            padding: 5rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Circular Card Styles */
       /* Add this to your CSS */
.team-circle-card {
    position: relative;
    width: 280px;
    height: 280px;
    margin: 0 auto 3rem;
    perspective: 1000px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.6s ease;
}

/* This class will be added when the page loads */
.card-visible {
    opacity: 1;
    transform: translateY(0);
}
        
        .team-circle-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.8s;
            transform-style: preserve-3d;
        }
        
        .team-circle-card:hover .team-circle-inner {
            transform: rotateY(180deg);
        }
        
        .team-circle-front, 
        .team-circle-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .team-circle-front {
            background: white;
            transform: rotateY(0deg);
        }
        
        .team-circle-back {
            background: var(--primary);
            color: white;
            transform: rotateY(180deg);
            padding: 2rem;
            text-align: center;
        }
        
        .team-circle-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .team-circle-card:hover .team-circle-img {
            transform: scale(1.05);
        }
        
        .team-circle-name {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .team-circle-role {
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .team-circle-bio {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .team-circle-social {
            display: flex;
            gap: 10px;
        }
        
        .team-circle-social a {
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }
        
        .team-circle-social a:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        /* Floating animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Section styles */
        .section-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 3rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .team-circle-card {
                width: 220px;
                height: 220px;
            }
            
            .team-header {
                padding: 3rem 0;
            }
        }
        
        /* Background elements */
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            background: var(--primary);
            top: -150px;
            right: -150px;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: var(--accent);
            bottom: -100px;
            left: -100px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-dumbbell me-2"></i>FitConnect
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back Home
                </a>
               
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="team-header position-relative">
        <div class="floating-shapes">
            <div class="shape shape-1 floating" style="animation-delay: 0s;"></div>
            <div class="shape shape-2 floating" style="animation-delay: 2s;"></div>
        </div>
        <div class="container position-relative">
            <h1 class="display-4 fw-bold mb-3">Meet Our Experts</h1>
            <p class="lead mb-0 mx-auto" style="max-width: 700px;">
                Our certified professionals are dedicated to helping you achieve your fitness and nutrition goals
            </p>
        </div>
    </section>

    <!-- Team Content -->
    <div class="container pb-5">
        <!-- Coaches Section -->
        <section class="py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Fitness Coaches</h2>
                <p class="text-muted">Certified professionals with years of experience</p>
            </div>
            
            <div class="row">
                <?php foreach ($coaches as $coach): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-5">
                        <div class="team-circle-card">
                            <div class="team-circle-inner">
                                <div class="team-circle-front">
                                    <img src="<?= htmlspecialchars($coach['coach_picture'] ?''.$coach['coach_picture'] : 'images/coach-placeholder.jpg') ?>" 
                                         class="team-circle-img" alt="<?= htmlspecialchars($coach['name']) ?>">
                                </div>
                                <div class="team-circle-back">
                                    <h3 class="team-circle-name"><?= htmlspecialchars($coach['name']) ?></h3>
                                    <div class="team-circle-role">Fitness Coach</div>
                                    <div class="team-circle-bio">
                                        <?= htmlspecialchars($coach['specialization']) ?><br>
                                        <?= htmlspecialchars($coach['coach_experience']) ?> years experience
                                    </div>
                                    <div class="team-circle-social">
                                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#"><i class="fab fa-instagram"></i></a>
                                        <a href="mailto:<?= htmlspecialchars($coach['email']) ?>"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Nutritionists Section -->
        <section class="py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Nutrition Experts</h2>
                <p class="text-muted">Registered dietitians and nutrition specialists</p>
            </div>
            
            <div class="row">
                <?php foreach ($nutritionists as $nutritionist): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-5">
                        <div class="team-circle-card">
                            <div class="team-circle-inner">
                                <div class="team-circle-front">
                                    <img src="<?= htmlspecialchars($nutritionist['nutritionist_picture'] ? ''.$nutritionist['nutritionist_picture'] : 'images/nutritionist-placeholder.jpg') ?>" 
                                         class="team-circle-img" alt="<?= htmlspecialchars($nutritionist['name']) ?>">
                                </div>
                                <div class="team-circle-back">
                                    <h3 class="team-circle-name"><?= htmlspecialchars($nutritionist['name']) ?></h3>
                                    <div class="team-circle-role">Nutritionist</div>
                                    <div class="team-circle-bio">
                                        <?= htmlspecialchars($nutritionist['specification']) ?><br>
                                        <?= htmlspecialchars($nutritionist['nutritionist_experience']) ?> years experience
                                    </div>
                                    <div class="team-circle-social">
                                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#"><i class="fab fa-instagram"></i></a>
                                        <a href="mailto:<?= htmlspecialchars($nutritionist['email']) ?>"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

 <!-- Replace the existing JavaScript with this simplified version -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simpler scroll-triggered animation
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.team-circle-card');
        
        function checkVisibility() {
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    card.classList.add('card-visible');
                }
            });
        }
        
        // Initial check
        checkVisibility();
        
        // Check on scroll
        window.addEventListener('scroll', checkVisibility);
    });
</script>
</body>
</html>