<?php
include("db.php");

// Fetch published FAQs
$result = $conn->query("SELECT question, answer FROM faqs WHERE status = 'Published' ORDER BY created_at ASC");
$faqs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - Tasty Bites</title>
    <link rel="stylesheet" href="css/style-navbar.css">
    <link rel="stylesheet" href="css/faq.css">
</head>
<body>
    <header>
        <h2 class="restaurant-title">🍽️ Tasty Bites Restaurant</h2>
        <?php include("navbar.php"); ?>
    </header>

    <main>
        <div class="faq-container">
            <h1>❓ Frequently Asked Questions</h1>
            <p class="description">Have questions? We have answers! Explore our FAQs below.</p>
            <?php if (count($faqs)): ?>
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item">
                        <h3><?= htmlspecialchars($faq['question']) ?></h3>
                        <div class="faq-answer">
                            <p><?= htmlspecialchars($faq['answer']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No FAQs available at the moment.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include("footer.php"); ?>
    <link rel="stylesheet" href="css/style-footer.css">

    <script>
        // FAQ toggle
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('active');
                const answer = item.querySelector('.faq-answer');
                if (answer.style.display === 'block') {
                    answer.style.display = 'none';
                } else {
                    answer.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>