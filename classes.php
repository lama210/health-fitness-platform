<?php 
session_start(); 
require_once("connection.php");

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;
$profileComplete = false;

if ($isLoggedIn) {
    // Check if user role is 'user'
    $user_id = $_SESSION['user_id'];
    $userCheck = $pdo->prepare("SELECT role, age, gender, email, name FROM user WHERE id = ?");
    $userCheck->execute([$user_id]);
    $user = $userCheck->fetch();

    if ($user && $user['role'] === 'user') {
        // Check if profile is complete
        $profileComplete = !empty($user['age']) && !empty($user['gender']);
    }
}

// Handle booking - only if logged in and is user
if (isset($_POST['book'])) {
    if (!$isLoggedIn) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['login_alert'] = "Please login first to book classes";
        header("Location: login.html");
        exit;
    }
    
    if (!$user || $user['role'] !== 'user') {
        echo "<div class='alert alert-danger text-center'>Access denied. Only users can book classes.</div>";
        exit;
    }

    if (!$profileComplete) {
        $message = "<div class='alert alert-danger text-center'>Please complete your profile (age and gender) before booking classes. <a href='profile.php' class='alert-link'>Go to Profile</a></div>";
    } else {
        $class_id = $_POST['class_id'];

        // Check if user has reached booking limit (max 3 active bookings)
        $bookingCount = $pdo->prepare("SELECT COUNT(*) FROM services 
                                      WHERE user_id = ? AND status = 'active'");
        $bookingCount->execute([$user_id]);
        $activeBookings = $bookingCount->fetchColumn();
        
        if ($activeBookings >= 3) {
            $message = "<div class='alert alert-warning text-center'>You've reached your limit of 3 active bookings.</div>";
        } else {
            // Check if already booked
            $check = $pdo->prepare("SELECT * FROM services WHERE user_id = ? AND class_id = ?");
            $check->execute([$user_id, $class_id]);

            if ($check->rowCount() == 0) {
                // Fetch class details including coach's email
                $classQuery = $pdo->prepare("SELECT classes.*, user.name AS coach_name, user.email AS coach_email 
                                            FROM classes 
                                            JOIN coach ON classes.coach_id = coach.id
                                            JOIN user ON coach.user_id = user.id
                                            WHERE classes.id = ?");
                $classQuery->execute([$class_id]);
                $class = $classQuery->fetch();

                if ($class) {
                    $coach_id = $class['coach_id'];
                    $coach_email = $class['coach_email'];
                    $coach_name = $class['coach_name'];
                    $availability = $class['schedule'];
                    $current = $class['current_users'];
                    $max = $class['max_users'];
                    $booked_at = date("Y-m-d H:i:s");
                    $expires_at = date("Y-m-d H:i:s", strtotime("+30 days"));

                    if ($current >= $max) {
                        $message = "<div class='alert alert-warning text-center'>Sorry, this class is already full.</div>";
                    } elseif (empty($availability)) {
                        $message = "<div class='alert alert-danger text-center'>Error: No schedule found for the class.</div>";
                    } else {
                        // Check if coach exists
                        $coachCheck = $pdo->prepare("SELECT id FROM coach WHERE id = ?");
                        $coachCheck->execute([$coach_id]);
                        $coach = $coachCheck->fetch();

                        if (!$coach) {
                            $message = "<div class='alert alert-danger text-center'>Error: Coach not found.</div>";
                        } else {
                            try {
                                // Begin transaction
                                $pdo->beginTransaction();

                                // Insert booking
                                $stmt = $pdo->prepare("INSERT INTO services (user_id, coach_id, nutritionist_id, class_id, booked_at, expires_at, status)
                                    VALUES (?, ?, NULL, ?, ?, ?, 'active')");
                                $stmt->execute([$user_id, $coach_id, $class_id, $booked_at, $expires_at]);

                                // Update class current_users
                                $updateClass = $pdo->prepare("UPDATE classes SET current_users = current_users + 1 WHERE id = ?");
                                $updateClass->execute([$class_id]);

                                // Commit transaction
                                $pdo->commit();

                                // Send emails to both user and coach
                                $mail = new PHPMailer(true);
                                try {
                                    // Server settings
                                    $mail->isSMTP();
                                    $mail->Host       = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = 'hussien3416@gmail.com';
                                    $mail->Password   = 'alis sixm ruhz jsxa';
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port       = 587;

                                    // 1. Send confirmation to user
                                    $mail->setFrom('hussien3416@gmail.com', 'FitConnect');
                                    $mail->addAddress($user['email'], $user['name']);
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Class Booking Confirmation';
                                    $mail->Body    = "
                                        <!DOCTYPE html>
                                        <html>
                                        <head>
                                            <style>
                                                body { font-family: Arial, sans-serif; line-height: 1.6; }
                                                .header { color: #4361ee; font-size: 18px; }
                                                .content { margin: 20px 0; }
                                                .footer { color: #777; font-size: 14px; }
                                            </style>
                                        </head>
                                        <body>
                                            <div class='header'>Hello {$user['name']},</div>
                                            <div class='content'>
                                                <p>Your class booking has been confirmed!</p>
                                                <p><strong>Booking Details:</strong></p>
                                                <ul>
                                                    <li><strong>Class:</strong> {$class['name']}</li>
                                                    <li><strong>Coach:</strong> {$coach_name}</li>
                                                    <li><strong>Schedule:</strong> {$class['schedule']}</li>
                                                    <li><strong>Booked On:</strong> $booked_at</li>
                                                    <li><strong>Expires On:</strong> $expires_at</li>
                                                </ul>
                                                <p>Thank you for choosing FitConnect!</p>
                                            </div>
                                            <div class='footer'>
                                                <p>This is an automated confirmation. Please do not reply directly to this email.</p>
                                                <p>&copy; " . date('Y') . " FitConnect. All rights reserved.</p>
                                            </div>
                                        </body>
                                        </html>
                                    ";
                                    $mail->send();

                                    // 2. Send notification to coach
                                    $mail->clearAddresses(); // Clear previous recipients
                                    $mail->addAddress($coach_email, $coach_name);
                                    $mail->Subject = 'New Class Booking Notification';
                                    $mail->Body    = "
                                        <!DOCTYPE html>
                                        <html>
                                        <head>
                                            <style>
                                                body { font-family: Arial, sans-serif; line-height: 1.6; }
                                                .header { color: #4361ee; font-size: 18px; }
                                                .content { margin: 20px 0; }
                                                .footer { color: #777; font-size: 14px; }
                                            </style>
                                        </head>
                                        <body>
                                            <div class='header'>Hello {$coach_name},</div>
                                            <div class='content'>
                                                <p>You have a new booking for your class!</p>
                                                <p><strong>Booking Details:</strong></p>
                                                <ul>
                                                    <li><strong>Class:</strong> {$class['name']}</li>
                                                    <li><strong>Student:</strong> {$user['name']}</li>
                                                    <li><strong>Student Email:</strong> {$user['email']}</li>
                                                    <li><strong>Schedule:</strong> {$class['schedule']}</li>
                                                    <li><strong>Booked On:</strong> $booked_at</li>
                                                </ul>
                                                <p>Please prepare for the session accordingly.</p>
                                            </div>
                                            <div class='footer'>
                                                <p>This is an automated notification. Please do not reply directly to this email.</p>
                                                <p>&copy; " . date('Y') . " FitConnect. All rights reserved.</p>
                                            </div>
                                        </body>
                                        </html>
                                    ";
                                    $mail->send();

                                    $message = "<div class='alert alert-success text-center'>Class booked successfully! Confirmation emails sent to you and your coach. <i class='fas fa-check-circle'></i></div>";
                                } catch (Exception $e) {
                                    error_log("Mailer Error: " . $mail->ErrorInfo);
                                    $message = "<div class='alert alert-success text-center'>Class booked successfully! <small>(Email notifications failed to send)</small> <i class='fas fa-check-circle'></i></div>";
                                }
                            } catch (PDOException $e) {
                                $pdo->rollBack();
                                $message = "<div class='alert alert-danger text-center'>Error: " . $e->getMessage() . "</div>";
                            }
                        }
                    }
                } else {
                    $message = "<div class='alert alert-danger text-center'>Class not found.</div>";
                }
            } else {
                $message = "<div class='alert alert-warning text-center'>You already booked this class. <i class='fas fa-exclamation-circle'></i></div>";
            }
        }
    }
}

// Fetch all available classes with coach picture from coach table
$raw_classes = $pdo->query("SELECT classes.*, user.name AS coach_name, coach.picture AS coach_image
                            FROM classes
                            JOIN coach ON classes.coach_id = coach.id
                            JOIN user ON coach.user_id = user.id
                            WHERE user.role = 'coach'")
                   ->fetchAll(PDO::FETCH_ASSOC);

// Group classes by coach and name
$grouped_classes = [];
foreach ($raw_classes as $class) {
    $key = $class['coach_name'] . '|' . $class['name'];
    $grouped_classes[$key][] = $class;
}

// Display login alert if it exists
if (isset($_SESSION['login_alert'])) {
    echo "<script>alert('" . $_SESSION['login_alert'] . "');</script>";
    unset($_SESSION['login_alert']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Classes | FitConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #4F46E5;
        --primary-light: #6366F1;
        --primary-dark: #4338CA;
        --secondary: #10B981;
        --light: #F9FAFB;
        --dark: #1F2937;
        --gray: #6B7280;
        --light-gray: #E5E7EB;
    }
    
    body {
        background-color: var(--light);
        font-family: 'Poppins', sans-serif;
        color: var(--dark);
    }
    
    .navbar-brand {
        font-weight: 700;
        color: var(--primary);
        font-size: 1.5rem;
    }
    
    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 12px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.2);
    }
    
    /* FIXED CARD STYLES WITH CONSISTENT IMAGE SIZES */
  .class-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    margin-bottom: 2rem;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.class-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.image-container {
    height: 400px; /* Increased from 250px */
    min-height: 250px; /* Increased from 200px */
    overflow: hidden;
    position: relative;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.5s ease;
}

    
    /* Special class for coach images that should maintain aspect ratio */
    .coach-image {
        width: auto;
        height: 100%;
        max-width: 100%;
        object-fit: contain;
        padding: 10px;
        background: white;
    }
    
    .class-card:hover .image-container img {
        transform: scale(1.05);
    }
    
   .card-body {
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    line-height: 1.3;
}

.coach-name {
    color: var(--primary);
    font-weight: 500;
    margin-bottom: 1rem;
    display: block;
    font-size: 0.95rem;
}

.price-tag {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    align-self: flex-start;
}

    
   .card-text {
    color: var(--gray);
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    flex-grow: 1;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

    .schedule-select {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid var(--light-gray);
        margin-bottom: 1.5rem;
        width: 100%;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }
    
    .schedule-select:focus {
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    
 .btn-book {
    background: var(--primary);
    border: none;
    padding: 0.85rem;
    border-radius: 8px;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
    color: white;
    margin-top: auto;
    font-size: 1rem;
    cursor: pointer;
}

.btn-book:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
    transform: none !important;
    box-shadow: none !important;
}
    
    .btn-book:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
    }
    
  
    
    .badge-space {
        background: var(--light);
        color: var(--dark);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        margin-bottom: 1.25rem;
    }
    
    .badge-space i {
        margin-right: 0.5rem;
        font-size: 0.8rem;
        color: var(--primary);
    }
    
    .alert {
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .container {
        max-width: 1200px;
        padding: 0 1.5rem;
    }
    
    /* Responsive adjustments */
  @media (max-width: 768px) {
    .image-container {
        height: 200px;
        min-height: 180px;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .card-title {
        font-size: 1.15rem;
    }
    
    .price-tag {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .image-container {
        height: 180px;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-dumbbell me-2"></i>FitConnect
            </a>
            <div class="d-flex align-items-center">
                <?php if ($isLoggedIn): ?>

                    <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-home me-1"></i> Back Home
                    </a>
                  
                <?php else: ?>
                    <a href="login.html" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                 
                     <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-home me-1"></i> Back Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Page Header -->
        <div class="page-header text-center">
            <h1 class="display-5 fw-bold mb-3">Available Fitness Classes</h1>
            <p class="lead mb-0">Book your preferred classes and start your fitness journey today</p>
        </div>

        <!-- Login Warning for non-logged in users -->
        <?php if (!$isLoggedIn): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                Please <a href="login.html" class="alert-link">login</a> to book classes.
            </div>
        <?php elseif ($user && $user['role'] !== 'user'): ?>
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-circle me-2"></i> 
                Access denied. Only users can book classes.
            </div>
        <?php elseif (!$profileComplete): ?>
            <!-- Profile Completion Warning -->
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                Please complete your profile (age and gender) before booking classes. 
                <a href="profile.php" class="alert-link">Complete Profile Now</a>
            </div>
        <?php endif; ?>

        <!-- Messages -->
        <?php if (isset($message)) echo $message; ?>

        <!-- Classes Grid -->
        <div class="row">
            <?php foreach ($grouped_classes as $key => $class_group): 
                $first = $class_group[0];
            ?>
               <div class="col-lg-4 col-md-6 mb-4 mt-2">
                    <div class="class-card">
                        <!-- Updated image tag to use coach's picture -->
    <div class="image-container">
    <?php if (!empty($first['coach_image'])): ?>
        <img src="<?= htmlspecialchars($first['coach_image']) ?>" 
             loading="lazy"
             alt="<?= htmlspecialchars($first['coach_name']) ?>">
    <?php else: ?>
        <img src="images/12.png" alt="Default coach image">
    <?php endif; ?>
</div>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($first['name']) ?></h5>
                            <span class="coach-name">
                                <i class="fas fa-user-tie me-2"></i><?= htmlspecialchars($first['coach_name']) ?>
                            </span>
                            <span class="price-tag">
                                <i class="fas fa-tag me-2"></i>$<?= number_format($first['price'], 2) ?>
                            </span>
                            
                            <p class="card-text text-muted mb-3">
                                <?= nl2br(htmlspecialchars($first['description'])) ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge-space">
                                    <i class="fas fa-users me-2"></i>
                                    <?= $first['current_users'] ?>/<?= $first['max_users'] ?> spots
                                </span>
                            </div>
                            
                            <form method="POST">
                                <select name="class_id" class="schedule-select" required <?= ($isLoggedIn && $user && $user['role'] === 'user' && $profileComplete) ? '' : 'disabled' ?>>
                                    <?php foreach ($class_group as $class): ?>
                                        <option value="<?= $class['id'] ?>">
                                            <i class="fas fa-calendar-alt me-2"></i><?= htmlspecialchars($class['schedule']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <?php if (!$isLoggedIn): ?>
                                    <button type="button" class="btn btn-book" onclick="showLoginAlert()">
                                        <i class="fas fa-calendar-plus me-2"></i> Login to Book
                                    </button>
                                <?php elseif (!$user || $user['role'] !== 'user'): ?>
                                    <button type="button" class="btn btn-book" disabled>
                                        <i class="fas fa-calendar-plus me-2"></i> Users Only
                                    </button>
                                <?php elseif (!$profileComplete): ?>
                                    <button type="button" class="btn btn-book" disabled>
                                        <i class="fas fa-calendar-plus me-2"></i> Complete Profile to Book
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="book" class="btn btn-book">
                                        <i class="fas fa-calendar-plus me-2"></i> Book Now
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any custom JavaScript here
        document.querySelectorAll('.schedule-select').forEach(select => {
            select.addEventListener('focus', function() {
                this.style.borderColor = '#4361ee';
            });
            select.addEventListener('blur', function() {
                this.style.borderColor = 'rgba(0, 0, 0, 0.1)';
            });
        });

        // Function to show login alert
        function showLoginAlert() {
            alert("Please login first to book classes");
            window.location.href = "login.html?redirect=" + encodeURIComponent(window.location.href);
        }

        // Check if there's a login alert in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const loginAlert = urlParams.get('login_alert');
        if (loginAlert) {
            alert(loginAlert);
            // Remove the parameter from URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>