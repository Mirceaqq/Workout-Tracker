<?php
session_start();
require_once 'php/auth.php';
require_once 'php/functions.php';
requireLogin();

$user = getCurrentUser();
if (!$user) {
    header('Location: logout.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$initials = strtoupper(mb_substr($user['name'], 0, 2, 'UTF-8'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cName    = sanitize($_POST['name']    ?? '');
    $cEmail   = sanitize($_POST['email']   ?? '');
    $cSubject = sanitize($_POST['subject'] ?? '');
    $cMessage = sanitize($_POST['message'] ?? '');

    if (empty($cName) || empty($cEmail) || empty($cSubject) || empty($cMessage)) {
        $error = 'Completează toate câmpurile.';
    } elseif (!filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresa de email nu este validă.';
    } else {
        $contacts   = readJson(__DIR__ . '/data/contacts.json');
        $contacts[] = [
            'id'         => generateId(),
            'user_id'    => $userId,
            'name'       => $cName,
            'email'      => $cEmail,
            'subject'    => $cSubject,
            'message'    => $cMessage,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        writeJson(__DIR__ . '/data/contacts.json', $contacts);
        header('Location: contact.php?sent=1');
        exit;
    }
}

$sent = isset($_GET['sent']);
?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">
        <!-- Sidebar identică cu dashboard.php -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-logo">WT.</div>
            <div class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link" title="Dashboard">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                </a>
                <a href="#" class="sidebar-link" data-modal-open="workoutModal" title="Antrenament nou">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </a>
                <a href="dashboard.php#history" class="sidebar-link" title="Istoric">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6" />
                        <line x1="8" y1="12" x2="21" y2="12" />
                        <line x1="8" y1="18" x2="21" y2="18" />
                        <line x1="3" y1="6" x2="3.01" y2="6" />
                        <line x1="3" y1="12" x2="3.01" y2="12" />
                        <line x1="3" y1="18" x2="3.01" y2="18" />
                    </svg>
                </a>
                <a href="contact.php" class="sidebar-link active" title="Contact">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </a>
            </div>
            <div class="sidebar-bottom">
                <div class="sidebar-divider"></div>
                <a href="logout.php" class="sidebar-link" title="Deconectare" data-confirm="Ești sigur că vrei să te deconectezi?">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                </a>
            </div>
        </nav>

        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <h1 class="greeting">Contact</h1>
                    <p class="topbar-date"><?php echo formatDateRo(date('Y-m-d')); ?></p>
                </div>
                <div class="topbar-right">
                    <div class="avatar" onclick="openModal('profileModal')"><?php echo $initials; ?></div>
                </div>
            </div>

            <div class="contact-card">
                <h2 class="contact-title">Trimite un mesaj</h2>
                <p class="contact-subtitle">Ai o întrebare sau o sugestie? Răspundem în cel mult 24 de ore.</p>

                <?php if ($sent): ?>
                    <div class="contact-success">
                        <div class="contact-success-icon">✓</div>
                        <p class="contact-success-title">Mesaj trimis!</p>
                        <p class="contact-success-msg">Te vom contacta în curând.</p>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" data-validate>
                        <div class="form-group">
                            <label for="c-name">Nume</label>
                            <input type="text" id="c-name" name="name" placeholder="Ion Popescu" value="<?php echo sanitize($_POST['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="c-email">Email</label>
                            <input type="email" id="c-email" name="email" placeholder="exemplu@email.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="c-subject">Subiect</label>
                            <input type="text" id="c-subject" name="subject" placeholder="Ex: Problemă cu contul" value="<?php echo sanitize($_POST['subject'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="c-message">Mesaj</label>
                            <textarea id="c-message" name="message" placeholder="Scrie mesajul tău..." required><?php echo sanitize($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn-primary" style="background: #C8FF00; color: #000;">Trimite mesajul →</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal profil (necesar pentru avatar) -->
    <div class="modal" id="profileModal">
        <div class="modal-overlay" data-modal-close="profileModal"></div>
        <div class="modal-box profile-modal-box">
            <div class="modal-header">
                <div class="profile-modal-user">
                    <div class="profile-modal-avatar"><?php echo $initials; ?></div>
                    <div>
                        <p class="profile-modal-name"><?php echo sanitize($user['name']); ?></p>
                        <p class="profile-modal-email"><?php echo sanitize($user['email']); ?></p>
                    </div>
                </div>
                <button class="modal-close" data-modal-close="profileModal">✕</button>
            </div>
            <div class="profile-menu">
                <a href="logout.php" class="profile-menu-item"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg> Deconectare</a>
                <form method="POST" action="dashboard.php"><input type="hidden" name="action" value="delete_account"><button type="submit" class="profile-menu-item profile-menu-danger" data-confirm="Ești sigur? Contul și toate antrenamentele tale vor fi șterse permanent."><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6M14 11v6" />
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                        </svg> Șterge cont</button></form>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>