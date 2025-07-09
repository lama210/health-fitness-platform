<?php
session_start();

// Check nutritionist login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'nutritionist') {
    header("Location: login.html");
    exit();
}

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get nutritionist_id from session
$userId = $_SESSION['user_id'];
$query = $conn->prepare("SELECT id FROM nutrition WHERE user_id = ?");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();
$nutritionist_id = $result->fetch_assoc()['id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update meal
    if (isset($_POST['meal_id']) && !isset($_POST['delete_single_meal'])) {
        $mealId = $_POST['meal_id'];
        $type = $_POST['type'];
        $day = $_POST['day_of_week'];
        $calories = $_POST['calories'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("UPDATE meals SET type = ?, day_of_week = ?, calories = ?, description = ? WHERE id = ? AND nutritionist_id = ?");
        $stmt->bind_param("ssisii", $type, $day, $calories, $description, $mealId, $nutritionist_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Meal updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating meal: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: nutritionDash.php");
        exit();
    }

    // Delete single meal
    if (isset($_POST['delete_single_meal']) && isset($_POST['meal_id'])) {
        $mealId = $_POST['meal_id'];
        
        $stmt = $conn->prepare("DELETE FROM meals WHERE id = ? AND nutritionist_id = ?");
        $stmt->bind_param("ii", $mealId, $nutritionist_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Meal deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting meal: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: nutritionDash.php");
        exit();
    }
}

// Display success/error messages
if (isset($_SESSION['message'])) {
    echo '<div class="notification is-success">'.$_SESSION['message'].'</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="notification is-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

// The rest of your HTML content would continue here...
// Make sure your forms in nutritionDash.php are properly configured