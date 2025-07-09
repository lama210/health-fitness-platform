<?php
session_start();
require_once('connection.php');

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "You must be logged in to submit feedback.";
    } else {
        $receiver_type = isset($_POST['receiver_type']) ? $_POST['receiver_type'] : '';
        $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        // Validation
        if ($receiver_id <= 0 || !in_array($receiver_type, ['coach', 'nutrition'])) {
            $errors[] = "Please select a valid professional.";
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Rating must be between 1 and 5.";
        }
        if (empty($comment)) {
            $errors[] = "Comment is required.";
        }

        if (empty($errors)) {
            try {
                // Check for existing feedback
                $checkStmt = $pdo->prepare("SELECT id FROM feedback 
                                          WHERE user_id = ? 
                                          AND receiver_id = ? 
                                          AND receiver_type = ?");
                $checkStmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_type]);
                
                if ($checkStmt->rowCount() > 0) {
                    $errors[] = "You've already submitted feedback for this professional!";
                } else {
                    // Insert new feedback
                    $stmt = $pdo->prepare("INSERT INTO feedback (user_id, receiver_id, receiver_type, rating, comment) 
                                         VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $receiver_id,
                        $receiver_type,
                        $rating,
                        $comment
                    ]);
                    $success = "Feedback submitted successfully!";
                    $_SESSION['feedback_success'] = $success;
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (PDOException $e) {
                $errors[] = "Error submitting feedback: " . $e->getMessage();
            }
        }
    }
}

// Check for success message from redirect
if (isset($_SESSION['feedback_success'])) {
    $success = $_SESSION['feedback_success'];
    unset($_SESSION['feedback_success']);
}

