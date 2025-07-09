<?php
session_start();
require_once("connection.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email, age, gender FROM user WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];

    // Validation
    if (empty($name)) {
        $message = '<div class="alert alert-danger">Name is required</div>';
    } elseif ($age < 13 || $age > 120) {
        $message = '<div class="alert alert-danger">Please enter a valid age (13-120)</div>';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE user SET name = ?, age = ?, gender = ? WHERE id = ?");
            $updateStmt->execute([$name, $age, $gender, $user_id]);
            
            // Update session data if needed
            $_SESSION['user_name'] = $name;
            
            $message = '<div class="alert alert-success">Profile updated successfully!</div>';
            
            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error updating profile: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | FitConnect</title>
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
        }
        
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .profile-header {
            background: var(--gradient);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: var(--primary);
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.25rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-save:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .info-value {
            color: var(--dark);
        }
        
        .edit-icon {
            color: var(--primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .edit-icon:hover {
            color: var(--secondary);
            transform: scale(1.1);
        }
        
        .gender-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .gender-option:hover {
            border-color: var(--accent);
        }
        
        .gender-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--gray);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
            background: transparent;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-card {
                padding: 1.5rem;
            }
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
                <a href="classes.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Classes
                </a>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="mb-1"><?= htmlspecialchars($user['name'] ?? 'Your Profile') ?></h2>
            <p class="text-white-50 mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        </div>

        <!-- Messages -->
        <?= $message ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <h3 class="section-title">
                <i class="fas fa-user-edit"></i> Personal Information
            </h3>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" 
                           value="<?= htmlspecialchars($user['age'] ?? '') ?>" min="13" max="120" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Gender</label>
                    <div class="gender-options">
                        <label class="gender-option <?= ($user['gender'] ?? '') === 'Male' ? 'active' : '' ?>">
                            <input type="radio" name="gender" value="Male" 
                                   <?= ($user['gender'] ?? '') === 'Male' ? 'checked' : '' ?> required>
                            <i class="fas fa-mars me-2"></i> Male
                        </label>
                        <label class="gender-option <?= ($user['gender'] ?? '') === 'Female' ? 'active' : '' ?>">
                            <input type="radio" name="gender" value="Female" 
                                   <?= ($user['gender'] ?? '') === 'Female' ? 'checked' : '' ?>>
                            <i class="fas fa-venus me-2"></i> Female
                        </label>
                        <label class="gender-option <?= ($user['gender'] ?? '') === 'Other' ? 'active' : '' ?>">
                            <input type="radio" name="gender" value="Other" 
                                   <?= ($user['gender'] ?? '') === 'Other' ? 'checked' : '' ?>>
                            <i class="fas fa-genderless me-2"></i> Other
                        </label>
                        <label class="gender-option <?= ($user['gender'] ?? '') === 'Prefer not to say' ? 'active' : '' ?>">
                            <input type="radio" name="gender" value="Prefer not to say" 
                                   <?= ($user['gender'] ?? '') === 'Prefer not to say' ? 'checked' : '' ?>>
                            <i class="fas fa-question me-2"></i> Prefer not to say
                        </label>
                    </div>
                </div>
                
                <button type="submit" name="update" class="btn btn-save">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </form>
        </div>
        
        <!-- Additional Info Card (optional) -->
        <div class="profile-card">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i> Account Information
            </h3>
            
            <div class="info-item">
                <span class="info-label">Member Since</span>
                <span class="info-value"><?= date('F Y', strtotime($_SESSION['created_at'] ?? 'now')) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Account Status</span>
                <span class="info-value text-success">Active <i class="fas fa-check-circle"></i></span>
            </div>
            <div class="info-item">
                <span class="info-label">Classes Booked</span>
                <span class="info-value">5</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add active class to selected gender option
        document.querySelectorAll('.gender-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.gender-option').forEach(option => {
                    option.classList.remove('active');
                });
                if (this.checked) {
                    this.closest('.gender-option').classList.add('active');
                }
            });
            
            // Initialize active class on page load
            if (this.checked) {
                this.closest('.gender-option').classList.add('active');
            }
        });
    </script>
</body>
</html>