<?php
session_start();
require_once 'php/auth.php';
require_once 'php/functions.php';
require_once 'php/save_data.php';
requireLogin();

$user   = getCurrentUser();
$userId = $_SESSION['user_id'];
$unit   = $user['unit'] ?? 'kg';

/* ── Handle POST actions ─────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add_workout') {
        $name     = sanitize($_POST['workout_name'] ?? '');
        $date     = sanitize($_POST['workout_date'] ?? date('Y-m-d'));
        $duration = (int)($_POST['duration'] ?? 0);
        $notes    = sanitize($_POST['notes'] ?? '');
        $exercises = [];

        if (isset($_POST['exercises']) && is_array($_POST['exercises'])) {
            foreach ($_POST['exercises'] as $ex) {
                $exName = sanitize($ex['name'] ?? '');
                if (empty($exName)) continue;

                $sets = [];
                if (isset($ex['sets']) && is_array($ex['sets'])) {
                    foreach ($ex['sets'] as $idx => $set) {
                        $reps   = max(1, (int)($set['reps'] ?? 1));
                        $weight = max(0, (float)($set['weight'] ?? 0));
                        $sets[] = [
                            'set_number' => $idx + 1,
                            'reps'       => $reps,
                            'weight'     => $weight
                        ];
                    }
                }

                if (!empty($sets)) {
                    $exercises[] = [
                        'name' => $exName,
                        'sets' => $sets
                    ];
                }
            }
        }

        if ($name && !empty($exercises)) {
            $workout = [
                'id'         => generateId(),
                'name'       => $name,
                'date'       => $date,
                'exercises'  => $exercises,
                'duration'   => $duration,
                'notes'      => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $saved = saveWorkout($userId, $workout);
            header('Location: dashboard.php?' . ($saved ? 'success=1' : 'error=1'));
            exit;
        }
        header('Location: dashboard.php?error=1');
        exit;
    }

    if ($action === 'delete_workout') {
        $wid = sanitize($_POST['workout_id'] ?? '');
        deleteWorkout($userId, $wid);
        header('Location: dashboard.php?deleted=1');
        exit;
    }

    if ($action === 'delete_account') {
        deleteAccount($userId);
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

/* ── Load workouts ───────────────────────────────────────────────────── */
$workouts = getWorkouts($userId);
usort($workouts, fn($a, $b) => strcmp($b['date'], $a['date']));

/* ── Stats ───────────────────────────────────────────────────────────── */
$totalWorkouts = count($workouts);
$dow           = (int)date('N');
$weekStart     = date('Y-m-d', strtotime('-' . ($dow - 1) . ' days'));
$thisWeek      = count(array_filter($workouts, fn($w) => $w['date'] >= $weekStart));

$streak = 0;
if ($totalWorkouts > 0) {
    $dates    = array_unique(array_column($workouts, 'date'));
    rsort($dates);
    $today    = date('Y-m-d');
    $yester   = date('Y-m-d', strtotime('-1 day'));
    if ($dates[0] === $today || $dates[0] === $yester) {
        $expected = $dates[0];
        foreach ($dates as $d) {
            if ($d === $expected) {
                $streak++;
                $expected = date('Y-m-d', strtotime($d . ' -1 day'));
            } else {
                break;
            }
        }
    }
}

$allExNames = [];
foreach ($workouts as $w) {
    foreach ($w['exercises'] as $ex) {
        $allExNames[strtolower(trim($ex['name']))] = true;
    }
}
$prCount = count($allExNames);

$exFreq = [];
foreach ($workouts as $w) {
    foreach ($w['exercises'] as $ex) {
        $key = ucfirst(strtolower(trim($ex['name'])));
        $exFreq[$key] = ($exFreq[$key] ?? 0) + 1;
    }
}
arsort($exFreq);
$topExercises = array_slice($exFreq, 0, 4, true);
$maxFreq      = $topExercises ? max($topExercises) : 1;

$recentWorkouts = array_slice($workouts, 0, 5);

$chartLabels = [];
$chartCounts = [];
for ($i = 7; $i >= 0; $i--) {
    $mon = new DateTime('monday this week');
    $mon->modify("-{$i} weeks");
    $sun = clone $mon;
    $sun->modify('+6 days');
    $mStr = $mon->format('Y-m-d');
    $sStr = $sun->format('Y-m-d');
    $cnt  = count(array_filter($workouts, fn($w) => $w['date'] >= $mStr && $w['date'] <= $sStr));
    $chartLabels[] = $mon->format('d M');
    $chartCounts[] = $cnt;
}

