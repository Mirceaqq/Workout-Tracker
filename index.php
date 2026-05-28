<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-logo">WT<span>.</span></a>
    <div class="navbar-links">
        <a href="#features">Features</a>
        <a href="#about">About</a>
        <a href="contact.php">Contact</a>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="navbar-cta">Dashboard</a>
    <?php else: ?>
        <a href="login.php" class="navbar-cta">Start Free</a>
    <?php endif; ?>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Track every rep.<br><span>Own your progress.</span></h1>
        <p>A workout tracker built for consistency. Log exercises, visualize progress, and build habits that last.</p>
        <div class="hero-ctas">
            <a href="register.php" class="btn-primary">Get started →</a>
            <a href="#features" class="btn-ghost">See how it works →</a>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="feature">
        <div class="feature-dot"></div>
        <h3>Track Workouts</h3>
        <p>Log every exercise, set, and rep with ease.</p>
    </div>
    <div class="feature">
        <div class="feature-dot"></div>
        <h3>Visualize Progress</h3>
        <p>Charts that show your improvement over time.</p>
    </div>
    <div class="feature">
        <div class="feature-dot"></div>
        <h3>Plan Ahead</h3>
        <p>Build custom plans and stick to your schedule.</p>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <p>© 2026 Workout Tracker</p>
    <p>Built with PHP · Chișinău</p>
</footer>

</body>
</html>