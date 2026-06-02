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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="split-page">

  <!-- Left panel -->
  <div class="split-left">
    <a href="index.php" class="split-brand">WT.</a>
    <div class="split-quote">
      <blockquote>
        "Constanța bate intensitatea."
      </blockquote>
      <cite>— Principiul progresului</cite>
    </div>
    <p class="split-tag">Workout Tracker &copy; <?php echo date('Y'); ?></p>
  </div>

  <!-- Right panel -->
  <div class="split-right">
    <div class="split-form-wrap">
      <h2>Bun venit înapoi</h2>
      <p class="sub">Autentifică-te pentru a continua.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">&#x26A0; <?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" data-validate>
        <div class="form-group">
          <label for="email">Adresă de email</label>
          <input type="email" id="email" name="email"
                 placeholder="exemplu@email.com"
                 value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                 required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Parolă</label>
          <input type="password" id="password" name="password"
                 placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" name="remember" value="1">
            Menține-mă autentificat
          </label>
        </div>
        <button type="submit" class="btn-primary btn-full" style="margin-top:8px;">
          Autentifică-te &rarr;
        </button>
      </form>

      <div class="form-footer">
        Nu ai cont?
        <a href="register.php">Creează unul gratuit</a>
      </div>
    </div>
  </div>

</div>

<script src="js/script.js"></script>
</body>
</html>