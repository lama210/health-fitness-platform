<?php
session_start();
require_once("connection.php");

// Ensure the user is a logged-in coach
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coach') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current profile info
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.gender, c.specialization, c.experience, c.picture
    FROM user u
    JOIN coach c ON u.id = c.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Coach Dashboard</title>
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
        
        .edit-profile-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .current-picture {
            margin-top: 15px;
        }
        
        .img-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            display: none;
            border: 3px solid var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="coachDashboard.php">
                <i class="fas fa-dumbbell me-2"></i>FitCoach
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= htmlspecialchars($profile['picture']) ?>" class="profile-img me-2" alt="Profile">
                            <?= htmlspecialchars($profile['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="coachDashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="edit-profile-container">
        <div class="profile-header">
            <div class="me-3">
                <?php if ($profile['picture']): ?>
                    <img src="<?= htmlspecialchars($profile['picture']) ?>" alt="Current Profile" class="profile-picture" id="currentPicture">
                <?php else: ?>
                    <div class="profile-picture bg-light d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-muted" style="font-size: 2.5rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <h2 class="mb-1">Edit Profile</h2>
                <p class="text-muted mb-0">Update your personal and professional information</p>
            </div>
        </div>

        <form action="saveProfileCoach.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($profile['name']) ?>" disabled>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($profile['email']) ?>" disabled>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select">
                        <option value="male" <?= $profile['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $profile['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= $profile['gender'] == 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="experience" class="form-label">Experience (years)</label>
                    <input type="number" name="experience" id="experience" class="form-control" 
                           value="<?= htmlspecialchars($profile['experience']) ?>" min="0" max="50">
                </div>
            </div>

            <div class="mb-4">
                <label for="specialization" class="form-label">Specialization</label>
                <input type="text" name="specialization" id="specialization" class="form-control" 
                       value="<?= htmlspecialchars($profile['specialization']) ?>">
                <small class="text-muted">E.g., Weight Training, Yoga, CrossFit, etc.</small>
            </div>

            <div class="mb-4">
                <label for="picture" class="form-label">Profile Picture</label>
                <input type="file" name="picture" id="picture" class="form-control" accept="image/*" onchange="previewImage(this)">
                <img id="imagePreview" class="img-preview" alt="Preview">
                <?php if ($profile['picture']): ?>
                    <div class="current-picture">
                        <small class="text-muted">Current Picture:</small><br>
                        <img src="<?= htmlspecialchars($profile['picture']) ?>" alt="Current" class="img-thumbnail mt-2" width="120">
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <a href="coachdash.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const current = document.getElementById('currentPicture');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    if (current) {
                        current.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>