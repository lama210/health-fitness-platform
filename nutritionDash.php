<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'nutritionist') {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id']; // Corrected

// Get nutritionist_id from nutrition table
$query = $pdo->prepare("SELECT id FROM nutrition WHERE user_id = ?");
$query->execute([$userId]);
$result = $query->fetch();

if (!$result) {
    die("Nutritionist not found.");
}

$nutritionist_id = $result['id'];

// Fetch nutritionist profile
// Fetch nutritionist profile including age and gender from user table
$stmt = $pdo->prepare("
    SELECT n.*, u.name, u.age, u.gender 
    FROM nutrition n 
    JOIN user u ON n.user_id = u.id 
    WHERE n.id = ?
");
$stmt->execute([$nutritionist_id]);
$nutritionist = $stmt->fetch();

// Check if profile is complete
if (
    empty($nutritionist['specification']) ||
    empty($nutritionist['experience']) ||
    empty($nutritionist['picture']) ||

    empty($nutritionist['age']) ||
    empty($nutritionist['gender'])
) {
    header("Location: profileNutrition.php");
    exit();
}

// Count active nutrition appointments
$appointmentsStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM services 
    WHERE nutritionist_id = ? AND type = 'nutrition' AND status = 'active'
");
$appointmentsStmt->execute([$nutritionist_id]);
$appointment_count = $appointmentsStmt->fetchColumn();


// Total meals
// Total meals
$totalMealsStmt = $pdo->prepare("SELECT COUNT(*) FROM meals WHERE nutritionist_id = ?");
$totalMealsStmt->execute([$nutritionist_id]);
$total_meals = $totalMealsStmt->fetchColumn();

// Average calories
// Average calories
$avgCaloriesStmt = $pdo->prepare("SELECT AVG(calories) FROM meals WHERE nutritionist_id = ?");
$avgCaloriesStmt->execute([$nutritionist_id]);
$avg_calories = round($avgCaloriesStmt->fetchColumn(), 2);

// Recent meals
// Recent meals
$recentMealsStmt = $pdo->prepare("
    SELECT * FROM meals 
    WHERE nutritionist_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentMealsStmt->execute([$nutritionist_id]);
$recent_meals = $recentMealsStmt->fetchAll();
// At the top of your existing PHP code (after session_start())
if (isset($_SESSION['message'])) {
    echo '<div class="notification is-success">'.$_SESSION['message'].'</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="notification is-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --accent-color: #f6c23e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
        
        .profile-card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: none;
        }
        
        .profile-img {
            height: 180px;
            width: 180px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: none;
        }
        
        .table-card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .appointments-card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .badge-calories {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--secondary-color);
        }
        
        .edit-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <!-- Profile Section -->
            <div class="col-lg-4 mb-4">
                <div class="profile-card bg-white p-4 text-center">
                    <div class="position-relative">
                        <img src="<?= $nutritionist['picture'] ?>" class="profile-img rounded-circle mb-3">
                        <button class="btn btn-sm btn-primary rounded-circle edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($nutritionist['name']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($nutritionist['specification']) ?></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-share-alt me-1"></i> Share
                        </button>
                        <a href="profileNutrition.php" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-pencil-alt me-1"></i> Edit
</a>

                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
             
            <div class="col-lg-8">
                <!-- Welcome Card -->
                <div class="welcome-card p-4 mb-4">
                    <h4 class="mb-3">Welcome back, <?= htmlspecialchars($nutritionist['name']) ?>!</h4>
                    <div class="row">
                        
                        <div class="col-md-6">
                            <div class="stat-card bg-white p-3 h-100">
                                <h6 class="text-muted mb-3">Active Appointments</h6>
                                <div class="d-flex align-items-center">
                                   <span class="display-6 fw-bold me-2"><?= $appointment_count ?></span>

                                    <span class="badge bg-light text-primary rounded-pill px-3 py-2">
                                        <i class="fas fa-users me-1"></i> Clients
                                    </span>
                                </div>
                                <small class="text-muted">Scheduled this week</small>
                            </div>
                        </div>
                    </div>
                </div>
               <!-- Meal Plans Table -->
<div class="table-card bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Meal Plan Details</h5>
       <a href="nutritionMeals.php" class="btn btn-sm btn-primary">
    <i class="fas fa-plus me-1"></i> Add New Meal
</a>

    </div>
   <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Day</th>
                <th>Description</th>
                <th>Calories</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Group meals by day
            $mealsByDay = [];
            foreach ($recent_meals as $meal) {
                $day = $meal['day_of_week'];
                if (!isset($mealsByDay[$day])) {
                    $mealsByDay[$day] = [];
                }

                // Only include meals with non-null type, description, and calories
                if (!empty($meal['type']) && !empty($meal['description']) && !empty($meal['calories'])) {
                    $mealsByDay[$day][] = $meal;
                }
            }

            // Display meals grouped by day
            foreach ($mealsByDay as $day => $meals):
                if (empty($meals)) continue; // skip empty days

                $dayTotal = array_sum(array_column($meals, 'calories'));
            ?>
                <!-- Day Header -->
                <tr class="bg-light">
                    <td colspan="6"><strong><?= htmlspecialchars($day) ?></strong></td>
                </tr>

                <?php foreach ($meals as $meal): ?>
                    <tr>
                        <td><?= htmlspecialchars($meal['type']) ?></td>
                        <td><?= htmlspecialchars($meal['day_of_week']) ?></td>
                        <td><?= nl2br(htmlspecialchars($meal['description'])) ?></td>
                        <td><?= htmlspecialchars($meal['calories']) ?> kcal</td>
                        <td><?= date('M j, Y', strtotime($meal['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Day Total -->
                <tr class="bg-light fw-bold">
                    <td colspan="3">Total Calories for <?= htmlspecialchars($day) ?>:</td>
                    <td><?= $dayTotal ?> kcal</td>
                    <td colspan="2"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
// Fetch recent appointments for this nutritionist
$appointmentsStmt = $pdo->prepare("
    SELECT s.*, u.name 
    FROM services s
    JOIN user u ON s.user_id = u.id
    WHERE s.nutritionist_id = ?
    ORDER BY s.booked_at DESC
    LIMIT 5
");
$appointmentsStmt->execute([$nutritionist_id]);
$appointments = $appointmentsStmt->fetchAll();
?>
               <div class="appointments-card bg-white p-4">
    <h5 class="mb-3">Recent Appointments</h5>
    <div class="list-group">
        <?php if ($appointments): ?>
            <?php foreach ($appointments as $app): ?>
                <div class="list-group-item border-0 py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($app['name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($app['type']) ?></small>
                        </div>
                        <div class="text-end">
                            <small class="d-block text-muted"><?= date("D, H:i A", strtotime($app['booked_at'])) ?></small>
                            <?php
                            $status = strtolower($app['status']);
                            $statusClasses = [
                                'confirmed' => 'bg-success bg-opacity-10 text-success',
                                'pending'   => 'bg-warning bg-opacity-10 text-warning',
                                'scheduled' => 'bg-primary bg-opacity-10 text-primary',
                                'canceled'  => 'bg-danger bg-opacity-10 text-danger',
                            ];
                          $badgeClass = isset($statusClasses[$status]) ? $statusClasses[$status] : 'bg-secondary bg-opacity-10 text-secondary';

                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No recent appointments.</p>
        <?php endif; ?>
    </div>
    
</div>


    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nutritionist['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nutritionist['specification']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" rows="3">Certified nutritionist with expertise in weight management and sports nutrition.</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>