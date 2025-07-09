<?php
require_once("connection.php");

// Fetch all services
$stmt = $pdo->query("SELECT s.id, u.name AS user_name, c.name AS class_name, s.status, s.booked_at, s.expires_at, s.coach_id, s.nutritionist_id
                     FROM services s
                     LEFT JOIN user u ON s.user_id = u.id
                     LEFT JOIN classes c ON s.class_id = c.id
                     ORDER BY s.booked_at DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Update Status (active, expired, cancelled)
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $service_id = $_POST['service_id'];

    // Update status in the services table
    $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
    $stmt->execute([$status, $service_id]);
    echo "<div class='alert alert-info'>Service status updated successfully!</div>";
}

// Handle Delete Service
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "<div class='alert alert-danger'>Service deleted successfully!</div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container">
    <h2 class="mb-4 text-center">Manage Services</h2>

    <div class="card mb-5">
        <div class="card-header bg-primary text-white">Services Overview</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Class</th>
                    <th>Coach</th>
                    <th>Nutritionist</th>
                    <th>Status</th>
                    <th>Booked At</th>
                    <th>Expires At</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= $service['user_name'] ?></td>
                        <td><?= $service['class_name'] ?></td>
                        <td><?= $service['coach_id'] ? "Coach {$service['coach_id']}" : "N/A" ?></td>
                        <td><?= $service['nutritionist_id'] ? "Nutritionist {$service['nutritionist_id']}" : "N/A" ?></td>
                        <td><?= $service['status'] ?></td>
                        <td><?= $service['booked_at'] ?></td>
                        <td><?= $service['expires_at'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" <?= $service['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="expired" <?= $service['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                                    <option value="cancelled" <?= $service['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-info">Update</button>
                            </form>
                            <a href="?delete=<?= $service['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this service?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
