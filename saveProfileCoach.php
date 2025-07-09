<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coach') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$gender = $_POST['gender'];
$specialization = $_POST['specialization'];
$experience = $_POST['experience'];

// Handle picture upload
$picturePath = null;
if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['picture']['tmp_name'];
    $fileName = $_FILES['picture']['name'];
    $destination = "uploads/" . uniqid() . '_' . $fileName;
    move_uploaded_file($fileTmpPath, $destination);
    $picturePath = $destination;
}

// Update user table
$stmt1 = $pdo->prepare("UPDATE user SET gender = ? WHERE id = ?");
$stmt1->execute([$gender, $user_id]);

// Update coach table
if ($picturePath) {
    $stmt2 = $pdo->prepare("UPDATE coach SET specialization = ?, experience = ?, picture = ? WHERE user_id = ?");
    $stmt2->execute([$specialization, $experience, $picturePath, $user_id]);
} else {
    $stmt2 = $pdo->prepare("UPDATE coach SET specialization = ?, experience = ? WHERE user_id = ?");
    $stmt2->execute([$specialization, $experience, $user_id]);
}

header("Location: coachdash.php");
exit();
?>
