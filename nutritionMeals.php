<?php
session_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check nutritionist login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'nutritionist') {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Get nutritionist_id from nutrition table
$query = $conn->prepare("SELECT id FROM nutrition WHERE user_id = ?");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("Nutritionist not found.");
}
$nutritionist_id = $result->fetch_assoc()['id'];

// Fetch first meal name if exists
$nameQuery = $conn->prepare("SELECT name FROM meals WHERE nutritionist_id = ? LIMIT 1");
$nameQuery->bind_param("i", $nutritionist_id);
$nameQuery->execute();
$nameResult = $nameQuery->get_result();
$currentMeal = $nameResult->fetch_assoc();

// Fetch all meals by this nutritionist
$stmt = $conn->prepare("SELECT * FROM meals WHERE nutritionist_id = ?");
$stmt->bind_param("i", $nutritionist_id);
$stmt->execute();
$mealResult = $stmt->get_result();

// Define day order for sorting
$daysOrder = [
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
    'Sunday' => 7
];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert a new meal
    if (!isset($_POST['update_name']) && !isset($_POST['delete_meal']) && isset($_POST['type']) && !isset($_POST['meal_id'])) {
        // Handle name: from DB or from POST
        if (!$currentMeal || !isset($currentMeal['name'])) {
            if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
                $name = trim($_POST['name']);
            } else {
                echo "<div class='notification is-danger'>Please enter a meal name.</div>";
                exit();
            }
        } else {
            $name = $currentMeal['name'];
        }

        $type = $_POST['type'];
        $day = $_POST['day_of_week'];
        $calories = $_POST['calories'];
        $description = $_POST['description'];

        // Check for duplicates
        $checkDuplicate = $conn->prepare("SELECT * FROM meals WHERE nutritionist_id = ? AND name = ? AND type = ? AND day_of_week = ?");
        $checkDuplicate->bind_param("isss", $nutritionist_id, $name, $type, $day);
        $checkDuplicate->execute();
        $duplicateResult = $checkDuplicate->get_result();

        if ($duplicateResult->num_rows > 0) {
            echo "<div class='notification is-danger'>This type of meal already exists for this day under this meal name.</div>";
        } else {
            // Safe to insert
            $insertStmt = $conn->prepare("INSERT INTO meals (nutritionist_id, name, type, day_of_week, calories, description) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("isssis", $nutritionist_id, $name, $type, $day, $calories, $description);
            $insertStmt->execute();
            echo "<div class='notification is-success'>Meal added successfully.</div>";
            header("Refresh:0");
            exit();
        }
    }

    // Updating meal name
    if (isset($_POST['update_name_submit']) && isset($_POST['name'])) {
        $newName = trim($_POST['name']);
        $oldName = isset($currentMeal['name']) ? $currentMeal['name'] : '';

        // Check if there are any meals for this nutritionist
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM meals WHERE nutritionist_id = ?");
        $checkStmt->bind_param("i", $nutritionist_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();

        // Case when there are no meals for the nutritionist (empty table)
        if ($checkRow['count'] == 0) {
            // Insert the first meal with the new name, and set day_of_week as NULL or a placeholder value
            $insertStmt = $conn->prepare("INSERT INTO meals (nutritionist_id, name, type, day_of_week, calories, description) VALUES (?, ?, 'Placeholder', NULL, 0, 'Initial placeholder')");
            $insertStmt->bind_param("is", $nutritionist_id, $newName);
            $insertStmt->execute();

            echo "<div class='notification is-success'>Meal name created successfully.</div>";
            header("Refresh:0");
            exit();
        } else {
            // Check if any meal with the new name already exists
            $checkNameStmt = $conn->prepare("SELECT COUNT(*) as count FROM meals WHERE nutritionist_id = ? AND name = ?");
            $checkNameStmt->bind_param("is", $nutritionist_id, $newName);
            $checkNameStmt->execute();
            $checkNameResult = $checkNameStmt->get_result();
            $checkNameRow = $checkNameResult->fetch_assoc();

            if ($checkNameRow['count'] > 0 && $newName != $oldName) {
                // If name already exists, show an error
                echo "<div class='notification is-danger'>This meal name already exists. Please choose another name.</div>";
            } else {
                // Update the existing meal row with the new name
                $updateStmt = $conn->prepare("UPDATE meals SET name = ? WHERE nutritionist_id = ? AND name = ?");
                $updateStmt->bind_param("sis", $newName, $nutritionist_id, $oldName);
                $updateStmt->execute();

                echo "<div class='notification is-success'>Meal name updated successfully.</div>";
                header("Refresh:0");
                exit();
            }
        }
    }

    // Delete all meals with the current meal name
    if (isset($_POST['delete_meal'])) {
        $deleteStmt = $conn->prepare("DELETE FROM meals WHERE nutritionist_id = ? AND name = ?");
        $deleteStmt->bind_param("is", $nutritionist_id, $currentMeal['name']);
        $deleteStmt->execute();
        echo "<div class='notification is-success'>Meal deleted successfully.</div>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Update a single meal
    if (isset($_POST['update_single_meal']) && isset($_POST['meal_id'])) {
        $mealId = $_POST['meal_id'];
        $type = $_POST['type'];
        $day = $_POST['day_of_week'];
        $calories = $_POST['calories'];
        $description = $_POST['description'];

        $updateStmt = $conn->prepare("UPDATE meals SET type = ?, day_of_week = ?, calories = ?, description = ? WHERE id = ? AND nutritionist_id = ?");
        $updateStmt->bind_param("sssisi", $type, $day, $calories, $description, $mealId, $nutritionist_id);
        
        if ($updateStmt->execute()) {
            echo "<div class='notification is-success'>Meal updated successfully.</div>";
            header("Refresh:0");
            exit();
        } else {
            echo "<div class='notification is-danger'>Error updating meal.</div>";
        }
    }

    // Delete a single meal
    if (isset($_POST['delete_single_meal']) && isset($_POST['meal_id'])) {
        $mealId = $_POST['meal_id'];
        
        $deleteStmt = $conn->prepare("DELETE FROM meals WHERE id = ? AND nutritionist_id = ?");
        $deleteStmt->bind_param("ii", $mealId, $nutritionist_id);
        
        if ($deleteStmt->execute()) {
            echo "<div class='notification is-success'>Meal deleted successfully.</div>";
            header("Refresh:0");
            exit();
        } else {
            echo "<div class='notification is-danger'>Error deleting meal.</div>";
        }
    }

    // Show modal form to update meal name
    if (isset($_POST['update_name'])) {
        echo '
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Update Meal Name</p>
                    <button class="delete" aria-label="close" onclick="closeModal()"></button>
                </header>
                <section class="modal-card-body">
                    <form method="POST">
                        <div class="field">
                            <label class="label">New Meal Name</label>
                            <div class="control">
                                <input class="input" type="text" name="name" required>
                            </div>
                        </div>
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <button type="submit" name="update_name_submit" class="button is-success">Update Name</button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #48c78e;
            --primary-dark: #3ec483;
            --secondary: #485fc7;
            --light: #f5f5f5;
            --dark: #363636;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem;
        }
        
        .card-header-title {
            color: white;
        }
        
        .meal-name-badge {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
        }
        
        .table {
            width: 100%;
        }
        
        .table th {
            background-color: var(--light);
            font-weight: 600;
        }
        
        .table tr:hover {
            background-color: #f9f9f9;
        }
        
        .tag {
            font-weight: 500;
        }
        
        .is-breakfast {
            background-color: #ffe08a;
            color: #946c00;
        }
        
        .is-lunch {
            background-color: #48c78e;
            color: white;
        }
        
        .is-dinner {
            background-color: #485fc7;
            color: white;
        }
        
        .is-snack {
            background-color: #f14668;
            color: white;
        }
        
        .day-chip {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            background-color: #f0f0f0;
            font-weight: 500;
            margin-right: 0.5rem;
        }
        
        .modal-card {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .has-text-primary {
            color: var(--primary) !important;
        }
        
        .button.is-primary {
            background-color: var(--primary);
        }
        
        .button.is-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .hero.is-primary {
            background-color: var(--primary);
        }
        
        .day-group-header {
            background-color: #f5f5f5 !important;
        }
        
        .total-calories {
            background-color: #f0f7f4 !important;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 0.5rem;
            }
            
            .columns {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .column {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h1 class="title">
                                <i class="fas fa-utensils mr-2"></i> Nutritionist Dashboard
                            </h1>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <a href="logout.php" class="button is-light">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="dashboard-container">
        <!-- Current Meal Name -->
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-tag mr-2"></i> Current Meal Plan
                </p>
            </div>
            <div class="card-content">
                <?php if ($currentMeal && isset($currentMeal['name'])): ?>
                    <div class="content">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <span class="meal-name-badge">
                                        <i class="fas fa-utensils mr-2"></i>
                                        <?= htmlspecialchars($currentMeal['name']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <form method="POST" class="buttons">
                                        <button type="submit" name="update_name" class="button is-warning">
                                            <i class="fas fa-edit mr-2"></i> Rename
                                        </button>
                                        <button type="submit" name="delete_meal" class="button is-danger">
                                            <i class="fas fa-trash mr-2"></i> Delete All
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="content">
                        <div class="notification is-light">
                            <p>No meal plan exists yet. Please create one:</p>
                        </div>
                        <form method="POST">
                            <div class="field has-addons">
                                <div class="control is-expanded">
                                    <input
                                        class="input"
                                        type="text"
                                        name="name"
                                        placeholder="Enter new meal plan name"
                                        required
                                    >
                                </div>
                                <div class="control">
                                    <button type="submit" name="update_name_submit" class="button is-primary">
                                        <i class="fas fa-plus mr-2"></i> Create
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Meal Form -->
        <?php if ($currentMeal): ?>
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Meal
                </p>
            </div>
            <div class="card-content">
                <form method="POST">
                    <div class="columns">
                        <div class="column is-3">
                            <div class="field">
                                <label class="label">Meal Type</label>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select name="type" required>
                                            <option value="Breakfast">Breakfast</option>
                                            <option value="Lunch">Lunch</option>
                                            <option value="Dinner">Dinner</option>
                                            <option value="Snack">Snack</option>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-utensil-spoon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="column is-3">
                            <div class="field">
                                <label class="label">Day of Week</label>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select name="day_of_week" required>
                                            <?php
                                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                                            foreach ($days as $day) {
                                                echo "<option value='$day'>$day</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-calendar-day"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="column is-2">
                            <div class="field">
                                <label class="label">Calories</label>
                                <div class="control has-icons-left">
                                    <input class="input" type="number" name="calories" placeholder="e.g., 500" required>
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-fire"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Description</label>
                        <div class="control">
                            <textarea class="textarea" name="description" placeholder="Describe the meal (ingredients, preparation, etc.)" rows="2" required></textarea>
                        </div>
                    </div>
                    
                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <i class="fas fa-plus mr-2"></i> Add Meal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Meal Table -->
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-list mr-2"></i> Meal Plan Details
                </p>
            </div>
            <div class="card-content">
                <div class="content">
                    <?php if ($mealResult->num_rows > 0): ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-hoverable">
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
                                        // Reset pointer to beginning of result set
                                        $mealResult->data_seek(0);
                                        
                                        // Initialize an empty array to group meals by day
                                        $mealsByDay = [];

                                        // Loop through the results and group meals by 'day_of_week'
                                        while ($row = $mealResult->fetch_assoc()) {
                                            // Skip rows with NULL values
                                            if (is_null($row['type']) || is_null($row['day_of_week']) || is_null($row['description']) || is_null($row['calories']) || is_null($row['created_at'])) {
                                                continue;
                                            }
                                            
                                            // Group by day_of_week
                                            $mealsByDay[$row['day_of_week']][] = $row;
                                        }

                                        // Sort the days in correct order before displaying
                                        uksort($mealsByDay, function($a, $b) use ($daysOrder) {
                                            return $daysOrder[$a] - $daysOrder[$b];
                                        });

                                        // Now loop through each day in correct order and render meals for that day
                                        foreach ($mealsByDay as $day => $meals):
                                            // Calculate total calories for the day
                                            $totalCalories = 0;
                                            foreach ($meals as $meal) {
                                                $totalCalories += $meal['calories'];
                                            }
                                    ?>
                                        <!-- Group by day -->
                                        <tr class="day-group-header">
                                            <td colspan="6" style="font-weight: bold; background-color: #f5f5f5;"><?= htmlspecialchars($day) ?></td>
                                        </tr>
                                        <?php foreach ($meals as $row): ?>
                                            <tr>
                                                <td>
                                                   <span class="tag <?= 
    strtolower($row['type']) === 'breakfast' ? 'is-breakfast' : 
    (strtolower($row['type']) === 'lunch' ? 'is-lunch' : 
    (strtolower($row['type']) === 'dinner' ? 'is-dinner' : 
    (strtolower($row['type']) === 'snack' ? 'is-snack' : ''))) ?>"> 
    <?= htmlspecialchars($row['type']) ?>
</span>
                                                </td>
                                                <td>
                                                    <span class="day-chip">
                                                        <?= htmlspecialchars($row['day_of_week']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($row['description']) ?></td>
                                                <td>
                                                    <span class="has-text-weight-semibold">
                                                        <?= htmlspecialchars($row['calories']) ?>
                                                    </span>
                                                    <span class="has-text-grey is-size-7">kcal</span>
                                                </td>
                                                <td>
                                                    <span class="has-text-grey">
                                                        <?= date('M j, Y', strtotime($row['created_at'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="buttons are-small">
                                                        <button class="button is-info is-light edit-meal" 
                                                            data-id="<?= $row['id'] ?>"
                                                            data-type="<?= htmlspecialchars($row['type']) ?>"
                                                            data-day="<?= htmlspecialchars($row['day_of_week']) ?>"
                                                            data-calories="<?= htmlspecialchars($row['calories']) ?>"
                                                            data-description="<?= htmlspecialchars($row['description']) ?>">
                                                            <span class="icon">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </button>
                                                        <button class="button is-danger is-light delete-meal" 
                                                            data-id="<?= $row['id'] ?>">
                                                            <span class="icon">
                                                                <i class="fas fa-trash"></i>
                                                            </span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <!-- Display Total Calories for the Day -->
                                        <tr class="total-calories">
                                            <td colspan="3" style="font-weight: bold;">Total Calories for <?= htmlspecialchars($day) ?>:</td>
                                            <td colspan="3" class="has-text-weight-semibold"><?= $totalCalories ?> kcal</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="notification is-light">
                            No meals found in this meal plan. Add your first meal above.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Edit Meal Modal -->
        <div class="modal" id="editMealModal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Edit Meal</p>
                    <button class="delete" aria-label="close" onclick="closeModal('editMealModal')"></button>
                </header>
                <section class="modal-card-body">
                    <form id="editMealForm" method="POST">
                        <input type="hidden" name="meal_id" id="editMealId">
                        <div class="field">
                            <label class="label">Meal Type</label>
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="type" id="editMealType" required>
                                        <option value="Breakfast">Breakfast</option>
                                        <option value="Lunch">Lunch</option>
                                        <option value="Dinner">Dinner</option>
                                        <option value="Snack">Snack</option>
                                    </select>
                                </div>
                                <span class="icon is-small is-left">
                                    <i class="fas fa-utensil-spoon"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Day of Week</label>
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="day_of_week" id="editMealDay" required>
                                        <?php
                                        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                                        foreach ($days as $day) {
                                            echo "<option value='$day'>$day</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <span class="icon is-small is-left">
                                    <i class="fas fa-calendar-day"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Calories</label>
                            <div class="control has-icons-left">
                                <input class="input" type="number" name="calories" id="editMealCalories" placeholder="e.g., 500" required>
                                <span class="icon is-small is-left">
                                    <i class="fas fa-fire"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Description</label>
                            <div class="control">
                                <textarea class="textarea" name="description" id="editMealDescription" placeholder="Describe the meal (ingredients, preparation, etc.)" rows="3" required></textarea>
                            </div>
                        </div>
                    </form>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success" id="saveMealChanges">Save changes</button>
                    <button class="button" onclick="closeModal('editMealModal')">Cancel</button>
                </footer>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal" id="deleteMealModal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Confirm Deletion</p>
                    <button class="delete" aria-label="close" onclick="closeModal('deleteMealModal')"></button>
                </header>
                <section class="modal-card-body">
                    <p>Are you sure you want to delete this meal? This action cannot be undone.</p>
                </section>
                <footer class="modal-card-foot">
                    <form id="deleteMealForm" method="POST">
                        <input type="hidden" name="meal_id" id="deleteMealId">
                        <button class="button is-danger" type="submit" name="delete_single_meal">Delete</button>
                        <button class="button" type="button" onclick="closeModal('deleteMealModal')">Cancel</button>
                    </form>
                </footer>
            </div>
        </div>

        <script>
            // Edit Meal functionality
            document.querySelectorAll('.edit-meal').forEach(button => {
                button.addEventListener('click', function() {
                    const mealId = this.getAttribute('data-id');
                    const mealType = this.getAttribute('data-type');
                    const mealDay = this.getAttribute('data-day');
                    const mealCalories = this.getAttribute('data-calories');
                    const mealDescription = this.getAttribute('data-description');
                    
                    document.getElementById('editMealId').value = mealId;
                    document.getElementById('editMealType').value = mealType;
                    document.getElementById('editMealDay').value = mealDay;
                    document.getElementById('editMealCalories').value = mealCalories;
                    document.getElementById('editMealDescription').value = mealDescription;
                    
                    document.getElementById('editMealModal').classList.add('is-active');
                });
            });
            
            // Delete Meal functionality
            document.querySelectorAll('.delete-meal').forEach(button => {
                button.addEventListener('click', function() {
                    const mealId = this.getAttribute('data-id');
                    document.getElementById('deleteMealId').value = mealId;
                    document.getElementById('deleteMealModal').classList.add('is-active');
                });
            });
            
            // Save changes for edit
            document.getElementById('saveMealChanges').addEventListener('click', function() {
                // Add the hidden field for update action
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'update_single_meal';
                hiddenInput.value = '1';
                document.getElementById('editMealForm').appendChild(hiddenInput);
                
                // Submit the form
                document.getElementById('editMealForm').submit();
            });
            
            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove('is-active');
            }
        </script>
    </div>
</body>
</html>