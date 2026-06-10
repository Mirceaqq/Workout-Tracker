<?php
session_start();
require_once 'php/auth.php';
requireGuest();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = sanitize($_POST['email']    ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($email) || empty($password)) {
    $error = 'Completează toate câmpurile.';
  } else {
    $user = loginUser($email, $password);
    if ($user) {
      session_regenerate_id(true);
      $_SESSION['user_id'] = $user['id'];
      if (!empty($_POST['remember'])) {
        setcookie('wt_remember', $user['id'], time() + 60 * 60 * 24 * 30, '/', '', false, true);
      }
      header('Location: dashboard.php');
      exit;
    } else {
      $error = 'Email sau parolă incorecte.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Autentificare — Workout Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">

  <div class="auth-page" id="authPage">

    <div class="auth-left">
      <a href="index.php" class="auth-logo">WT.</a>
      <div class="auth-left-body">
        <div class="auth-accent-line"></div>
        <blockquote class="auth-quote">"Constanța bate intensitatea."</blockquote>
        <p class="auth-quote-cite">— Principiul progresului</p>
      </div>
      <p class="auth-left-footer">Workout Tracker &middot; <?php echo date('Y'); ?></p>
    </div>

    <div class="auth-right">
      <div class="auth-form-wrap">
        <h1 class="auth-title">Bun venit înapoi</h1>
        <p class="auth-subtitle">Autentifică-te pentru a continua.</p>

        <?php if ($error): ?>
          <div class="auth-alert">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">

          <div style="margin-bottom:16px;">
            <label for="email" style="display:block;font-size:10px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">Email</label>
            <input type="email" id="email" name="email" placeholder="exemplu@email.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" style="height:48px;background:#111;border:1px solid #2A2A2A;border-radius:10px;padding:0 16px;font-size:14px;color:#F5F5F5;width:100%;outline:none;font-family:inherit;position:relative;z-index:10;">
          </div>

          <div style="margin-bottom:16px;">
            <label for="password" style="display:block;font-size:10px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">Parolă</label>
            <input type="password" id="password" name="password" placeholder="Parola ta" style="height:48px;background:#111;border:1px solid #2A2A2A;border-radius:10px;padding:0 16px;font-size:14px;color:#F5F5F5;width:100%;outline:none;font-family:inherit;position:relative;z-index:10;">
          </div>

          <div class="auth-check">
            <label>
              <input type="checkbox" name="remember" value="1">
              <span>Menține-mă autentificat</span>
            </label>
          </div>

          <button type="submit" class="auth-btn" id="submitBtn">Autentifica-te →</button>
        </form>

        <p class="auth-switch">Nu ai cont? <a href="register.php">Creează unul gratuit</a></p>
      </div>
    </div>
  </div>

  <script src="js/script.js"></script>
</body>

</html>