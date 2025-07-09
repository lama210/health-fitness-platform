<?php
require_once("connection.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = trim($_POST['first_name'] . ' ' . $_POST['last_name']);
    $age = 18;
    $gender = 'not-specified';
    $email = trim($_POST['your_email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        echo "<script>alert('Please fill in all required fields.');</script>";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            echo "<script>alert('Email is already registered.');</script>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into user table
            $stmt = $pdo->prepare("INSERT INTO user (name, age, gender, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $success = $stmt->execute([$name, $age, $gender, $email, $hashed_password, $role]);

            if ($success) {
                // Get the ID of the newly inserted user
                $user_id = $pdo->lastInsertId();

                // If the user is a coach, insert into coach table too
                if ($role === 'coach') {
                    $defaultSpecialization = 'General Fitness';
                    $defaultExperience = 0;
                    $defaultPicture = 'default.png';
                
                    $stmtCoach = $pdo->prepare("INSERT INTO coach (user_id, specialization, experience, picture) VALUES (?, ?, ?, ?)");
                    $stmtCoach->execute([$user_id, $defaultSpecialization, $defaultExperience, $defaultPicture]);
                }
                
                // Handle nutritionist role
                if ($role === 'nutritionist') {
                    $defaultQualification = 'Certified Nutritionist';
                    $defaultExperience = 0;
                    $defaultPicture = 'default.png';
                    $defaultSchedule = 'Not set';

                    $stmtNutrition = $pdo->prepare("INSERT INTO nutrition (user_id, specification, experience, picture,schedule) VALUES (?, ?, ?, ?,?)");
                    $stmtNutrition->execute([$user_id, $defaultQualification, $defaultExperience, $defaultPicture,$defaultSchedule]);
                }
                

                echo "<script>alert('Registration successful!'); window.location.href='login.html';</script>";
            } else {
                echo "<script>alert('Something went wrong. Please try again.');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="register.css">
<Style>
            body{
    background-image: url(images/reg.jpg);
      background-size: cover; /* This property ensures that the background image covers the entire body */
      background-repeat: no-repeat; /* This property prevents the background image from repeating */
      margin: 0;
} 
.book-style-container {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    max-width: 500px;
    margin: 1rem auto;
}

.book-info {
    flex: 1;
    padding: 30px;
    border-radius: 8px 0 0 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background-color: #2211dc;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.book-info h2 {
    font-weight: bold;
    margin-bottom: 30px;
}

.book-info p {
    margin-bottom: 20px;
}

.custom-btn {
    background-color: #fff;
    color: #007bff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    animation: fadeIn 1s ease;
}

.custom-btn:hover {
    background-color: #f0f0f0;
    animation: scaleButton 0.3s ease forwards; /* Apply scale animation on hover */
}

.registration-form {
    flex: 1;
    padding: 30px;
    border-radius: 0 8px 8px 0;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background-color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.registration-form h2 {
    font-weight: bold;
    color: #007bff;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: bold;
}

.form-control {
    border-radius: 4px;
}

.checkbox-label {
    font-weight: normal;
}

.register-btn {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    animation: fadeIn 1s ease;
}

.register-btn:hover {
    background-color: #0056b3;
    animation: scaleButton 0.3s ease forwards; /* Apply scale animation on hover */
}



@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes scaleButton {
    from {
        transform: scale(1);
    }
    to {
        transform: scale(1.1);
    }
}
.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #666;
    z-index: 2;
}

.password-toggle:hover {
    color: #333;
}

/* Add padding to prevent text overlap */
#password {
    padding-right: 40px;
}

</style>
</head>
<body>

<div class="container mt-5">
    <div class="book-style-container">
       
        <div class="registration-form">
        <h2><i class="fas fa-user-plus"></i> Register Form</h2>
            <form id="registration-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                            method="post">
                <div class="form-group">
                    <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name"><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="your_email"><i class="fas fa-envelope"></i> Your Email</label>
                    <input type="email" class="form-control" id="your_email" name="your_email" required>
                </div>
                <div class="form-group password-wrapper">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                </div>
                
                <div class="form-group">
                    <label for="role"><i class="fas fa-user-tag"></i> Select Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="" disabled selected>Select a role</option>
                        <option value="user">User</option>
                        <option value="coach">Coach</option>
                        <option value="nutritionist">Nutritionist</option>
                    </select>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="agree_terms" required>
                    <label class="form-check-label checkbox-label" for="agree_terms">I agree to the <a href="term.html" class="text-primary">Terms and Conditions</a></label>
                </div>

                <button type="submit" class="btn register-btn" name="register" value="Register"><i class="fas fa-user-plus"></i> Register</button>
                
            </form>
            
            </div>
            </div>
            </div>
            <script>
                document.getElementById('togglePassword').addEventListener('click', function(e) {
                    const password = document.getElementById('password');
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                    this.classList.toggle('fa-eye');
                });
                </script>

</body>
</html>
        
         