$timeOfDay = getGreeting();
$firstName = explode(' ', $user['name'])[0];
$todayRo   = formatDateRo(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">

        <!-- Sidebar (exact aceeași structură ca în dashboard) -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-logo">WT.</div>
            <div class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active" title="Dashboard">
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
                <a href="#history" class="sidebar-link" title="Istoric">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6" />
                        <line x1="8" y1="12" x2="21" y2="12" />
                        <line x1="8" y1="18" x2="21" y2="18" />
                        <line x1="3" y1="6" x2="3.01" y2="6" />
                        <line x1="3" y1="12" x2="3.01" y2="12" />
                        <line x1="3" y1="18" x2="3.01" y2="18" />
                    </svg>
                </a>
                <a href="contact.php" class="sidebar-link" title="Contact">
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

        <!-- Main content -->
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <h1 class="greeting">
                        Bună <?php echo $timeOfDay; ?>, <?php echo $firstName; ?>
                        <span class="arrow">&#8599;</span>
                    </h1>
                    <p class="topbar-date"><?php echo $todayRo; ?></p>
                </div>
                <div class="topbar-right">
                    <button class="btn-primary" data-modal-open="workoutModal">+ Antrenament nou</button>
                    <div class="avatar" onclick="openModal('profileModal')"><?php echo mb_substr($user['name'], 0, 2, 'UTF-8'); ?></div>
                </div>
            </div>

            <?php if (isset($_GET['welcome'])): ?>
                <div class="alert alert-success">&#10003; Bun venit! Contul tău a fost creat. Adaugă primul antrenament.</div>
            <?php elseif (isset($_GET['success'])): ?>
                <div class="alert alert-success">&#10003; Antrenamentul a fost salvat cu succes!</div>
            <?php elseif (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">&#10003; Antrenamentul a fost șters.</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-error">&#9888; Eroare la salvare. Verifică datele introduse.</div>
            <?php endif; ?>

            <?php if ($totalWorkouts === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">&#127947;</div>
                    <h2>Niciun antrenament înregistrat</h2>
                    <p>Înregistrează primul antrenament și începe să-ți urmărești progresul.</p>
                    <button class="btn-primary" data-modal-open="workoutModal">+ Înregistrează primul antrenament</button>
                </div>
            <?php else: ?>
                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-icon">&#9776;</span><span class="stat-value"><?php echo $totalWorkouts; ?></span><span class="stat-label">Total antrenamente</span></div>
                    <div class="stat-card"><span class="stat-icon">&#128197;</span><span class="stat-value"><?php echo $thisWeek; ?></span><span class="stat-label">Săptămâna aceasta</span></div>
                    <div class="stat-card"><span class="stat-icon">&#9889;</span><span class="stat-value"><?php echo $streak; ?></span><span class="stat-label">Zile consecutive</span></div>
                    <div class="stat-card"><span class="stat-icon">&#9733;</span><span class="stat-value"><?php echo $prCount; ?></span><span class="stat-label">Exerciții unice</span></div>
                </div>

                <div class="dash-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3>Activitate săptămânală</h3><span style="font-size:0.8rem;color:var(--muted)">ultimele 8 săptămâni</span>
                        </div>
                        <div class="chart-container"><canvas id="workoutChart"></canvas></div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Exerciții frecvente</h3>
                        </div><?php if (!empty($topExercises)): ?><div class="volume-list"><?php foreach ($topExercises as $name => $freq): ?><div class="volume-item">
                                        <div class="volume-item-top"><span><?php echo sanitize($name); ?></span><span><?php echo $freq; ?> sesiuni</span></div>
                                        <div class="volume-bar-track">
                                            <div class="volume-bar-fill" style="width:<?php echo round($freq / $maxFreq * 100); ?>%"></div>
                                        </div>
                                    </div><?php endforeach; ?></div><?php else: ?><p style="font-size:0.85rem;">Adaugă antrenamente pentru a vedea statisticile.</p><?php endif; ?>
                    </div>
                </div>

                <div class="card" id="history">
                    <div class="card-header">
                        <h3>Antrenamente recente</h3><?php if ($totalWorkouts > 5): ?><span style="font-size:0.8rem;color:var(--muted)"><?php echo $totalWorkouts; ?> total</span><?php endif; ?>
                    </div>
                    <table class="workout-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Antrenament</th>
                                <th>Exerciții</th>
                                <th>Durată</th>
                                <th class="td-actions"></th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($recentWorkouts as $w): ?><tr>
                                    <td><span class="workout-date-badge"><?php echo formatDate($w['date']); ?></span></td>
                                    <td class="workout-name"><?php echo sanitize($w['name']); ?></td>
                                    <td><?php $shown = array_slice($w['exercises'], 0, 3);
                                        foreach ($shown as $ex): ?><span class="exercise-tag"><?php echo sanitize($ex['name']); ?></span><?php endforeach;
                                                                                                                                                                                        if (count($w['exercises']) > 3): ?><span class="exercise-tag">+<?php echo count($w['exercises']) - 3; ?></span><?php endif; ?></td>
                                    <td><?php echo $w['duration'] ? $w['duration'] . ' min' : '—'; ?></td>
                                    <td class="td-actions">
                                        <form method="POST" action="dashboard.php" style="display:inline"><input type="hidden" name="action" value="delete_workout"><input type="hidden" name="workout_id" value="<?php echo sanitize($w['id']); ?>"><button type="submit" class="btn-danger btn-sm" data-confirm="Ștergi antrenamentul «<?php echo sanitize($w['name']); ?>»?">&#x2715;</button></form>
                                    </td>
                                </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Adaugă antrenament (cu suport seturi multiple) -->
    <div class="modal" id="workoutModal">
        <div class="modal-overlay" data-modal-close="workoutModal"></div>
        <div class="modal-box">
            <div class="modal-header">
                <h2>Antrenament nou</h2><button class="modal-close" data-modal-close="workoutModal">&#x2715;</button>
            </div>
            <form method="POST" action="dashboard.php" id="workoutForm" data-validate>
                <input type="hidden" name="action" value="add_workout">
                <div class="form-row">
                    <div class="form-group"><label for="workout_name">Nume antrenament</label><input type="text" id="workout_name" name="workout_name" placeholder="Ex: Antrenament piept" required></div>
                    <div class="form-group"><label for="workout_date">Data</label><input type="date" id="workout_date" name="workout_date" value="<?php echo date('Y-m-d'); ?>"></div>
                </div>

                <!-- Zona cu exerciții și seturi -->
                <div class="form-group">
                    <label>Exerciții</label>
                    <div id="exercisesContainer"></div>
                    <button type="button" class="btn-add-exercise" id="addExerciseBtn">+ Adaugă exercițiu</button>
                </div>

                <div class="form-row">
                    <div class="form-group"><label for="duration">Durată (minute)</label><input type="number" id="duration" name="duration" placeholder="60" min="0"></div>
                    <div class="form-group"><label for="notes">Note</label><input type="text" id="notes" name="notes" placeholder="Observații..."></div>
                </div>
                <div class="form-actions"><button type="button" class="btn-ghost" data-modal-close="workoutModal">Anulează</button><button type="submit" class="btn-primary">Salvează antrenamentul</button></div>
            </form>
        </div>
    </div>

    <!-- Modal profil -->
    <div class="modal" id="profileModal">
        <div class="modal-overlay" data-modal-close="profileModal"></div>
        <div class="modal-box profile-modal-box">
            <div class="modal-header">
                <div class="profile-modal-user">
                    <div class="profile-modal-avatar"><?php echo mb_substr($user['name'], 0, 2, 'UTF-8'); ?></div>
                    <div>
                        <p class="profile-modal-name"><?php echo sanitize($user['name']); ?></p>
                        <p class="profile-modal-email"><?php echo sanitize($user['email']); ?></p>
                    </div>
                </div><button class="modal-close" data-modal-close="profileModal">&#x2715;</button>
            </div>
            <div class="profile-menu"><a href="logout.php" class="profile-menu-item"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg> Deconectare</a>
                <form method="POST" action="dashboard.php"><input type="hidden" name="action" value="delete_account"><button type="submit" class="profile-menu-item profile-menu-danger" data-confirm="Ești sigur? Contul și toate antrenamentele tale vor fi șterse permanent."><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6M14 11v6" />
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                        </svg> Șterge cont</button></form>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        (function() {
            var ctx = document.getElementById('workoutChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($chartCounts); ?>,
                        backgroundColor: 'rgba(200,255,0,0.65)',
                        borderColor: '#C8FF00',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ' ' + ctx.parsed.y + ' antrenament' + (ctx.parsed.y !== 1 ? 'e' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#888',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            },
                            grid: {
                                color: 'rgba(42,42,42,0.8)'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#888',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        })();
    </script>
</body>

</html>