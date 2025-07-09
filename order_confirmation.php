<?php
session_start();
require_once("connection.php");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Fetch the most recent purchase for this user
$stmt = $pdo->prepare("
    SELECT * FROM purchases 
    WHERE user_id = ? 
    ORDER BY purchase_date DESC 
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);

// If no purchase found, redirect to shop
if (!$purchase) {
    header("Location: shop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | NutriCore Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2rem;
            text-align: center;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }
        
        .confirmation-title {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .confirmation-text {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .order-details {
            background: rgba(76, 201, 240, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <i class="fas fa-check-circle confirmation-icon"></i>
            <h1 class="confirmation-title">Order Confirmed!</h1>
            <p class="confirmation-text">Thank you for your purchase. Your order has been received and is being processed.</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value">#<?= $purchase['purchase_id'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?= date('F j, Y', strtotime($purchase['purchase_date'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value">$<?= number_format($purchase['total_price'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">Credit Card</span>
                </div>
            </div>
            
            <p class="confirmation-text">We've sent a confirmation email to your registered address.</p>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                </a>
             
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>