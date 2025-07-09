<?php
session_start();
require_once("connection.php");

// Ensure user is logged in before booking appointment
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$success = "";
$selected_time = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nutritionist_id = isset($_POST['nutritionist_id']) ? $_POST['nutritionist_id'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : 'nutrition';  // default to nutrition
    $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : ''; // Get selected time

    // Calculate expiration based on type
    if ($type === 'nutrition') {
        $expire_interval = 12;  // 12 days for nutrition
        $class_id = null;
        $coach_id = null;
    } else {
        $expire_interval = 30;  // 30 days for classes
        $class_id = isset($_POST['class_id']) ? $_POST['class_id'] : null;
        $coach_id = isset($_POST['coach_id']) ? $_POST['coach_id'] : null;
    }

    // Insert into services table
    $stmt = $pdo->prepare("
        INSERT INTO services (user_id, nutritionist_id, coach_id, class_id, type, booked_at, expires_at, status)
        VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active', ?)
    ");
    $stmt->execute([$user_id, $nutritionist_id, $coach_id, $class_id, $type, $expire_interval]);

    $success = "Appointment booked successfully!";
}

// Fetch all nutritionists from the database
$stmt = $pdo->query("SELECT n.*, u.name FROM nutrition n JOIN user u ON n.user_id = u.id");
$nutritionists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure that the result is an array before using foreach
if (!isset($nutritionists) || !is_array($nutritionists) || empty($nutritionists)) {
    $nutritionists = []; // Set to empty array if no nutritionists found
}

// Check if any expired appointments should be marked as expired
$currentDate = date('Y-m-d H:i:s');
$stmtUpdate = $pdo->prepare("UPDATE services SET status = 'expired' WHERE expires_at < ? AND status = 'active'");
$stmtUpdate->execute([$currentDate]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nutritionist Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Book an Appointment with a Nutritionist</h2>

    <div class="row">
        <?php if (empty($nutritionists)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    No nutritionists available at the moment. Please try again later.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($nutritionists as $n): ?>
                <?php
                    // Decode the nutritionist's schedule
                    $schedule = json_decode($n['schedule'], true);
                    $scheduleOptions = [];
                    if (is_array($schedule)) {
                        foreach ($schedule as $day => $time) {
                            $scheduleOptions[$day] = $time;
                        }
                    }
                ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?= htmlspecialchars($n['picture']) ?>" class="card-img-top" alt="Nutritionist" style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($n['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($n['specification']) ?></p>
                            <p class="card-text"><small><?= htmlspecialchars($n['experience']) ?> years of experience</small></p>
                            <div class="mb-2">
                                <strong>Schedule:</strong><br>
                                <form method="POST">
                                    <input type="hidden" name="nutritionist_id" value="<?= $n['id'] ?>">
                                    <input type="hidden" name="type" value="nutrition">
                                    <div class="form-group">
                                        <label for="daySelect">Select Day</label>
                                        <select class="form-control" id="daySelect" name="day">
                                            <?php foreach ($scheduleOptions as $day => $time): ?>
                                                <option value="<?= $day ?>"><?= $day ?>: <?= $time['start'] ?> - <?= $time['end'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label for="selected_time">Select Time</label>
                                        <select class="form-control" name="selected_time" id="selected_time">
                                            <?php foreach ($scheduleOptions as $day => $time): ?>
                                                <option value="<?= $time['start'] ?>"><?= $time['start'] ?> - <?= $time['end'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-3">Book Appointment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