// Fetch professionals
try {
    // For Nutritionists - include specification
    $nutritionists = $pdo->query(
        "SELECT nutrition.id, 
                user.name, 
                user.email, 
                nutrition.picture AS profile_image,
                nutrition.experience,
                nutrition.specification,
                COALESCE(ROUND(AVG(feedback.rating), 1), 0) AS avg_rating,
                'nutrition' AS receiver_type 
         FROM nutrition 
         JOIN user ON nutrition.user_id = user.id
         LEFT JOIN feedback ON feedback.receiver_id = nutrition.id 
            AND feedback.receiver_type = 'nutrition'
         GROUP BY nutrition.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // For Coaches - include specialization
    $coaches = $pdo->query(
        "SELECT coach.id, 
                user.name, 
                user.email, 
                coach.picture AS profile_image,
                coach.experience,
                coach.specialization,
                COALESCE(ROUND(AVG(feedback.rating), 1), 0) AS avg_rating,
                'coach' AS receiver_type 
         FROM coach 
         JOIN user ON coach.user_id = user.id
         LEFT JOIN feedback ON feedback.receiver_id = coach.id 
            AND feedback.receiver_type = 'coach'
         GROUP BY coach.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    $all_receivers = array_merge($nutritionists, $coaches);
    usort($all_receivers, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} catch (PDOException $e) {
    die("Error fetching professionals: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f9f6;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .rating-star {
            transition: all 0.2s ease;
        }
        
        .rating-star:hover {
            transform: scale(1.2);
        }
        
        .filter-btn.active {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.5);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .bg-mint {
            background-color: #10b981;
        }
        
        .bg-mint-light {
            background-color: #d1fae5;
        }
        
        .text-mint {
            color: #10b981;
        }
        
        .border-mint {
            border-color: #10b981;
        }
        
        .hover-bg-mint:hover {
            background-color: #0d9f6e;
        }
        
        .bg-blue {
            background-color: #3b82f6;
        }
        
        .bg-blue-light {
            background-color: #dbeafe;
        }
        
        .text-blue {
            color: #3b82f6;
        }
        
        .border-blue {
            border-color: #3b82f6;
        }
        
        .hover-bg-blue:hover {
            background-color: #2563eb;
        }
        
        .login-prompt {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .login-prompt-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl md:text-3xl font-bold">
                    <i class="fas fa-comment-alt mr-2"></i> Provide Feedback
                </h1>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all duration-300">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden md:inline">Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="login.html" class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all duration-300">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="hidden md:inline">Login</span>
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-home"></i>
                        <span class="hidden md:inline">Back Home</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Filter Buttons -->
            <div class="flex flex-wrap justify-center gap-4 mb-8">
                <button onclick="filterReceivers('all')" class="filter-btn px-6 py-2 bg-blue text-white rounded-full hover:bg-blue-700 transition-all shadow-lg flex items-center">
                    <i class="fas fa-users mr-2"></i> All Professionals
                </button>
                <button onclick="filterReceivers('coach')" class="filter-btn px-6 py-2 bg-mint text-white rounded-full hover:bg-mint-700 transition-all shadow-lg flex items-center">
                    <i class="fas fa-dumbbell mr-2"></i> Coaches
                </button>
                <button onclick="filterReceivers('nutrition')" class="filter-btn px-6 py-2 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-all shadow-lg flex items-center">
                    <i class="fas fa-utensils mr-2"></i> Nutritionists
                </button>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div id="successMessage" class="bg-mint-light border-l-4 border-mint text-mint-800 p-4 mb-8 rounded-lg max-w-md mx-auto flex items-center animate-fade-in">
                    <i class="fas fa-check-circle text-mint mr-3 text-xl"></i>
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
                <script>
                    setTimeout(() => {
                        document.getElementById('successMessage').classList.add('animate-fade-out');
                        setTimeout(() => {
                            document.getElementById('successMessage').style.display = 'none';
                        }, 500);
                    }, 3000);
                </script>
            <?php endif; ?>

            <!-- Professionals Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($all_receivers as $r): 
                    $hasSubmittedFeedback = false;
                    if (isset($_SESSION['user_id'])) {
                        $checkStmt = $pdo->prepare("SELECT id FROM feedback 
                                                  WHERE user_id = ? 
                                                  AND receiver_id = ? 
                                                  AND receiver_type = ?");
                        $checkStmt->execute([$_SESSION['user_id'], $r['id'], $r['receiver_type']]);
                        $hasSubmittedFeedback = $checkStmt->rowCount() > 0;
                    }
                ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden receiver-card card-hover transition-all duration-300 border border-gray-100"
                         data-type="<?= htmlspecialchars($r['receiver_type']) ?>">
                        <!-- Profile Header -->
                        <div class="relative">
                            <div class="h-24 w-full <?= $r['receiver_type'] === 'coach' ? 'bg-mint' : 'bg-blue' ?>"></div>
                            <div class="absolute -bottom-12 left-4">
                                <?php if (!empty($r['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($r['profile_image']) ?>" 
                                         alt="<?= htmlspecialchars($r['name']) ?>" 
                                         class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                                <?php else: ?>
                                    <div class="w-24 h-24 <?= $r['receiver_type'] === 'coach' ? 'bg-mint-light text-mint' : 'bg-blue-light text-blue' ?> rounded-full flex items-center justify-center text-4xl font-bold border-4 border-white shadow-lg">
                                        <?= strtoupper(substr(htmlspecialchars($r['name']), 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Profile Content -->
                        <div class="pt-16 px-6 pb-6">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($r['name']) ?></h3>
                                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($r['email']) ?></p>
                                </div>
                                <span class="text-xs font-medium <?= $r['receiver_type'] === 'coach' ? 'bg-mint-light text-mint' : 'bg-blue-light text-blue' ?> px-2 py-1 rounded-full">
                                    <?= ucfirst(htmlspecialchars($r['receiver_type'])) ?>
                                </span>
                            </div>
                            
                            <!-- Specialization -->
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    <i class="<?= $r['receiver_type'] === 'coach' ? 'fas fa-dumbbell text-mint' : 'fas fa-utensils text-blue' ?> mr-1"></i>
                                    <?= $r['receiver_type'] === 'coach' ? 'Specialization' : 'Specialization' ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= htmlspecialchars($r[$r['receiver_type'] === 'coach' ? 'specialization' : 'specification']) ?>
                                </p>
                            </div>
                            
                            <!-- Experience and Rating -->
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-briefcase mr-1"></i>
                                    <span><?= $r['experience'] ?> years experience</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex mr-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="w-4 h-4 <?= $i <= floor($r['avg_rating']) ? 'text-yellow-400' : 'text-gray-300' ?> rating-star" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">
                                        <?= number_format($r['avg_rating'], 1) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Feedback Button -->
                            <button 
                                onclick="<?= isset($_SESSION['user_id']) && !$hasSubmittedFeedback ? "openFeedbackModal('{$r['receiver_type']}', {$r['id']}, '" . addslashes(htmlspecialchars($r['name'])) . "')" : "handleFeedbackButtonClick(" . (isset($_SESSION['user_id']) ? 'true' : 'false') . ", " . ($hasSubmittedFeedback ? 'true' : 'false') . ")" ?>"
                                class="w-full px-4 py-3 rounded-lg font-medium transition-all duration-300 flex items-center justify-center 
                                    <?= $hasSubmittedFeedback ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 
                                       (isset($_SESSION['user_id']) ? 
                                           ($r['receiver_type'] === 'coach' ? 'bg-mint hover:bg-mint-700 text-white' : 'bg-blue hover:bg-blue-700 text-white') : 
                                           'bg-gray-300 text-gray-600 hover:bg-gray-400') ?>"
                                <?= $hasSubmittedFeedback ? 'disabled' : '' ?>>
                                <?php if ($hasSubmittedFeedback): ?>
                                    <i class="fas fa-check-circle mr-2"></i> Feedback Submitted
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <i class="fas fa-lock mr-2"></i> Submit Feedback
                                <?php else: ?>
                                    <i class="fas fa-pen-alt mr-2"></i> Submit Feedback
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="flex justify-between items-center bg-gradient-to-r from-blue to-mint px-6 py-4">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-star mr-2"></i> Feedback for <span id="receiverName"></span>
                </h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-6 mt-4 animate-shake">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                There were <?= count($errors) ?> errors with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="p-6">
                <input type="hidden" name="receiver_type" id="modalReceiverType" value="">
                <input type="hidden" name="receiver_id" id="modalReceiverId" value="">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Rating</label>
                        <div class="flex justify-center gap-1" id="ratingStars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" 
                                    onclick="setRating(<?= $i ?>)" 
                                    class="rating-star w-10 h-10 rounded-full bg-gray-100 hover:bg-yellow-400 transition-all flex items-center justify-center text-lg font-bold shadow-sm"
                                    data-rating="<?= $i ?>">
                                    <?= $i ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="selectedRating" required>
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment-dots text-blue mr-1"></i> Your Feedback
                        </label>
                        <textarea name="comment" id="comment" rows="4" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue focus:border-transparent placeholder-gray-400 transition-all"
                            placeholder="Share your experience..."><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-all font-medium flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-blue hover:bg-blue-700 text-white rounded-lg transition-all font-medium flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Prompt Modal -->
    <div id="loginPromptModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300">
            <div class="flex justify-between items-center bg-gradient-to-r from-blue to-mint px-6 py-4">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-lock mr-2"></i> Login Required
                </h3>
                <button onclick="closeLoginPrompt()" class="text-white hover:text-gray-200 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 text-center">
                <div class="text-5xl mb-4 text-blue">
                    <i class="fas fa-user-lock"></i>
                </div>
                <p class="text-gray-700 mb-6">You need to be logged in to submit feedback. Please login or create an account to continue.</p>
                <div class="flex flex-col space-y-3">
                    <a href="login.html" class="px-6 py-3 bg-blue hover:bg-blue-700 text-white rounded-lg font-medium transition-all">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="register.html" class="px-6 py-3 bg-mint hover:bg-mint-700 text-white rounded-lg font-medium transition-all">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filter professionals
        function filterReceivers(type) {
            const cards = document.querySelectorAll('.receiver-card');
            cards.forEach(card => {
                card.style.display = (type === 'all' || card.dataset.type === type) ? 'block' : 'none';
            });
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'ring-2', 'ring-offset-2', 'ring-blue', 'ring-mint');
                if ((type === 'all' && btn.textContent.includes('All')) ||
                    (type === 'coach' && btn.textContent.includes('Coaches')) ||
                    (type === 'nutrition' && btn.textContent.includes('Nutritionists'))) {
                    btn.classList.add('active');
                    const ringColor = type === 'all' ? 'ring-blue' : 
                                      type === 'coach' ? 'ring-mint' : 'ring-blue';
                    btn.classList.add('ring-2', 'ring-offset-2', ringColor);
                }
            });
        }

        // Handle feedback button click
        function handleFeedbackButtonClick(isLoggedIn, hasSubmitted) {
            if (!isLoggedIn) {
                showLoginPrompt();
                return;
            }
            if (hasSubmitted) {
                alert("You've already submitted feedback for this professional!");
                return;
            }
        }

        // Modal handling
        function openFeedbackModal(type, id, name) {
            // Reset form
            document.getElementById('feedbackModal').querySelector('form').reset();
            resetRating();
            
            // Set receiver info
            document.getElementById('receiverName').textContent = name;
            document.getElementById('modalReceiverType').value = type;
            document.getElementById('modalReceiverId').value = id;
            
            // Show modal with animation
            document.getElementById('feedbackModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => {
                document.getElementById('modalContent').classList.remove('scale-95', 'opacity-0');
                document.getElementById('modalContent').classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            document.getElementById('modalContent').classList.remove('scale-100', 'opacity-100');
            document.getElementById('modalContent').classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                document.getElementById('feedbackModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                resetRating();
            }, 300);
        }

        // Login prompt functions
        function showLoginPrompt() {
            document.getElementById('loginPromptModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginPrompt() {
            document.getElementById('loginPromptModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Rating system
        function setRating(rating) {
            document.querySelectorAll('#ratingStars .rating-star').forEach(star => {
                const starValue = parseInt(star.dataset.rating);
                if (starValue <= rating) {
                    star.classList.add('bg-yellow-400', 'text-white', 'shadow-md');
                    star.classList.remove('bg-gray-100');
                } else {
                    star.classList.remove('bg-yellow-400', 'text-white', 'shadow-md');
                    star.classList.add('bg-gray-100');
                }
            });
            document.getElementById('selectedRating').value = rating;
        }

        function resetRating() {
            document.querySelectorAll('#ratingStars .rating-star').forEach(star => {
                star.classList.remove('bg-yellow-400', 'text-white', 'shadow-md');
                star.classList.add('bg-gray-100');
            });
            document.getElementById('selectedRating').value = '';
        }

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === document.getElementById('feedbackModal')) {
                closeModal();
            }
            if (event.target === document.getElementById('loginPromptModal')) {
                closeLoginPrompt();
            }
        });

        // Initialize - show all by default
        document.addEventListener('DOMContentLoaded', () => {
            filterReceivers('all');
            
            // Add animation classes
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-10px); }
                }
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
                .animate-fade-out { animation: fadeOut 0.3s ease-out forwards; }
                .animate-shake { animation: shake 0.5s ease-in-out; }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>