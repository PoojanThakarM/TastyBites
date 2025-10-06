<?php
session_start();
include("db.php"); // Make sure $conn is defined in db.php

$message = "";

// Handle new review submission only if user is logged in
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'], $_POST['rating'])) {
    $review_text = htmlspecialchars(trim($_POST['review_text']));
    $rating = (int)$_POST['rating'];
    $user_id = $_SESSION['user_id'];

    if (!empty($review_text) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, review_text, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $review_text, $rating);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: reviews.php?success=1");
            exit();
        } else {
            $message = "❌ Error posting review. Try again.";
            $stmt->close();
        }
    } else {
        $message = "❌ Please provide valid review text and rating (1-5).";
    }
}

// Show success message if redirected
if (isset($_GET['success'])) {
    $message = "✅ Review posted successfully!";
}

// Fetch all reviews (everyone can view)
$reviews = [];
$result = $conn->query("SELECT r.review_text, r.rating, r.created_at, u.username 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        ORDER BY r.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Reviews - Tasty Bites</title>
<link rel="stylesheet" href="css/style-navbar.css">
<link rel="stylesheet" href="css/style-reviews.css">
<style>
/* Floating add button – only visible for logged-in users */
#addReviewBtn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #E63946;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 36px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
#addReviewBtn:hover { background: #d62828; }

/* Modal */
.modal {
    display: none; 
    position: fixed; 
    z-index: 2000; 
    left: 0; top: 0; 
    width: 100%; height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.6);
}
.modal-content {
    background-color: #fff3e0;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
    max-width: 400px;
    position: relative;
}
.close-btn {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 24px; cursor: pointer;
}
.modal-content input[type="text"], .modal-content textarea, .modal-content select {
    width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 4px; border: 1px solid #ccc;
}
.modal-content button {
    background: #E63946; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;
}
.modal-content button:hover { background: #d62828; }

.review-card {
    background: #fffaf4;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 5px solid #E63946;
    border-radius: 6px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.review-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.review-card h3 { margin: 0 0 5px 0; color: #E63946; font-size: 16px; }
.review-card p { margin: 5px 0; }
.review-card .date { font-size: 12px; color: #666; }
.centered-text h1.reviews-heading {
    text-align: center;
    font-size: 26px;
    color: #E63946;
    margin-bottom: 20px;
}
/* Floating action menu */
.floating-menu {
  position: fixed;
  bottom: 25px;
  right: 25px;
  z-index: 1500;
}

#fabMain {
  background: #E63946;
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 36px;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  transition: transform 0.3s ease;
}

#fabMain:hover {
  background: #d62828;
  transform: rotate(45deg);
}

.fab-options {
  display: none;
  flex-direction: column;
  position: absolute;
  bottom: 70px;
  right: 0;
  gap: 10px;
}

.fab-options button {
  background: #fff3e0;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  font-size: 20px;
  cursor: pointer;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
  transition: all 0.2s ease;
}

.fab-options button:hover {
  background: #ffe0cc;
  transform: scale(1.1);
}

</style>
</head>
<body>
<header>
  <h1>🍽️ Tasty Bites Restaurant</h1>
  <?php include("navbar.php"); ?>
</header>

<main>
    <div class="centered-text">
        <h1 class="reviews-heading">⭐ Customer Reviews ⭐</h1>
        <?php if(!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
    </div>

    <div class="review-container">
        <?php if(count($reviews) > 0): ?>
            <?php foreach($reviews as $rev): ?>
                <div class="review-card">
                    <h3><?php echo str_repeat("⭐", $rev['rating']); ?> - <?php echo htmlspecialchars($rev['username']); ?></h3>
                    <p><?php echo htmlspecialchars($rev['review_text']); ?></p>
                    <p class="date"><?php echo $rev['created_at']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to post!</p>
        <?php endif; ?>
    </div>

    <!-- Floating Add Review Button (only for logged-in users) -->
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Floating Action Menu -->
<div class="floating-menu">
  <button id="fabMain">+</button>
  <div class="fab-options" id="fabOptions">
    <button id="addReview" title="Add Review">📝</button>
    <button id="editReview" title="Edit Review">✏️</button>
    <button id="deleteReview" title="Delete Review">🗑️</button>
  </div>
</div>


        <!-- Modal for adding review -->
        <div id="reviewModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <h3>Post a Review</h3>
                <form method="POST" action="">
                    <label for="review_text">Your Review:</label>
                    <textarea id="review_text" name="review_text" rows="4" required></textarea>
                    <label for="rating">Rating:</label>
                    <select name="rating" id="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                    <button type="submit">Post Review</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">

<?php if(isset($_SESSION['user_id'])): ?>
<script>
const fabMain = document.getElementById("fabMain");
const fabOptions = document.getElementById("fabOptions");

// Toggle floating options visibility
fabMain.onclick = function() {
  fabOptions.style.display = (fabOptions.style.display === "flex") ? "none" : "flex";
};

// Open modal on Add Review click
const reviewModal = document.getElementById("reviewModal");
document.getElementById("addReview").onclick = function() {
  reviewModal.style.display = "block";
  fabOptions.style.display = "none";
};

// Close modal
document.getElementById("closeModal").onclick = function() {
  reviewModal.style.display = "none";
};

// Hide modal when clicking outside
window.onclick = function(event) {
  if (event.target == reviewModal) reviewModal.style.display = "none";
};

// Placeholder actions for edit/delete (we’ll add logic later)
document.getElementById("editReview").onclick = function() {
  alert("Edit feature coming soon!");
};
document.getElementById("deleteReview").onclick = function() {
  alert("Delete feature coming soon!");
};
</script>

<?php endif; ?>
</body>
</html>
