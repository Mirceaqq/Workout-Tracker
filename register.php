<?php
session_start();
require_once 'php/auth.php';
requireGuest();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = sanitize($_POST['name']     ?? '');
  $email    = sanitize($_POST['email']    ?? '');
  $password = $_POST['password']         ?? '';
  $confirm  = $_POST['password_confirm'] ?? '';

  if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
    $error = 'Completează toate câmpurile.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Adresa de email nu este validă.';
  } elseif (strlen($password) < 6) {
    $error = 'Parola trebuie să aibă cel puțin 6 caractere.';
  } elseif ($password !== $confirm) {
    $error = 'Parolele nu coincid.';
  } else {
    $user = registerUser($name, $email, $password);
    if ($user) {
      session_regenerate_id(true);
      $_SESSION['user_id']   = $user['id'];
      $_SESSION['user_name'] = $user['name'];
      header('Location: dashboard.php?welcome=1');
      exit;
    } else {
      $error = 'Această adresă de email este deja înregistrată.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Înregistrare — Workout Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">

  <div class="auth-page">

    <!-- Left panel -->
    <div class="auth-left">
      <a href="index.php" class="auth-logo">WT.</a>

      <div class="auth-left-body">
        <div class="auth-accent-line"></div>
        <blockquote class="auth-quote">
          "Începe înainte să fii pregătit."
        </blockquote>
        <p class="auth-quote-cite">— Primul pas contează cel mai mult</p>
      </div>

      <p class="auth-left-footer">Workout Tracker &middot; <?php echo date('Y'); ?></p>
    </div>

    <!-- Right panel -->
    <div class="auth-right">
      <div class="auth-form-wrap">

        <h1 class="auth-title">Creează cont</h1>
        <p class="auth-subtitle">Gratuit, pentru totdeauna.</p>

        <?php if ($error): ?>
          <div class="auth-alert">&#x26A0;&nbsp; <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">

          <div class="auth-field">
            <label for="name">Nume complet</label>
            <input type="text" id="name" name="name"
              placeholder="Ion Popescu"
              value="<?php echo sanitize($_POST['name'] ?? ''); ?>"
              required autofocus>
          </div>

          <div class="auth-field">
            <label for="email">Adresă de email</label>
            <input type="email" id="email" name="email"
              placeholder="exemplu@email.com"
              value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
              required>
          </div>

          <div class="auth-field">
            <label for="password">Parolă</label>
            <input type="password" id="password" name="password"
              placeholder="Minim 6 caractere" required>
          </div>

          <div class="auth-field">
            <label for="password_confirm">Confirmă parola</label>
            <input type="password" id="password_confirm" name="password_confirm"
              placeholder="Repetă parola" required>
          </div>

          <button type="submit" class="auth-btn">
            Creează cont &rarr;
          </button>

        </form>

        <p class="auth-switch">
          Ai deja cont?
          <a href="login.php">Autentifică-te</a>
        </p>

      </div>
    </div>

  </div>

  <script src="js/script.js"></script>
</body>

</html>