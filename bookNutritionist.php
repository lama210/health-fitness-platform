<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require_once('connection.php');

// Clear and retrieve alert messages from session
$bookingSuccess = isset($_SESSION['booking_success']) ? $_SESSION['booking_success'] : null;
$bookingError = isset($_SESSION['booking_error']) ? $_SESSION['booking_error'] : null;
$loginRequiredError = isset($_SESSION['login_required']) ? $_SESSION['login_required'] : null;

unset($_SESSION['booking_success'], $_SESSION['booking_error'], $_SESSION['login_required']);

// Automatically expire old bookings
$pdo->exec("UPDATE services 
           SET status = 'expired' 
           WHERE type = 'nutrition' 
           AND status = 'active' 
           AND expires_at < NOW()");

// Check active bookings if logged in
$activeBookings = 0;
$currentBooking = null;
if (isset($_SESSION['user_id'])) {
    $checkStmt = $pdo->prepare("SELECT * FROM services 
                               WHERE user_id = :user_id 
                               AND type = 'nutrition' 
                               AND status = 'active' 
                               AND expires_at > NOW() 
                               LIMIT 1");
    $checkStmt->execute([':user_id' => $_SESSION['user_id']]);
    $activeBookings = $checkStmt->rowCount();
    $currentBooking = $checkStmt->fetch(PDO::FETCH_ASSOC);
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nutritionist_id'], $_POST['schedule_json'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_required'] = "You need to login first to book a nutritionist.";
        header("Location: login.html");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $nutritionist_id = $_POST['nutritionist_id'];
    $schedule_json = $_POST['schedule_json'];

    if ($activeBookings > 0) {
        $_SESSION['booking_error'] = "You already have an active nutrition booking. Only one booking is allowed at a time.";
    } else {
        $expires_at = date('Y-m-d H:i:s', strtotime('+13 days'));
        $stmt = $pdo->prepare("INSERT INTO services 
            (user_id, nutritionist_id, type, booked_at, expires_at, status)
            VALUES (:user_id, :nutritionist_id, 'nutrition', NOW(), :expires_at, 'active')");

        if ($stmt->execute([
            ':user_id' => $user_id,
            ':nutritionist_id' => $nutritionist_id,
            ':expires_at' => $expires_at
        ])) {
            $_SESSION['booking_success'] = "Booking confirmed! Expires on " . date('M j, Y', strtotime($expires_at));

            // Fetch user info
            $userStmt = $pdo->prepare("SELECT name, email FROM user WHERE id = :id");
            $userStmt->execute([':id' => $user_id]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

            // Fetch nutritionist info
            $nutriStmt = $pdo->prepare("SELECT u.name, u.email FROM user u JOIN nutrition n ON u.id = n.user_id WHERE n.id = :id");
            $nutriStmt->execute([':id' => $nutritionist_id]);
            $nutriData = $nutriStmt->fetch(PDO::FETCH_ASSOC);

            // Decode the schedule JSON
            $scheduleData = json_decode($schedule_json, true);

            // Format the schedule information
            $scheduleText = '';
            if (isset($scheduleData['day'])) {
                $scheduleText = "Day: " . $scheduleData['day'] . "\n";
                $scheduleText .= "Time: " . $scheduleData['start'] . " - " . $scheduleData['end'] . "\n";
            } elseif (isset($scheduleData['days'])) {
                $scheduleText = "Days: " . implode(", ", $scheduleData['days']) . "\n";
                $scheduleText .= "Time: " . $scheduleData['start_time'] . " - " . $scheduleData['end_time'] . "\n";
            }

            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'hussien3416@gmail.com';
                $mail->Password = 'alis sixm ruhz jsxa';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // 1. Send to User
                $mail->setFrom('hussien3416@gmail.com', 'Nutrition Booking System');
                $mail->addAddress($userData['email'], $userData['name']);
                $mail->Subject = 'Nutrition Booking Confirmation';
                $mail->Body = "Dear {$userData['name']},\n\n"
                    . "Thank you for booking with {$nutriData['name']}.\n\n"
                    . "Booking Details:\n"
                    . "Nutritionist: {$nutriData['name']}\n"
                    . "Schedule:\n" . $scheduleText . "\n"
                    . "Nutritionist Contact: {$nutriData['email']}\n\n"
                    . "Please make sure to attend your scheduled sessions.\n\n"
                    . "Regards,\nHealthPlus Team";
                $mail->send();

                // 2. Send to Nutritionist
                $mail->clearAddresses();
                $mail->addAddress($nutriData['email'], $nutriData['name']);
                $mail->Subject = 'New Nutrition Booking';
                $mail->Body = "Dear {$nutriData['name']},\n\n"
                    . "You have a new booking from {$userData['name']}.\n\n"
                    . "Client Details:\n"
                    . "Name: {$userData['name']}\n"
                    . "Email: {$userData['email']}\n\n"
                    . "Booking Details:\n"
                    . "Schedule:\n" . $scheduleText . "\n"
                    . "Please contact the client to confirm any additional details.\n\n"
                    . "Regards,\nHealthPlus Team";
                $mail->send();

            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }

            // Refresh active bookings
            $checkStmt->execute([':user_id' => $_SESSION['user_id']]);
            $activeBookings = $checkStmt->rowCount();
            $currentBooking = $checkStmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Handle day filter
$selectedDay = isset($_GET['day']) ? $_GET['day'] : '';

// Fetch nutritionists with aggregated schedules
// Replace the existing SQL query with this:
$sql = "SELECT 
            u.id AS user_id,
            u.name,
            u.age,
            u.gender,
            u.email,
            n.specification,
            n.experience,
            n.picture,
            n.id AS nutrition_id,
            n.schedule
        FROM user u
        JOIN nutrition n ON u.id = n.user_id
        WHERE n.schedule IS NOT NULL 
        AND n.schedule != 'Not set'
        ORDER BY u.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($results as $row) {
    $userId = $row['user_id'];
    
    // Skip if schedule is invalid
    $schedule = json_decode($row['schedule'], true);
    if (!$schedule || (!isset($schedule['day']) && !isset($schedule['days']))) {
        continue;
    }
    
    if (!isset($grouped[$userId])) {
        $grouped[$userId] = [
            'nutrition_id' => $row['nutrition_id'],
            'name' => $row['name'],
            'age' => $row['age'],
            'gender' => $row['gender'],
            'email' => $row['email'],
            'specification' => $row['specification'],
            'experience' => $row['experience'],
            'picture' => $row['picture'],
            'schedules' => []
        ];
    }
    
    // Add the valid schedule
    $grouped[$userId]['schedules'][] = $schedule;
}

// Apply day filter
// Apply day filter
$filteredGroup = [];
foreach ($grouped as $userId => $nutritionist) {
    $filteredSchedules = [];
    
    foreach ($nutritionist['schedules'] as $schedule) {
        // Skip if schedule is invalid
        if (!is_array($schedule)) {
            continue;
        }
        
        $match = false;
        if ($selectedDay !== '') {
            if (isset($schedule['day'])) {
                $match = strtolower($schedule['day']) === strtolower($selectedDay);
            } elseif (isset($schedule['days'])) {
                $match = in_array($selectedDay, array_map('ucfirst', $schedule['days']));
            }
        } else {
            $match = true;
        }
        
        if ($match) {
            $filteredSchedules[] = $schedule;
        }
    }

    // Only include nutritionists with valid schedules
    if (!empty($filteredSchedules)) {
        $nutritionist['schedules'] = $filteredSchedules;
        $filteredGroup[$userId] = $nutritionist;
    }
}

$grouped = $filteredGroup;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Experts | HealthPlus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --mint-green: #8bd8bd;
            --light-mint: #d4f3e6;
            --deep-mint: #5cc2a0;
            --sky-blue: #87ceeb;
            --light-blue: #e0f7fa;
            --deep-blue: #4a9fe0;
            --text-dark: #2d3748;
            --text-light: #4a5568;
            --white: #ffffff;
            --card-shadow: 0 12px 24px rgba(74, 159, 224, 0.1);
        }
        
        body {
            background-color: #f8fcfb;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .nutrition-header {
            background: linear-gradient(135deg, var(--deep-mint), var(--deep-blue));
            color: var(--white);
            padding: 5rem 0 7rem;
            margin-bottom: 2rem;
              position: relative; /* Add this */
    padding-top: 6rem; /* Increase top padding */
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }
        
        .nutrition-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none" opacity="0.1"><path d="M0,70 C20,40 40,60 60,30 C80,0 100,50 100,70 L100,100 L0,100 Z" fill="white"/></svg>');
            background-size: cover;
            background-position: bottom;
        }
        
        .nutrition-card {
            position: relative;
            padding-top: 80px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            background: white;
        }
        
        .nutrition-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(74, 159, 224, 0.15);
        }
        
        .nutrition-avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 5px solid var(--white);
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin: -60px auto 1.5rem;
            display: block;
            position: relative;
            z-index: 2;
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        .nutrition-card:hover .nutrition-avatar {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .specialty-label {
            background: linear-gradient(to right, var(--deep-mint), var(--deep-blue));
            color: var(--white);
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            margin: 0.5rem 0;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(92, 194, 160, 0.2);
        }
        
        .availability-badge {
            background: rgba(74, 159, 224, 0.1);
            color: var(--deep-blue);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            display: inline-block;
            border: 1px solid rgba(74, 159, 224, 0.3);
            margin: 0.5rem 0;
        }
        
        .btn-nutrition {
            background: linear-gradient(135deg, var(--deep-mint), var(--deep-blue));
            color: var(--white);
            border: none;
            border-radius: 30px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(92, 194, 160, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-nutrition:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(92, 194, 160, 0.4);
            color: var(--white);
        }
        
        .btn-nutrition:disabled {
            background: #e2e8f0;
            transform: none;
            box-shadow: none;
        }
        
        .btn-nutrition::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .btn-nutrition:hover::after {
            opacity: 1;
        }
        
        .filter-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 3rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .nutrition-icon {
            color: var(--deep-mint);
            margin-right: 0.5rem;
        }
        
        .experience-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--white);
            color: var(--deep-blue);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            font-weight: 700;
            font-size: 0.8rem;
            z-index: 2;
        }
        
        .nutrition-stats {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .value {
            font-weight: 700;
            color: var(--deep-blue);
            font-size: 1.2rem;
        }
        
        .stat-item .label {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        .current-booking {
            background: rgba(139, 216, 189, 0.15);
            border-left: 4px solid var(--deep-mint);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(139, 216, 189, 0.3);
        }
        
        .form-select, .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--deep-mint);
            box-shadow: 0 0 0 0.25rem rgba(92, 194, 160, 0.25);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(92, 194, 160, 0.15);
            color: #1a533f;
            border-left: 4px solid var(--deep-mint);
        }
        
        .alert-danger {
            background-color: rgba(211, 47, 47, 0.1);
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #854d0e;
            border-left: 4px solid #f59e0b;
        }
        
        .page-title {
            font-weight: 700;
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .page-title::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--white);
            border-radius: 3px;
        }
        
        .empty-state {
            background: var(--white);
            padding: 3rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--card-shadow);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--deep-mint);
            margin-bottom: 1rem;
            opacity: 0.7;
        }
        
        .badge-available {
            background-color: rgba(92, 194, 160, 0.1);
            color: var(--deep-mint);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .filter-btn {
            transition: all 0.3s;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .nutrition-header {
                padding: 3rem 0 5rem;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }
            
            .filter-section {
                padding: 1.5rem;
            }
            
            .nutrition-stats {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .nutrition-avatar {
                width: 100px;
                height: 100px;
                margin: -50px auto 1rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-card {
            animation: fadeIn 0.6s ease forwards;
            opacity: 0;
        }
        
        /* Delay animations for each card */
        .nutrition-card:nth-child(1) { animation-delay: 0.1s; }
        .nutrition-card:nth-child(2) { animation-delay: 0.2s; }
        .nutrition-card:nth-child(3) { animation-delay: 0.3s; }
        .nutrition-card:nth-child(4) { animation-delay: 0.4s; }
        .nutrition-card:nth-child(5) { animation-delay: 0.5s; }
        .nutrition-card:nth-child(6) { animation-delay: 0.6s; }
        
        /* Ripple effect */
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        .home-button {
    position: absolute;
    left: 1.5rem;
    top: 1.5rem;
    color: var(--white);
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.3s;
}

.home-button:hover {
    opacity: 0.8;
}
    </style>
</head>
<body>
    
    <header class="nutrition-header">
        <div class="container text-center position-relative">
            <h1 class="display-4 fw-bold mb-3 page-title">
                <i class="fas fa-leaf me-2"></i> Nutrition Specialists
            </h1>
            <p class="lead mb-0">Find certified nutrition experts to guide your health journey</p>
        </div>
         <a href="index.php" class="home-button">
        <i class="fas fa-arrow-left me-1"></i> Back to Home
    </a>
    </header>
    


    <div class="container mb-5">
        <?php if (!empty($bookingSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <div><?= htmlspecialchars($bookingSuccess) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($bookingError)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?= htmlspecialchars($bookingError) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($loginRequiredError)): ?>
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div><?= htmlspecialchars($loginRequiredError) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $activeBookings > 0 && $currentBooking): ?>
            <div class="current-booking animate-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-3 mb-md-0">
                        <h5 class="mb-2"><i class="fas fa-calendar-check me-2"></i> Your Active Nutrition Plan</h5>
                        <div class="d-flex flex-wrap gap-4">
                            <div>
                                <small class="text-muted d-block">Start Date</small>
                                <strong><?= date('M j, Y', strtotime($currentBooking['booked_at'])) ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Expiration</small>
                                <strong><?= date('M j, Y', strtotime($currentBooking['expires_at'])) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge-available px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i> Active Plan
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="filter-section animate-card">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label for="day" class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt nutrition-icon"></i> Filter by Availability
                    </label>
                    <select name="day" id="day" class="form-select">
                        <option value="">All Days</option>
                        <?php
                        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        foreach ($days as $day) {
                            $selected = ($selectedDay === $day) ? 'selected' : '';
                            echo "<option value='$day' $selected>$day</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-nutrition filter-btn mt-md-4">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-4 text-md-end mt-md-4">
                    <span class="badge bg-light text-dark p-2">
                        <i class="fas fa-user-nurse text-primary me-1"></i>
                        <?= count($grouped) ?> Specialist<?= count($grouped) !== 1 ? 's' : '' ?> Available
                    </span>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if (empty($grouped)): ?>
                <div class="col-12 animate-card">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4 class="mb-3">No specialists available</h4>
                        <p class="text-muted mb-4">Try adjusting your filters or check back later</p>
                        <a href="?" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-sync me-1"></i> Reset Filters
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($grouped as $userId => $nutritionist): ?>
                    <?php $firstSchedule = $nutritionist['schedules'][0]; ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card nutrition-card h-100 animate-card">
                            <div class="experience-tag">
                                <?= htmlspecialchars($nutritionist['experience']) ?>+ yrs
                            </div>
                            <div class="text-center pt-4">
                                <?php if (!empty($nutritionist['picture'])): ?>
                                    <img src="<?= htmlspecialchars($nutritionist['picture']) ?>" 
                                         alt="<?= htmlspecialchars($nutritionist['name']) ?>" 
                                         class="nutrition-avatar">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($nutritionist['name']) ?>&background=5cc2a0&color=fff" 
                                         class="nutrition-avatar">
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-2 fw-semibold">
                                    <?= htmlspecialchars($nutritionist['name']) ?>
                                </h5>
                                <div class="text-center mb-3">
                                    <span class="specialty-label">
                                        <?= htmlspecialchars($nutritionist['specification']) ?>
                                    </span>
                                </div>

                                <div class="nutrition-stats">
                                    <div class="stat-item">
                                        <div class="value"><?= htmlspecialchars($nutritionist['age']) ?></div>
                                        <div class="label">Age</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="value"><?= htmlspecialchars($nutritionist['gender']) ?></div>
                                        <div class="label">Gender</div>
                                    </div>
                                </div>

                                <div class="availability-badge mb-3">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php
                                    if (isset($firstSchedule['day'])) {
                                        echo htmlspecialchars($firstSchedule['day'] . " " . $firstSchedule['start'] . "-" . $firstSchedule['end']);
                                    } elseif (isset($firstSchedule['days'])) {
                                        echo htmlspecialchars(implode(", ", $firstSchedule['days']) . " " . $firstSchedule['start_time'] . "-" . $firstSchedule['end_time']);
                                    }
                                    ?>
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="nutritionist_id" value="<?= htmlspecialchars($nutritionist['nutrition_id']) ?>">
                                    <input type="hidden" name="schedule_json" value='<?= htmlspecialchars(json_encode($firstSchedule)) ?>'>
                                    <?php if (!isset($_SESSION['user_id'])): ?>
                                        <button type="button" class="btn-nutrition" onclick="window.location.href='login.html'">
                                            <i class="fas fa-sign-in-alt me-1"></i> Login to Book
                                        </button>
                                    <?php elseif ($activeBookings > 0): ?>
                                        <button type="button" class="btn-nutrition" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Already Booked
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn-nutrition">
                                            <i class="fas fa-calendar-plus me-1"></i> Book Session
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation for cards
            const animateElements = document.querySelectorAll('.animate-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            animateElements.forEach(el => {
                observer.observe(el);
            });
            
            // Ripple effect for buttons
            const buttons = document.querySelectorAll('.btn-nutrition');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.disabled) return;
                    
                    const rect = button.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    button.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 1000);
                });
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>