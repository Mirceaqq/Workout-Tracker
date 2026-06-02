<?php
session_start();
require_once 'php/auth.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker — Track every rep.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="lp-nav">
        <a href="index.php" class="lp-nav-logo">WT<span>.</span></a>
        <ul class="lp-nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="lp-nav-right">
            <a href="login.php" class="lp-nav-signin">Sign in</a>
            <a href="register.php" class="lp-nav-cta">Start Free</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="lp-hero">

        <div class="lp-hero-left">
            <p class="lp-eyebrow"><span class="lp-eyebrow-dot"></span>WORKOUT TRACKER</p>
            <h1 class="lp-hero-title">
                <span class="lp-title-light">Track every rep.</span>
                <span class="lp-title-accent">Own your progress.</span>
            </h1>
            <p class="lp-hero-sub">
                Log your workouts, track your lifts and watch your progress compound —
                free, fast and built for athletes who take training seriously.
            </p>
            <div class="lp-hero-actions">
                <a href="register.php" class="lp-btn-primary">Get started &rarr;</a>
                <a href="#features" class="lp-btn-ghost">See how it works &rarr;</a>
            </div>
        </div>

        <div class="lp-hero-right">
            <div class="lp-preview">
                <div class="lp-preview-topbar">
                    <span class="lp-preview-brand">WT.</span>
                    <span class="lp-preview-dots"><i></i><i></i></span>
                </div>
                <div class="lp-preview-stats">
                    <div class="lp-preview-stat">
                        <span class="lp-preview-stat-num">48</span>
                        <span class="lp-preview-stat-label">Total</span>
                    </div>
                    <div class="lp-preview-stat">
                        <span class="lp-preview-stat-num">3</span>
                        <span class="lp-preview-stat-label">Week</span>
                    </div>
                    <div class="lp-preview-stat">
                        <span class="lp-preview-stat-num">7d</span>
                        <span class="lp-preview-stat-label">Streak</span>
                    </div>
                </div>
                <div class="lp-preview-chart">
                    <svg viewBox="0 0 260 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="cg" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#C8FF00" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#C8FF00" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <polygon points="10,65 50,52 90,44 130,32 170,20 210,14 250,6 250,80 10,80"
                            fill="url(#cg)" />
                        <polyline points="10,65 50,52 90,44 130,32 170,20 210,14 250,6"
                            fill="none" stroke="#C8FF00" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <p class="lp-preview-chart-label">Bench Press Progress</p>
            </div>
        </div>

    </section>

    <!-- STATS BAR -->
    <div class="lp-stats-bar">
        <div class="lp-stat-item">
            <span class="lp-stat-num">2,400+</span>
            <span class="lp-stat-label">workouts logged</span>
        </div>
        <div class="lp-stat-item">
            <span class="lp-stat-num">180+</span>
            <span class="lp-stat-label">active users</span>
        </div>
        <div class="lp-stat-item">
            <span class="lp-stat-num">12,000+</span>
            <span class="lp-stat-label">exercises tracked</span>
        </div>
        <div class="lp-stat-item">
            <span class="lp-stat-num">98%</span>
            <span class="lp-stat-label">satisfaction rate</span>
        </div>
    </div>

    <!-- FEATURES -->
    <section class="lp-features" id="features">
        <p class="lp-eyebrow"><span class="lp-eyebrow-dot"></span>FEATURES</p>
        <h2 class="lp-section-title">Everything you need to train smarter.</h2>
        <div class="lp-features-grid">
            <div class="lp-feature-card">
                <span class="lp-feature-num">01</span>
                <h3 class="lp-feature-title">Track Workouts</h3>
                <p class="lp-feature-desc">Log every exercise, set, rep and weight. Full history always available.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-num">02</span>
                <h3 class="lp-feature-title">Visualize Progress</h3>
                <p class="lp-feature-desc">Beautiful charts show exactly how far you've come. Set goals and beat them.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-num">03</span>
                <h3 class="lp-feature-title">Plan Ahead</h3>
                <p class="lp-feature-desc">Build reusable workout templates. Start any session in seconds.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="lp-cta" id="about">
        <p class="lp-eyebrow lp-eyebrow-center"><span class="lp-eyebrow-dot"></span>START TODAY</p>
        <h2 class="lp-cta-title">Ready to track your<br>progress?</h2>
        <a href="register.php" class="lp-btn-primary lp-cta-btn">Create free account &rarr;</a>
    </section>

    <!-- FOOTER -->
    <footer class="lp-footer">
        <div class="lp-footer-col">
            <a href="index.php" class="lp-footer-logo">WT<span>.</span></a>
            <p class="lp-footer-tagline">Track every rep.</p>
        </div>
        <nav class="lp-footer-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="contact.php">Contact</a>
            <a href="login.php">Login</a>
        </nav>
        <p class="lp-footer-copy">&copy; <?php echo date('Y'); ?> Workout Tracker &middot; Chi&#537;in&#259;u</p>
    </footer>

    <script src="js/script.js"></script>
</body>

</html>