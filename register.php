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
            $_SESSION['user_id'] = $user['id'];
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
        "Începe înainte să fii pregătit."
      </blockquote>
      <cite>— Primul pas contează cel mai mult</cite>
    </div>
    <p class="split-tag">Workout Tracker &copy; <?php echo date('Y'); ?></p>
  </div>

  <!-- Right panel -->
  <div class="split-right">
    <div class="split-form-wrap">
      <h2>Creează cont</h2>
      <p class="sub">Gratuit, pentru totdeauna. Fără card de credit.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">&#x26A0; <?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php" data-validate>
        <div class="form-group">
          <label for="name">Nume complet</label>
          <input type="text" id="name" name="name"
                 placeholder="Ion Popescu"
                 value="<?php echo sanitize($_POST['name'] ?? ''); ?>"
                 required autofocus>
        </div>
        <div class="form-group">
          <label for="email">Adresă de email</label>
          <input type="email" id="email" name="email"
                 placeholder="exemplu@email.com"
                 value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                 required>
        </div>
        <div class="form-group">
          <label for="password">Parolă</label>
          <input type="password" id="password" name="password"
                 placeholder="Minim 6 caractere" required>
          <span class="form-hint">Cel puțin 6 caractere.</span>
        </div>
        <div class="form-group">
          <label for="password_confirm">Confirmă parola</label>
          <input type="password" id="password_confirm" name="password_confirm"
                 placeholder="Repetă parola" required>
        </div>
        <button type="submit" class="btn-primary btn-full" style="margin-top:8px;">
          Creează cont &rarr;
        </button>
      </form>

      <div class="form-footer">
        Ai deja cont?
        <a href="login.php">Autentifică-te</a>
      </div>
    </div>
  </div>

</div>

<script src="js/script.js"></script>
</body>
</html>