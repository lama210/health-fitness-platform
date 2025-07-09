<?php
session_start();
require_once("connection.php");

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // === 1. Default Admin Login ===
    $default_email = "admin@fitlife.com";
    $default_plain_password = "admin123";

    if ($email === $default_email && $password === $default_plain_password) {
        $_SESSION['admin_email'] = $default_email;
        $_SESSION['admin_name'] = "Super Admin";
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_default_admin'] = true;
        header("Location: admin/adminDash.php");
        exit();
    }

    // === 2. Check Admin Table ===
    $adminStmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $adminStmt->execute([$email]);
    $admin = $adminStmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_default_admin'] = false;
        header("Location: admindash.php");
        exit();
    }

    // === 3. Check User Table ===
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'coach') {
            header("Location: coachdash.php");
        } elseif ($user['role'] === 'nutritionist') {
            header("Location: nutritionDash.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }

    // === 4. Invalid Credentials ===
    header("Location: login.html?error=1");
    exit();
}
?>

