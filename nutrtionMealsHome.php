
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Store the current URL to redirect back after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.html");
    exit();
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'health';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get user's booked nutritionists
$user_id = $_SESSION['user_id'];
$booked_nutritionists = [];

// Get active nutrition bookings
$booking_sql = "SELECT nutritionist_id 
               FROM services 
               WHERE user_id = $user_id 
               AND type = 'nutrition' 
               AND status = 'active' 
               AND expires_at > NOW()";

$booking_result = $conn->query($booking_sql);

if ($booking_result->num_rows > 0) {
    while ($row = $booking_result->fetch_assoc()) {
        $booked_nutritionists[] = $row['nutritionist_id'];
    }
}

// If no booked nutritionists, show message
if (empty($booked_nutritionists)) {
    $show_empty_state = true;
} else {
    // Get meals only for booked nutritionists
    $nutri_ids = implode(",", $booked_nutritionists);
    
    $sql = "SELECT m.*, u.name AS nutritionist_name
            FROM meals m
            JOIN nutrition n ON m.nutritionist_id = n.id
            JOIN user u ON u.id = n.user_id
            WHERE m.nutritionist_id IN ($nutri_ids)
            ORDER BY m.nutritionist_id, 
                     FIELD(m.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                     FIELD(m.type, 'Breakfast', 'Lunch', 'Dinner', 'Snack'), 
                     m.created_at DESC";

    $result = $conn->query($sql);
    $nutritionists = [];

    // Define the correct day order
    $dayOrder = [
        'Monday' => 1,
        'Tuesday' => 2,
        'Wednesday' => 3,
        'Thursday' => 4,
        'Friday' => 5,
        'Saturday' => 6,
        'Sunday' => 7
    ];

    while ($row = $result->fetch_assoc()) {
        $nutriId = $row['nutritionist_id'];
        $day = $row['day_of_week'];

        if (!isset($nutritionists[$nutriId])) {
            $nutritionists[$nutriId] = [
                'name' => $row['nutritionist_name'],
                'meals' => [],
            ];
        }

        $nutritionists[$nutriId]['meals'][$day][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Meals | Modern Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00c6a9;
            --primary-light: #e0f7f4;
            --secondary: #5a67d8;
            --dark: #2d3748;
            --light: #f8fafc;
            --gray: #e2e8f0;
        }
        
        body {
            background-color: #f9fafb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .nutritionist-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .nutritionist-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .day-header {
            background-color: var(--primary-light);
            color: var(--dark);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-left: 4px solid var(--primary);
        }
        
        .meal-type {
            display: inline-block;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .breakfast { background-color: #fff3e0; color: #e65100; }
        .lunch { background-color: #e3f2fd; color: #1565c0; }
        .dinner { background-color: #e8f5e9; color: #2e7d32; }
        .snack { background-color: #f3e5f5; color: #7b1fa2; }
        
        .meal-row {
            border-bottom: 1px solid var(--gray);
            transition: background-color 0.2s;
        }
        
        .meal-row:hover {
            background-color: rgba(0, 198, 169, 0.05);
        }
        
        .total-row {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
        }
        
        .calorie-badge {
            background-color: var(--primary);
            color: white;
            padding: 0.35rem 0.7rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .empty-state {
            padding: 3rem;
            text-align: center;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }
        
        /* Login modal styles */
        .login-modal .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .login-modal .modal-header {
            background-color: var(--primary);
            color: white;
            border-bottom: none;
        }
        
        .login-modal .modal-body {
            padding: 2rem;
        }
        
        .login-modal .btn-login {
            background-color: var(--primary);
            color: white;
            width: 100%;
            padding: 0.5rem;
            font-weight: 600;
        }
        
        .login-modal .form-control {
            padding: 0.75rem;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Login Modal -->
    <div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel"><i class="fas fa-sign-in-alt me-2"></i>Login Required</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-lock fa-4x mb-4" style="color: var(--primary);"></i>
                    <h4 class="mb-3">Please Login to Continue</h4>
                    <p class="text-muted mb-4">You need to be logged in to access this feature.</p>
                    <div class="d-grid gap-2">
                        <a href="login.html" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-3" style="color: var(--dark);">🥗 Your Nutrition Plans</h1>
            <p class="lead text-muted">Meal plans from your booked nutritionists</p>
        </div>

        <?php if (!empty($nutritionists)): ?>
            <div class="row">
                <?php foreach ($nutritionists as $nutriId => $data): ?>
                    <div class="col-lg-8 mx-auto mb-4">
                        <div class="nutritionist-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-md me-2"></i>
                                    <?= htmlspecialchars($data['name']) ?>
                                </h5>
                                <span class="badge bg-white text-primary">Nutritionist</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th width="15%">Meal Type</th>
                                            <th width="25%">Description</th>
                                            <th width="15%">Calories</th>
                                            <th width="15%">Day</th>
                                            <th width="15%">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['meals'] as $day => $meals): ?>
                                            <?php 
                                                // Filter meals that have all important fields set
                                                $filteredMeals = array_filter($meals, function ($meal) {
                                                    return !empty($meal['type']) && !empty($meal['description']) && !empty($meal['calories']);
                                                }); 
                                            ?>
                                            <?php if (!empty($filteredMeals)): ?>
                                                <tr class="day-header">
                                                    <td colspan="5">
                                                        <i class="far fa-calendar-alt me-2"></i>
                                                        <?= htmlspecialchars($day) ?>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    $totalCalories = 0;
                                                    foreach ($filteredMeals as $meal): 
                                                        $totalCalories += $meal['calories'];
                                                ?>
                                                    <tr class="meal-row align-middle">
                                                        <td>
                                                            <span class="meal-type <?= strtolower($meal['type']) ?>">
                                                                <?= htmlspecialchars($meal['type']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($meal['description']) ?></td>
                                                        <td>
                                                            <span class="calorie-badge">
                                                                <?= htmlspecialchars($meal['calories']) ?> kcal
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($meal['day_of_week']) ?></td>
                                                        <td class="text-muted small">
                                                            <?= date('M j, Y', strtotime($meal['created_at'])) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td colspan="2" class="text-end"><strong>Daily Total:</strong></td>
                                                    <td><strong><?= $totalCalories ?> kcal</strong></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state text-center">
                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                <h4 class="mb-3">No Active Nutrition Plans</h4>
                <p class="text-muted">You don't have any active nutritionist bookings.</p>
                <button class="btn btn-primary mt-3" id="bookNutritionistBtn">
                    <i class="fas fa-book-medical me-2"></i>Book a Nutritionist
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if there's a login message in the URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login_required')) {
                // Show login modal
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }
            
            // Handle book nutritionist button click
            document.getElementById('bookNutritionistBtn')?.addEventListener('click', function() {
                <?php if (!isset($_SESSION['user_id'])): ?>
                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                <?php else: ?>
                    window.location.href = 'bookNutritionist.php';
                <?php endif; ?>
            });
            
    
        });
    </script>
</body>
</html>