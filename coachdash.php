<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coach') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Join user and coach tables to get full coach info
$stmt = $pdo->prepare("SELECT u.name, u.email, u.gender, c.id as coach_id, c.specialization, c.experience, c.picture 
                       FROM user u 
                       JOIN coach c ON u.id = c.user_id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$coach = $stmt->fetch();

if (!$coach) {
    die("Coach not found.");
}

$coach_id = $coach['coach_id'];

// Fetch classes for this coach
$class_stmt = $pdo->prepare("SELECT * FROM classes WHERE coach_id = ?");
$class_stmt->execute([$coach_id]);
$classes = $class_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 2rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-color);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.3);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .user-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .user-item {
            border-left: 3px solid var(--accent-color);
            padding-left: 10px;
            margin-bottom: 10px;
        }
        
        .profile-section {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .stats-card {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-dumbbell me-2"></i>FitCoach
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= htmlspecialchars($coach['picture']) ?>" class="profile-img me-2" alt="Profile">
                            <?= htmlspecialchars($coach['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="editProfileCoach.php"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">Welcome back, <?= htmlspecialchars($coach['name']) ?>!</h1>
                    <p class="lead mb-0">Here's what's happening with your classes today.</p>
                </div>
                <div class="col-md-4 text-center">
                    <img src="<?= htmlspecialchars($coach['picture']) ?>" alt="Profile Picture" class="profile-picture">
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= count($classes) ?></div>
                    <div class="stats-label">Total Classes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">
                        <?php
                        $total_users = 0;
                        foreach ($classes as $class) {
                            $total_users += $class['current_users'];
                        }
                        echo $total_users;
                        ?>
                    </div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= htmlspecialchars($coach['experience']) ?>+</div>
                    <div class="stats-label">Years of Experience</div>
                </div>
            </div>
        </div>

        <!-- Classes Section -->
        <div class="row">
            <div class="col-lg-4">
                <div class="profile-section mb-4">
                    <h4 class="mb-3">Coach Profile</h4>
                    <p><i class="fas fa-envelope me-2"></i> <strong>Email:</strong> <?= htmlspecialchars($coach['email']) ?></p>
                    <p><i class="fas fa-venus-mars me-2"></i> <strong>Gender:</strong> <?= htmlspecialchars($coach['gender']) ?></p>
                    <p><i class="fas fa-star me-2"></i> <strong>Specialization:</strong> <?= htmlspecialchars($coach['specialization']) ?></p>
                    <p><i class="fas fa-briefcase me-2"></i> <strong>Experience:</strong> <?= htmlspecialchars($coach['experience']) ?> years</p>
                    <a href="editProfileCoach.php" class="btn btn-primary w-100 mt-3"><i class="fas fa-edit me-2"></i>Edit Profile</a>
                </div>
            </div>
            
            <div class="col-lg-8">
                <h3 class="mb-4">Your Classes</h3>
                
                <?php if (count($classes) > 0): ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?= htmlspecialchars($class['name']) ?></h5>
                                <span class="badge bg-primary"><?= $class['current_users'] ?>/<?= $class['max_users'] ?> students</span>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><strong>Description:</strong> <?= htmlspecialchars($class['description']) ?></p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="card-text"><i class="fas fa-clock me-2"></i> <strong>Schedule:</strong> <?= htmlspecialchars($class['schedule']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><i class="fas fa-dollar-sign me-2"></i> <strong>Price:</strong> $<?= htmlspecialchars($class['price']) ?></p>
                                    </div>
                                </div>
                                
                                <button class="btn btn-outline-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#users-<?= $class['id'] ?>">
                                    <i class="fas fa-users me-2"></i> View Enrolled Students (<?= $class['current_users'] ?>)
                                </button>
                                
                                <div class="collapse mt-3" id="users-<?= $class['id'] ?>">
                                    <div class="card card-body">
                                        <?php
                                        // Fetch users enrolled in this class
                                        $user_stmt = $pdo->prepare("
                                            SELECT u.id, u.name, u.age, u.gender
                                            FROM services s
                                            JOIN user u ON s.user_id = u.id
                                            WHERE s.class_id = ?
                                            AND s.status = 'active'
                                        ");
                                        $user_stmt->execute([$class['id']]);
                                        $enrolled_users = $user_stmt->fetchAll();
                                        ?>
                                        
                                        <h6>Enrolled Students:</h6>
                                        <?php if (!empty($enrolled_users)): ?>
                                            <div class="user-list">
                                                <?php foreach ($enrolled_users as $user): ?>
                                                    <div class="user-item">
                                                        <strong><?= htmlspecialchars($user['name']) ?></strong>
                                                        <div class="text-muted small">
                                                            Age: <?= htmlspecialchars($user['age']) ?> | 
                                                            Gender: <?= htmlspecialchars($user['gender']) ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">No students enrolled in this class yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Created at: <?= $class['created_at'] ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> You don't have any classes yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any custom JavaScript here if needed
    </script>
</body>
</html>