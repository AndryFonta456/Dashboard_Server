<?php
// ============================================================
// index.php — Pagina di login
// ============================================================

require_once __DIR__ . '/auth.php';

// Già loggato → vai alla dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Gestione form
$error = handleLoginPost();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Server — Login</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body class="page-login">

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
       CONTENUTO
  ===================================================== -->
  <main class="login-wrap">

    <div class="login-card glass">

      <!-- Logo / titolo -->
      <div class="login-header">
        <div class="logo-mark">
          <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="4"  y="4"  width="14" height="14" rx="3" fill="currentColor" opacity=".9"/>
            <rect x="22" y="4"  width="14" height="14" rx="3" fill="currentColor" opacity=".5"/>
            <rect x="4"  y="22" width="14" height="14" rx="3" fill="currentColor" opacity=".5"/>
            <rect x="22" y="22" width="14" height="14" rx="3" fill="currentColor" opacity=".9"/>
          </svg>
        </div>
        <h1 class="login-title">Home Server</h1>
        <p class="login-sub">Accedi al pannello di controllo</p>
      </div>

      <!-- Messaggio di errore -->
      <?php if ($error): ?>
        <div class="alert alert-error" role="alert">
          <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <!-- Form login -->
      <form class="login-form" method="POST" action="index.php" novalidate>

        <div class="field-group">
          <label class="field-label" for="username">Username</label>
          <div class="field-wrap">
            <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
            <input
              class="field-input"
              type="text"
              id="username"
              name="username"
              autocomplete="username"
              placeholder="Inserisci username"
              value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              required
            >
          </div>
        </div>

        <div class="field-group">
          <label class="field-label" for="password">Password</label>
          <div class="field-wrap">
            <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <input
              class="field-input"
              type="password"
              id="password"
              name="password"
              autocomplete="current-password"
              placeholder="••••••••"
              required
            >
            <button type="button" class="toggle-pw" aria-label="Mostra/nascondi password" onclick="togglePassword(this)">
              <svg class="eye-icon eye-show" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
              <svg class="eye-icon eye-hide" viewBox="0 0 20 20" fill="currentColor" style="display:none"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <span>Accedi</span>
          <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
        </button>

      </form>
    </div>

  </main>

  <script src="main.js"></script>
  <script>
    function togglePassword(btn) {
      const input = btn.closest('.field-wrap').querySelector('.field-input');
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.querySelector('.eye-show').style.display = isText ? '' : 'none';
      btn.querySelector('.eye-hide').style.display = isText ? 'none' : '';
    }
  </script>
</body>
</html>
