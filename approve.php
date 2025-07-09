<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'C:/xampp/htdocs/SMS/PHPMailer/src/Exception.php';
require 'C:\xampp\htdocs\SMS\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\SMS\PHPMailer\src\SMTP.php';

// Database connection details
$servername = "localhost";
$username = "root";
$password = "your_password";
$dbname = "service management system";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if ID parameter is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Update user approval status to TRUE
    $sql = "UPDATE users SET approved = TRUE WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        // Retrieve user's email address from the database
        $sql_select_email = "SELECT email FROM users WHERE id = $id";
        $result_select_email = mysqli_query($conn, $sql_select_email);
        $row_select_email = mysqli_fetch_assoc($result_select_email);
        $user_email = $row_select_email['email'];

        // Send email notification to the user
        $mail = new PHPMailer(true);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hussien3416@gmail.com'; // Your Gmail email address
        $mail->Password   = 'fvlzylvzerayyedq'; // Your Gmail password or App Password
        $mail->SMTPSecure = 'tls'; // Use TLS
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('hussien3416@gmail.com', 'Your Service Platform Admin'); // Your name and email address
        $mail->addAddress($user_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Approved';
        $mail->Body    = 'Your account has been approved. You can now access our services.';

        // Send email
        $mail->send();

        // Redirect back to admin dashboard after approval
        header("Location: admindash.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    // Redirect to admin dashboard if ID parameter is not set
    header("Location: admindash.php");
    exit();
}

// Close connection
mysqli_close($conn);
?>
