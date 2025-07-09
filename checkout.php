<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$userId = $_SESSION['user_id'];

// Fetch user details
$userStmt = $pdo->prepare("SELECT name, email, age, gender FROM user WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Fetch cart items
$cartItems = [];
$total = 0;
$itemCount = 0;

$stmt = $pdo->prepare("
    SELECT c.*, s.productName, s.price, s.picturePath 
    FROM cart c 
    JOIN Shop s ON c.product_id = s.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cartProducts as $product) {
    $subtotal = $product['quantity'] * $product['price'];
    $total += $subtotal;
    $itemCount += $product['quantity'];
    $cartItems[] = [
        'id' => $product['product_id'],
        'name' => $product['productName'],
        'price' => $product['price'],
        'quantity' => $product['quantity'],
        'subtotal' => $subtotal,
        'picturePath' => $product['picturePath']
    ];
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceedCheck'])) {
    try {
        $pdo->beginTransaction();
        $deliveryFee = 3.00;
        $orderTotal = $total + $deliveryFee;
        $purchaseDate = date('Y-m-d H:i:s');
        
        // Insert each cart item into purchases
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO purchases 
                (user_id, product_id, quantity, total_price, purchase_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $item['id'],
                $item['quantity'],
                $item['subtotal'], // Store item subtotal without delivery fee
                $purchaseDate
            ]);
        }
        
        // Clear the cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $_SESSION['cart_count'] = 0;
        
        $pdo->commit();

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hussien3416@gmail.com';
            $mail->Password   = 'mgiqukrzhhpqezwg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('hussien3416@gmail.com', 'NutriCore Store');
            $mail->addAddress($user['email']);
            
            $formattedDate = date("F j, Y, g:i a", strtotime($purchaseDate));
            $formattedTotal = number_format($orderTotal, 2);

            $mail->isHTML(true);
            $mail->Subject = 'Order Confirmation';
            $mail->Body = "
                <h2>Thank you for your order!</h2>
                <p>Order Date: $formattedDate</p>
                <p>Order Total: $$formattedTotal</p>
                <h3>Order Details:</h3>
                <ul>
            ";
            
            foreach ($cartItems as $item) {
                $mail->Body .= "<li>{$item['name']} - {$item['quantity']} x \${$item['price']} = \${$item['subtotal']}</li>";
            }
            
            $mail->Body .= "
                </ul>
                <p>Delivery Fee: \$3.00</p>
                <p><strong>Total Amount: $$formattedTotal</strong></p>
                <p>Your order will be delivered within 3-5 business days.</p>
                <p>Thank you for shopping with us!</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        header("Location: order_confirmation.php");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error processing your order: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | NutriCore Store</title>
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
            --danger: #ff6b6b;
            --gray: #6c757d;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--dark);
        }
        
        /* Navigation */
        .store-navbar {
            background: white;
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.08);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.25rem;
        }
        
        .nav-icon {
            font-size: 1.5rem;
        }
        
        /* Checkout Container */
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .checkout-header {
            margin-bottom: 2.5rem;
        }
        
        .checkout-title {
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 2rem;
        }
        
        /* Checkout Steps */
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-bottom: 3rem;
        }
        
        .progress-line {
            position: absolute;
            height: 3px;
            width: 100%;
            background: #e9ecef;
            z-index: 1;
        }
        
        .progress-bar {
            height: 100%;
            width: 33%;
            background: var(--primary);
            transition: all 0.3s ease;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            border: 3px solid white;
        }
        
        .step.active .step-number {
            background: var(--primary);
            color: white;
        }
        
        .step.completed .step-number {
            background: var(--success);
            color: white;
        }
        
        .step-label {
            color: var(--gray);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .step.active .step-label,
        .step.completed .step-label {
            color: var(--dark);
            font-weight: 600;
        }
        
        /* Checkout Sections */
        .checkout-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
        }
        
        /* Payment Method */
        .payment-method {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method.active {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }
        
        .payment-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-title {
            font-weight: 600;
            color: var(--dark);
        }
        
        .payment-description {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Order Summary */
        .order-summary {
            position: sticky;
            top: 120px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .item-quantity {
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        .item-total {
            font-weight: 600;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 1.5rem 0;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        /* Checkout Button */
        .checkout-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .checkout-btn:hover {
            background: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        .secure-checkout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 1rem;
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        /* Error Message */
        .alert-danger {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .order-summary {
                position: static;
                margin-top: 2rem;
            }
            
            .checkout-steps {
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .checkout-title {
                font-size: 1.5rem;
            }
            
            .step-label {
                display: none;
            }
            
            .checkout-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg store-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat nav-icon"></i>
                <span>NutriCore Store</span>
            </a>
            
            <div class="d-flex align-items-center">
                <a href="cart.php" class="btn btn-outline-primary me-3">
                    <i class="fas fa-arrow-left me-2"></i> Back to Cart
                </a>
                <a href="cart.php" class="btn btn-primary position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $itemCount > 0 ? $itemCount : '' ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Checkout Content -->
    <div class="checkout-container">
        <div class="checkout-header">
            <h1 class="checkout-title">
                <i class="fas fa-shopping-bag"></i> Checkout
            </h1>
        </div>
        
        <!-- Checkout Progress Steps -->
        <div class="checkout-steps">
            <div class="progress-line">
                <div class="progress-bar"></div>
            </div>
            <div class="step completed">
                <div class="step-number">1</div>
                <div class="step-label">Cart</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Information</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="checkout.php">
                    <!-- Customer Information -->
                    <div class="checkout-section">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i> Customer Information
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($user['name']) ?>"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control"
                                           value="<?= htmlspecialchars($user['email']) ?>"
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Age</label>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($user['age']) ?>"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($user['gender']) ?>"
                                           readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                                    <div class="checkout-section">
                    <h3 class="section-title">
                        <i class="fas fa-truck"></i> Shipping Address
                    </h3>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" id="city" name="city" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h3 class="section-title">
                            <i class="fas fa-money-bill-wave"></i> Payment Method
                        </h3>
                        
                        <div class="payment-method active">
                            <i class="fas fa-box payment-icon"></i>
                            <div class="payment-details">
                                <div class="payment-title">Cash on Delivery</div>
                                <div class="payment-description">Pay when you receive your order</div>
                            </div>
                            <input type="hidden" name="paymentMethod" value="cod">
                        </div>
                    </div>
            </div>
            
            <!-- Order Summary Column -->
            <div class="col-lg-4">
                <div class="checkout-section order-summary">
                    <h3 class="section-title">
                        <i class="fas fa-receipt"></i> Order Summary
                    </h3>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <img src="<?= htmlspecialchars($item['picturePath']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-price">$<?= number_format($item['price'], 2) ?></div>
                                <div class="item-quantity">Qty: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-total">$<?= number_format($item['subtotal'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-total">
                        <span>Subtotal</span>
                        <span>$<?= number_format($total, 2) ?></span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Delivery Fee</span>
                        <span>$3.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?= number_format($total + 3, 2) ?></span>
                    </div>
                                        
  
<form method="POST">
<input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <button type="submit" name="proceedCheck" class="checkout-btn">
                        <i class="fas fa-lock"></i> Complete Purchase
                    </button>
                    </form>

                    
                    <div class="secure-checkout">
                        <i class="fas fa-lock"></i>
                        <span>Secure checkout</span>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>