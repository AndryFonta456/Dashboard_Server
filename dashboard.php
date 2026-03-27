<?php
// ============================================================
// dashboard.php — Dashboard principale
// ============================================================

require_once __DIR__ . '/auth.php';
requireLogin();

// Recupera tutti i servizi dal database
$db       = getDB();
$stmt     = $db->query('SELECT id, service_name, url, icon, username, password FROM services ORDER BY service_name ASC');
$services = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Server — Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="page-dashboard">

  <!-- ====================================================
       VIDEO BACKGROUND
  ===================================================== -->
  <div class="video-wrap">
    <video class="video-bg" autoplay muted loop playsinline>
      <source src="video/background.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
  </div>

  <!-- ====================================================
       HEADER
  ===================================================== -->
  <header class="dash-header glass">
    <div class="dash-header-inner">
      <div class="dash-brand">
        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="brand-logo">
          <rect x="4"  y="4"  width="14" height="14" rx="3" fill="currentColor" opacity=".9"/>
          <rect x="22" y="4"  width="14" height="14" rx="3" fill="currentColor" opacity=".5"/>
          <rect x="4"  y="22" width="14" height="14" rx="3" fill="currentColor" opacity=".5"/>
          <rect x="22" y="22" width="14" height="14" rx="3" fill="currentColor" opacity=".9"/>
        </svg>
        <span class="brand-name">Home Server</span>
      </div>

      <nav class="dash-nav">
        <a href="dashboard.php" class="nav-link nav-link--active">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
          Dashboard
        </a>
        <a href="add.php" class="nav-link">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
          Aggiungi
        </a>
      </nav>

      <div class="dash-user">
        <span class="user-name">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
          <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="logout.php" class="btn-logout">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
          Esci
        </a>
      </div>
    </div>
  </header>

  <!-- ====================================================
       CONTENUTO PRINCIPALE
  ===================================================== -->
  <main class="dash-main">

    <div class="dash-hero">
      <h1 class="dash-title">I tuoi servizi</h1>
      <p class="dash-sub">Passa il cursore su una card per vedere le credenziali</p>
    </div>

    <?php if (empty($services)): ?>
      <div class="empty-state glass">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="8" width="32" height="32" rx="6" stroke="currentColor" stroke-width="2"/>
          <path d="M24 18v8M24 30h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p>Nessun servizio trovato.</p>
        <a href="add.php" class="btn-add-first">Aggiungi il primo servizio</a>
      </div>
    <?php else: ?>
      <div class="services-grid">
        <?php foreach ($services as $svc): ?>
          <?php
            $name = htmlspecialchars($svc['service_name'], ENT_QUOTES, 'UTF-8');
            $url  = htmlspecialchars($svc['url'],          ENT_QUOTES, 'UTF-8');
            $user = $svc['username'] ? htmlspecialchars($svc['username'], ENT_QUOTES, 'UTF-8') : null;
            $pass = $svc['password'] ? htmlspecialchars($svc['password'], ENT_QUOTES, 'UTF-8') : null;

            // Genera iniziali per avatar
            $words    = explode(' ', $svc['service_name']);
            $initials = mb_strtoupper(mb_substr($words[0], 0, 1));
            if (isset($words[1])) {
                $initials .= mb_strtoupper(mb_substr($words[1], 0, 1));
            }

            // Colori avatar ciclici
            static $colorIndex = 0;
            $colors = ['--accent-blue','--accent-teal','--accent-purple','--accent-coral','--accent-amber','--accent-green'];
            $accentColor = $colors[$colorIndex % count($colors)];
            $colorIndex++;
          ?>
          <a href="<?= $url ?>" target="_blank" rel="noopener noreferrer" class="service-card glass" aria-label="Apri <?= $name ?>">

            <!-- Avatar / icona -->
            <?php if (!empty($svc['icon'])): ?>
              <div class="card-avatar card-avatar--has-img" style="--card-accent: var(<?= $accentColor ?>)">
                <img class="avatar-image" src="<?= htmlspecialchars($svc['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="Icona <?= $name ?>" loading="lazy" onerror="this.closest('.card-avatar').classList.add('no-img'); this.remove();" />
                <span class="avatar-letters"><?= $initials ?></span>
                <div class="avatar-ring"></div>
              </div>
            <?php else: ?>
              <div class="card-avatar" style="--card-accent: var(<?= $accentColor ?>)">
                <span class="avatar-letters"><?= $initials ?></span>
                <div class="avatar-ring"></div>
              </div>
            <?php endif; ?>

            <!-- Info sempre visibili -->
            <div class="card-info">
              <h2 class="card-title"><?= $name ?></h2>
              <span class="card-url"><?= $url ?></span>
            </div>

            <!-- Credenziali — visibili solo all'hover -->
            <div class="card-creds" aria-hidden="true">
              <?php if ($user): ?>
                <div class="cred-row">
                  <svg class="cred-icon" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 8a3 3 0 100-6 3 3 0 000 6zM8 9a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span class="cred-label">User</span>
                  <span class="cred-value"><?= $user ?></span>
                  <button class="copy-btn" data-copy="<?= $user ?>" onclick="copyText(event, this)" title="Copia username">
                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L13.414 4A2 2 0 0114 5.414V12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2-1a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1V5.414a1 1 0 00-.293-.707L11.293 3.293A1 1 0 0010.586 3H6z"/><path d="M2 6a2 2 0 012-2v1a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1v-1h1a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                  </button>
                </div>
              <?php endif; ?>
              <?php if ($pass): ?>
                <div class="cred-row">
                  <svg class="cred-icon" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M5 8a3 3 0 016 0v1h1a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V10a1 1 0 011-1h1V8zm3-2a2 2 0 00-2 2v1h4V8a2 2 0 00-2-2z"/>
                  </svg>
                  <span class="cred-label">Pass</span>
                  <span class="cred-value cred-pass">••••••••</span>
                  <button class="copy-btn" data-copy="<?= $pass ?>" onclick="copyText(event, this)" title="Copia password">
                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L13.414 4A2 2 0 0114 5.414V12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2-1a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1V5.414a1 1 0 00-.293-.707L11.293 3.293A1 1 0 0010.586 3H6z"/><path d="M2 6a2 2 0 012-2v1a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1v-1h1a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                  </button>
                </div>
              <?php endif; ?>
              <?php if (!$user && !$pass): ?>
                <span class="no-creds">Nessuna credenziale</span>
              <?php endif; ?>
            </div>

            <!-- Freccia apertura -->
            <div class="card-arrow">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </div>

          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

  <!-- Toast notifica copia -->
  <div class="toast" id="copy-toast" aria-live="polite">Copiato!</div>

  <script src="main.js"></script>
</body>
</html>
