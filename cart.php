<?php
session_start();
require_once("connection.php");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch cart items from database
$cartItems = [];
$total = 0;
$itemCount = 0;

// Get all cart items for this user with product details
$stmt = $pdo->prepare("
    SELECT c.*, s.productName, s.price, s.picturePath, s.category 
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
        'image' => $product['picturePath'],
        'category' => $product['category'],
        'quantity' => $product['quantity'],
        'subtotal' => $subtotal
    ];
}

// Handle cart updates (quantity changes or removal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $productId = $_POST['id'];
        $action = $_POST['action'];
        
        if ($action === 'increase') {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } elseif ($action === 'decrease') {
            // Check current quantity
            $checkStmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $checkStmt->execute([$userId, $productId]);
            $currentQty = $checkStmt->fetchColumn();
            
            if ($currentQty > 1) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
            } else {
                // Remove if quantity would go to 0
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
            }
        } elseif ($action === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        }
        
        // Refresh the page to show updated cart
        header("Location: cart.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | NutriCore Store</title>
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
        
        /* Cart Container */
        .cart-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }
        
        .cart-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .cart-title {
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.75rem;
        }
        
        .cart-count {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Cart Items */
        .cart-item {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }
        
        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }
        
        .cart-item-img-container {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            margin-right: 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .cart-item-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .cart-item:hover .cart-item-img {
            transform: scale(1.05);
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .cart-item-category {
            display: inline-block;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .cart-item-price {
            font-weight: 700;
            color: var(--success);
            font-size: 1.1rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            margin: 0 2rem;
            background: var(--light);
            border-radius: 50px;
            padding: 5px;
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .quantity-btn:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }
        
        .quantity-input {
            width: 40px;
            text-align: center;
            border: none;
            font-weight: 600;
            margin: 0 5px;
            background: transparent;
        }
        
        .remove-btn {
            color: var(--danger);
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .remove-btn:hover {
            background: rgba(255, 107, 107, 0.1);
            transform: scale(1.1);
        }
        
        /* Summary Card */
        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 120px;
        }
        
        .summary-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 1.25rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .summary-label {
            color: #6c757d;
        }
        
        .summary-value {
            font-weight: 500;
        }
        
        .summary-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark);
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .checkout-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            margin-top: 1.5rem;
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
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
        
        .empty-cart-icon {
            font-size: 3.5rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        .empty-cart-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .empty-cart-text {
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .cart-container {
                margin: 2rem auto;
            }
            
            .summary-card {
                margin-top: 2rem;
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 1rem;
            }
            
            .cart-item-img-container {
                margin-right: 0;
                margin-bottom: 1.5rem;
                width: 100%;
                height: 180px;
            }
            
            .quantity-control {
                margin: 1.5rem 0;
            }
            
            .cart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
                <a href="shop.php" class="btn btn-outline-primary me-3">
                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
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

    <!-- Cart Content -->
    <div class="cart-container">
        <div class="row">
            <!-- Cart Items Column -->
            <div class="col-lg-8">
                <div class="cart-header">
                    <h2 class="cart-title">
                        <i class="fas fa-shopping-cart"></i> Your Shopping Cart
                        <span class="cart-count"><?= $itemCount ?> item<?= $itemCount != 1 ? 's' : '' ?></span>
                    </h2>
                </div>
                
                <?php if (empty($cartItems)) : ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart empty-cart-icon"></i>
                        <h3 class="empty-cart-title">Your cart is empty</h3>
                        <p class="empty-cart-text">Looks like you haven't added anything to your cart yet</p>
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                    </div>
                <?php else : ?>
                    <!-- Cart Items -->
                    <?php foreach ($cartItems as $item) : ?>
                        <div class="cart-item">
                            <div class="cart-item-img-container">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                            </div>
                            <div class="cart-item-details">
                                <h5 class="cart-item-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <span class="cart-item-category">
                                    <?= htmlspecialchars($item['category']) ?>
                                </span>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="cart-item-price">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="text-muted">Subtotal: $<?= number_format($item['subtotal'], 2) ?></span>
                                </div>
                            </div>
                            <form method="POST" action="cart.php" class="quantity-control">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                                <input type="text" class="quantity-input" value="<?= $item['quantity'] ?>" readonly>
                                <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                            </form>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" name="action" value="remove" class="remove-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Order Summary Column -->
            <?php if (!empty($cartItems)) : ?>
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal (<?= $itemCount ?> item<?= $itemCount != 1 ? 's' : '' ?>)</span>
                            <span class="summary-value">$<?= number_format($total, 2) ?></span>
                        </div>
                        
                    
                       
                    <div class="summary-total">
                        <span>Delivery Fee</span>
                        <span>$3.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?= number_format($total + 3, 2) ?></span>
                    </div>
                        
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        
                        <div class="secure-checkout">
                            <i class="fas fa-lock"></i>
                            <span>Secure checkout</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth animations for item removal
        document.querySelectorAll('button[value="remove"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const cartItem = this.closest('.cart-item');
                cartItem.style.transform = 'translateX(100px)';
                cartItem.style.opacity = '0';
                setTimeout(() => {
                    cartItem.style.display = 'none';
                }, 300);
            });
        });
    </script>
</body>
</html>