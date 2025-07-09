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

// Check if user is a nutritionist
$stmt = $pdo->prepare("SELECT n.*, u.name, u.email, u.age, u.gender 
                       FROM nutrition n 
                       JOIN user u ON n.user_id = u.id 
                       WHERE n.user_id = ?");
$stmt->execute([$user_id]);
$nutritionist = $stmt->fetch();

if (!$nutritionist) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $specification = trim($_POST['specification']);
    $experience = trim($_POST['experience']);
    
    // File upload handling
    $picturePath = $nutritionist['picture']; // Default to existing picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = basename($_FILES['profile_picture']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedTypes)) {
            $uploadDir = 'uploads/';
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;

            // Check if the file is a valid image
            $check = getimagesize($fileTmpPath);
            if ($check !== false && move_uploaded_file($fileTmpPath, $targetPath)) {
                // Update picture path
                $picturePath = $targetPath;

                // Delete old picture if needed
                if (!empty($nutritionist['picture']) && !str_contains($nutritionist['picture'], 'default') && file_exists($nutritionist['picture'])) {
                    unlink($nutritionist['picture']);
                }
            } else {
                $message = '<div class="alert alert-danger">Invalid image file.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Only JPG, JPEG, PNG & GIF files are allowed.</div>';
        }
    }

    // Validation checks
    if (empty($name)) {
        $message = '<div class="alert alert-danger">Name is required</div>';
    } elseif ($age < 18 || $age > 120) {
        $message = '<div class="alert alert-danger">Please enter a valid age (18-120)</div>';
    } elseif (empty($specification)) {
        $message = '<div class="alert alert-danger">Specialization is required</div>';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Update user table
            $updateUser = $pdo->prepare("UPDATE user SET name = ?, age = ?, gender = ? WHERE id = ?");
            $updateUser->execute([$name, $age, $gender, $user_id]);

            // Update nutrition table with new data
            $updateNutrition = $pdo->prepare("UPDATE nutrition SET specification = ?, experience = ?, picture = ? WHERE user_id = ?");
            $updateNutrition->execute([$specification, $experience, $picturePath, $user_id]);

            // Commit the transaction
            $pdo->commit();

            // Update session data
            $_SESSION['user_name'] = $name;

            $message = '<div class="alert alert-success">Profile updated successfully!</div>';

            // Refresh profile data
            $refresh = $pdo->prepare("SELECT n.*, u.name, u.email, u.age, u.gender 
                                      FROM nutrition n 
                                      JOIN user u ON n.user_id = u.id 
                                      WHERE n.user_id = ?");
            $refresh->execute([$user_id]);
            $nutritionist = $refresh->fetch();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Error updating profile: ' . $e->getMessage() . '</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Profile | FitConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --secondary: #607D8B;
            --accent: #8BC34A;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --gradient: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .profile-header {
            background: var(--gradient);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
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
        
        .profile-pic-container {
            position: relative;
            width: 150px;
            margin: 0 auto 1rem;
        }
        
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: var(--primary-dark);
        }
        
        .profile-pic-upload {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .profile-pic-upload:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }
        
        #profile-picture-input {
            display: none;
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
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(139, 195, 74, 0.15);
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
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .badge-specialty {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-card {
                padding: 1.5rem;
            }
            
            .profile-pic {
                width: 120px;
                height: 120px;
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-leaf me-2"></i>FitNutrition
            </a>
            <div class="d-flex align-items-center">
                <a href="nutritionDash.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <!-- Profile Header -->
           <form method="POST" enctype="multipart/form-data">
        <div class="profile-header">
            <div class="profile-pic-container">
                <?php if (!empty($nutritionist['picture'])): ?>
<img src="<?= htmlspecialchars($nutritionist['picture']) . '?t=' . time() ?>" class="profile-pic" alt="Profile Picture" id="profile-pic-display">
                <?php else: ?>
                    <div class="profile-pic" id="profile-pic-display">
                        <i class="fas fa-user-md"></i>
                    </div>
                <?php endif; ?>
                <div class="profile-pic-upload" id="upload-trigger">
                    <i class="fas fa-camera"></i>
                </div>
                <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*">
            </div>
            <h2 class="mb-1"><?= htmlspecialchars($nutritionist['name']) ?></h2>
            <span class="badge-specialty">
                <i class="fas fa-certificate"></i>
                <?= htmlspecialchars($nutritionist['specification']) ?>
            </span>
        </div>

        <!-- Messages -->
        <?= $message ?>

        <!-- Profile Form -->
      
            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-user-edit"></i> Personal Information
                </h3>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($nutritionist['name']) ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" 
                               value="<?= htmlspecialchars($nutritionist['email']) ?>" readonly>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" 
                               value="<?= htmlspecialchars($nutritionist['age']) ?>" min="18" max="120" required>
                    </div>
                    
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Gender</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="male" 
                                       value="Male" <?= $nutritionist['gender'] === 'Male' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="male">
                                    <i class="fas fa-mars me-1"></i> Male
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="female" 
                                       value="Female" <?= $nutritionist['gender'] === 'Female' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="female">
                                    <i class="fas fa-venus me-1"></i> Female
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="other" 
                                       value="Other" <?= $nutritionist['gender'] === 'Other' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="other">
                                    <i class="fas fa-genderless me-1"></i> Other
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Nutritionist Information -->
            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-certificate"></i> Professional Information
                </h3>
                
                <div class="mb-3">
                    <label for="specification" class="form-label">Specialization</label>
                    <input type="text" class="form-control" id="specification" name="specification" 
                           value="<?= htmlspecialchars($nutritionist['specification']) ?>" required>
                    <div class="form-text">E.g., Sports Nutrition, Pediatric Nutrition, Weight Management</div>
                </div>
                
                <div class="mb-3">
                    <label for="experience" class="form-label">Experience</label>
                    <textarea class="form-control" id="experience" name="experience" rows="4"><?= htmlspecialchars($nutritionist['experience']) ?></textarea>
                    <div class="form-text">Describe your professional background and expertise</div>
                </div>
            </div>
            
            <button type="submit" name="update" class="btn btn-save">
                <i class="fas fa-save me-2"></i> Save Profile
            </button>
        </form>
        
        <!-- Stats Card -->
        <div class="profile-card">
            <h3 class="section-title">
                <i class="fas fa-chart-line"></i> Nutritionist Statistics
            </h3>
            
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <div class="p-3 bg-light rounded">
                        <h4 class="text-primary">24</h4>
                        <p class="mb-0 text-muted">Clients</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 bg-light rounded">
                        <h4 class="text-primary">156</h4>
                        <p class="mb-0 text-muted">Sessions</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 bg-light rounded">
                        <h4 class="text-primary">4.9</h4>
                        <p class="mb-0 text-muted">Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('upload-trigger').addEventListener('click', function() {
            document.getElementById('profile-picture-input').click();
        });

        document.getElementById('profile-picture-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const profilePicDisplay = document.getElementById('profile-pic-display');
                    
                    if (profilePicDisplay.tagName === 'IMG') {
                        profilePicDisplay.src = e.target.result;
                    } else {
                        // Replace the div with an img tag
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.className = 'profile-pic';
                        newImg.id = 'profile-pic-display';
                        newImg.alt = 'Profile Picture';
                        profilePicDisplay.parentNode.replaceChild(newImg, profilePicDisplay);
                    }
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>