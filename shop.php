<?php
session_start();
require_once("connection.php");

// Get cart count for display
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['cart_count'])) {
        $cartCount = $_SESSION['cart_count'];
    } else {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cartCount = $stmt->fetchColumn();
        if ($cartCount === false) {
            $cartCount = 0;
        }
        $_SESSION['cart_count'] = $cartCount;
    }
}

// Handle add to cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['add_to_cart_error'] = "Please login first to add items to cart";
        header("Location: login.html");
        exit();
    }
    
    // Check if user has 'user' role
    $roleStmt = $pdo->prepare("SELECT role FROM user WHERE id = ?");
    $roleStmt->execute([$_SESSION['user_id']]);
    $userRole = $roleStmt->fetchColumn();
    
    if ($userRole !== 'user') {
        $_SESSION['add_to_cart_error'] = "Only regular users can add items to cart";
        header("Location: shop.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];

    // Check if this product already exists in cart for this user
    $checkStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $productId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update quantity
        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $updateStmt->execute([$userId, $productId]);
    } else {
        // Insert new row
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insertStmt->execute([$userId, $productId]);
    }

    // Update the cart count in session for immediate UI update
    $_SESSION['cart_count'] = (isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0) + 1;

    // Redirect to prevent form resubmission
    header("Location: shop.php?added=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCore Store | Premium Health Products</title>
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
        }
        
        /* Modern Navigation */
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
        
        /* Hero Section */
        .store-hero {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 30px 30px;
        }
        
        .hero-title {
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .filter-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-btn {
            border: 2px solid var(--primary);
            color: var(--primary) !important;
            border-radius: 50px;
            padding: 8px 20px;
            margin: 0 5px 10px 0;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary) !important;
            color: white !important;
            transform: translateY(-2px);
        }
        
        .filter-btn i {
            margin-right: 8px;
        }
        
        .price-filter, .search-input {
            border-radius: 50px;
            padding: 10px 20px;
            border: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .price-filter:focus, .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        /* Product Cards */
        .product-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            background: white;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .product-image-container {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .product-category {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 1.5rem;
        }
        
        .add-to-cart {
            background: var(--primary);
            border: none;
            border-radius: 50px;
            padding: 10px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
        }
        
        .add-to-cart:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .add-to-cart:disabled {
            background: #cccccc !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        /* Product Grid Spacing */
        #product-grid {
            --grid-gap: 2rem;
            gap: var(--grid-gap);
        }
        
        @media (max-width: 768px) {
            #product-grid {
                --grid-gap: 1.5rem;
            }
        }
        
        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
        }
        
        /* Alert Messages */
        .alert-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            animation: fadeInOut 3s ease-in-out;
            opacity: 0;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .alert-error {
            background-color: #ff6b6b;
            color: white;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .no-results-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        /* Login Prompt */
        .login-prompt {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            animation: slideUp 0.5s ease-out;
            display: none;
        }
        
        .login-prompt a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
        }
          .nav-buttons {
            display: flex;
            gap: 10px;
        }
        
        .square-btn {
            border-radius: 8px !important;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        @keyframes slideUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg store-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat nav-icon"></i>
                <span>NutriCore Store</span>
            </a>
             <div class="nav-buttons">
                <a href="index.php" class="btn btn-outline-primary square-btn">
                    <i class="fas fa-home"></i>
                    <span>Back Home</span>
                </a>
            
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="btn btn-outline-dark position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="login.html" class="btn btn-outline-dark">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="store-hero">
        <div class="container text-center">
            <h1 class="hero-title mb-3">Premium Health & Wellness</h1>
            <p class="lead mb-0">Discover products to fuel your fitness journey</p>
        </div>
    </section>

    <div class="container py-4">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="filter-title"><i class="fas fa-filter"></i> Category</h5>
                    <div class="d-flex flex-wrap">
                        <button class="btn filter-btn active" data-filter="all">
                            <i class="fas fa-cubes"></i> All
                        </button>
                        <button class="btn filter-btn" data-filter="supplements">
                            <i class="fas fa-capsules"></i> Supplements
                        </button>
                        <button class="btn filter-btn" data-filter="equipment">
                            <i class="fas fa-dumbbell"></i> Equipment
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="filter-title"><i class="fas fa-tag"></i> Price Range</h5>
                    <select class="form-select price-filter">
                        <option value="all">All Prices</option>
                        <option value="0-50">$0 - $50</option>
                        <option value="50-100">$50 - $100</option>
                        <option value="100-999">$100+</option>
                    </select>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="filter-title"><i class="fas fa-search"></i> Search</h5>
                    <input type="text" class="form-control search-input" placeholder="Search products...">
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-toast" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Product added to cart!</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($_SESSION['add_to_cart_error'])): ?>
            <div class="alert alert-error alert-dismissible fade show alert-toast" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= $_SESSION['add_to_cart_error'] ?></div>
                </div>
            </div>
            <?php unset($_SESSION['add_to_cart_error']); ?>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php
        $sql = "SELECT * FROM Shop";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            echo '<div class="row g-4" id="product-grid">';
            foreach ($result as $row) {
                echo '
                <div class="col-md-6 col-lg-4 col-xl-3" data-category="'.htmlspecialchars($row["category"]).'" 
                     data-price="'.$row["price"].'" data-name="'.strtolower($row["productName"]).'">
                    <div class="product-card h-100">
                        <div class="product-image-container">
                            <img src="'.htmlspecialchars($row["picturePath"]).'" class="product-image" alt="'.htmlspecialchars($row["productName"]).'">
                            <span class="product-badge">'.htmlspecialchars($row["category"]).'</span>
                        </div>
                        <div class="card-body">
                            <h5 class="product-title">'.htmlspecialchars($row["productName"]).'</h5>
                            <span class="product-category">
                                <i class="fas fa-tag me-2"></i>'.htmlspecialchars($row["category"]).'
                            </span>
                            <div class="product-price">$'.number_format($row["price"], 2).'</div>
                            <form method="POST" action="shop.php">
                                <input type="hidden" name="product_id" value="'.$row["id"].'">
                                <button type="submit" name="add_to_cart" class="btn add-to-cart" '.((!isset($_SESSION['user_id'])) ? 'disabled' : '').'>
                                    <i class="fas fa-cart-plus"></i> '.(isset($_SESSION['user_id']) ? 'Add to Cart' : 'Login to Purchase').'
                                </button>
                            </form>
                        </div>
                    </div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '
            <div class="no-results">
                <i class="fas fa-search no-results-icon"></i>
                <h3 class="mb-3">No products found</h3>
                <p class="text-muted">Try adjusting your filters or search terms</p>
            </div>';
        }
        ?>
    </div>

    <!-- Login Prompt (shown when trying to add to cart without login) -->
    <div class="login-prompt" id="loginPrompt">
        <i class="fas fa-info-circle"></i>
        <span>Please <a href="login.html">login</a> to add items to your cart</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const priceFilter = document.querySelector('.price-filter');
        const searchInput = document.querySelector('.search-input');
        const products = document.querySelectorAll('#product-grid > div');
        const noResults = document.querySelector('.no-results');
        const loginPrompt = document.getElementById('loginPrompt');
        const addToCartButtons = document.querySelectorAll('.add-to-cart');

        // Show login prompt when trying to add to cart without login
        addToCartButtons.forEach(button => {
            if (button.disabled) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    loginPrompt.style.display = 'flex';
                    setTimeout(() => {
                        loginPrompt.style.display = 'none';
                    }, 3000);
                });
            }
        });

        function filterProducts() {
            const selectedCategory = document.querySelector('.filter-btn.active').dataset.filter;
            const priceRange = priceFilter.value;
            const searchTerm = searchInput.value.toLowerCase();
            
            let visibleCount = 0;

            products.forEach(product => {
                const category = product.dataset.category;
                const price = parseFloat(product.dataset.price);
                const productName = product.dataset.name;
                const [min, max] = priceRange.split('-').map(Number);
                
                // Category check
                const categoryMatch = selectedCategory === 'all' || category === selectedCategory;
                
                // Price check
                const priceMatch = priceRange === 'all' || 
                    (price >= min && (max ? price <= max : true));
                
                // Search check
                const searchMatch = productName.includes(searchTerm);

                // Combined match
                const shouldShow = categoryMatch && priceMatch && searchMatch;

                // Toggle visibility
                if(shouldShow) {
                    product.style.display = 'block';
                    visibleCount++;
                } else {
                    product.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (noResults) {
                noResults.style.display = visibleCount > 0 ? 'none' : 'block';
            }
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterProducts();
            });
        });

        priceFilter.addEventListener('change', filterProducts);
        searchInput.addEventListener('input', filterProducts);

        // Initial filter
        filterProducts();
        
        // Auto-dismiss messages
        const alerts = document.querySelectorAll('.alert-toast');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.remove();
            }, 3000);
        });
    });
    </script>
</body>
</html>