<?php
// ============================================================
// add.php — Aggiunta utenti e servizi
// ============================================================

require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();

$errors   = [];
$success  = '';
$activeTab = $_POST['tab'] ?? ($_GET['tab'] ?? 'service');

// ============================================================
// POST: aggiunta servizio
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'service') {
    $serviceName = trim($_POST['service_name'] ?? '');
    $serviceUrl  = trim($_POST['service_url']  ?? '');
    $serviceIcon = trim($_POST['service_icon'] ?? '');
    $serviceUser = trim($_POST['service_user'] ?? '');
    $servicePass = $_POST['service_pass']      ?? '';

    if ($serviceName === '') $errors[] = 'Il nome del servizio è obbligatorio.';
    if ($serviceUrl === '')  $errors[] = "L'URL del servizio è obbligatorio.";
    if (!filter_var($serviceUrl, FILTER_VALIDATE_URL)) $errors[] = "L'URL non è valido.";
    if ($serviceIcon !== '' && !filter_var($serviceIcon, FILTER_VALIDATE_URL)) $errors[] = "L'URL dell'icona non è valido.";

    $uploadedIcon = null;
    if (!empty($_FILES['service_icon_file']) && $_FILES['service_icon_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['service_icon_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Errore caricamento immagine: ' . $file['error'];
        } else {
            $mime = mime_content_type($file['tmp_name']);
            $allowed = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
            if (!in_array($mime, $allowed, true)) {
                $errors[] = 'Formato immagine non supportato. Usa png/jpg/webp/svg.';
            } else {
                $uploadDir = __DIR__ . '/img/services';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $targetName = sprintf('%s_%s.%s', $filename, uniqid(), $ext);
                $targetPath = $uploadDir . '/' . $targetName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'Impossibile salvare il file immagine. Controlla permessi e che la cartella img/services sia scrivibile. Destinazione: ' . $targetPath;
                } else {
                    $uploadedIcon = 'img/services/' . $targetName;
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO services (service_name, url, icon, username, password) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            $errors[] = 'Errore database: ' . $db->error;
        } else {
            $iconParam = $uploadedIcon ?? ($serviceIcon !== '' ? $serviceIcon : null);
            $userParam = $serviceUser !== '' ? $serviceUser : null;
            $passParam = $servicePass !== '' ? $servicePass : null;
            $stmt->bind_param('sssss', $serviceName, $serviceUrl, $iconParam, $userParam, $passParam);
            $stmt->execute();
            $stmt->close();
            $success  = 'Servizio aggiunto con successo!';
            $activeTab = 'service';
        }
    }
}

// ============================================================
// POST: aggiunta utente
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'user') {
    $newUsername = trim($_POST['new_username'] ?? '');
    $newPassword = $_POST['new_password']      ?? '';
    $confirmPass = $_POST['confirm_password']  ?? '';

    if ($newUsername === '')  $errors[] = 'Lo username è obbligatorio.';
    if ($newPassword === '')  $errors[] = 'La password è obbligatoria.';
    if (strlen($newPassword) < 8) $errors[] = 'La password deve avere almeno 8 caratteri.';
    if ($newPassword !== $confirmPass) $errors[] = 'Le password non coincidono.';

    if (empty($errors)) {
        // Controlla username duplicato
        $check = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        if (!$check) {
            $errors[] = 'Errore database: ' . $db->error;
        } else {
            $check->bind_param('s', $newUsername);
            $check->execute();
            $res = $check->get_result();
            if ($res && $res->fetch_assoc()) {
                $errors[] = 'Lo username esiste già.';
            } else {
                $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt   = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
                if (!$stmt) {
                    $errors[] = 'Errore database: ' . $db->error;
                } else {
                    $stmt->bind_param('ss', $newUsername, $hashed);
                    $stmt->execute();
                    $stmt->close();
                    $success  = 'Utente creato con successo!';
                    $activeTab = 'user';
                }
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Server — Aggiungi</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/add.css">
</head>
<body class="page-add">

  <!-- VIDEO BACKGROUND -->
  <div class="video-wrap">
    <video class="video-bg" autoplay muted loop playsinline>
      <source src="video/background.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
  </div>

  <!-- HEADER -->
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
        <a href="dashboard.php" class="nav-link">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
          Dashboard
        </a>
        <a href="add.php" class="nav-link nav-link--active">
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

  <!-- CONTENUTO -->
  <main class="add-main">

    <div class="add-card glass">

      <div class="add-header">
        <h1 class="add-title">Aggiungi</h1>
        <p class="add-sub">Gestisci servizi e utenti del pannello</p>
      </div>

      <!-- Tab switcher -->
      <div class="tab-bar" role="tablist">
        <button
          class="tab-btn <?= $activeTab === 'service' ? 'tab-btn--active' : '' ?>"
          role="tab"
          aria-selected="<?= $activeTab === 'service' ? 'true' : 'false' ?>"
          onclick="switchTab('service', this)"
        >
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
          Nuovo servizio
        </button>
        <button
          class="tab-btn <?= $activeTab === 'user' ? 'tab-btn--active' : '' ?>"
          role="tab"
          aria-selected="<?= $activeTab === 'user' ? 'true' : 'false' ?>"
          onclick="switchTab('user', this)"
        >
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/></svg>
          Nuovo utente
        </button>
      </div>

      <!-- Feedback -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <!-- ===============================
           TAB: Servizio
      ================================ -->
      <div id="tab-service" class="tab-panel <?= $activeTab === 'service' ? 'tab-panel--active' : '' ?>" role="tabpanel">
        <form method="POST" action="add.php" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="tab" value="service">

          <div class="form-grid">
            <div class="field-group">
              <label class="field-label" for="service_name">Nome servizio <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="text" id="service_name" name="service_name" placeholder="es. Portainer"
                  value="<?= htmlspecialchars($_POST['service_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="service_url">URL <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="url" id="service_url" name="service_url" placeholder="http://192.168.1.10:9000"
                  value="<?= htmlspecialchars($_POST['service_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="service_icon_file">Icona (file locale)</label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm0 2h12v10H4V5z"/></svg>
                <input class="field-input" type="file" id="service_icon_file" name="service_icon_file" accept="image/png,image/jpeg,image/webp,image/svg+xml">
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="service_icon">Icona (URL)</label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm0 2h12v10H4V5z"/></svg>
                <input class="field-input" type="url" id="service_icon" name="service_icon" placeholder="https://example.com/icon.png"
                  value="<?= htmlspecialchars($_POST['service_icon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="service_user">Username</label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="text" id="service_user" name="service_user" placeholder="Facoltativo"
                  value="<?= htmlspecialchars($_POST['service_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="service_pass">Password</label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="password" id="service_pass" name="service_pass" placeholder="Facoltativo">
              </div>
            </div>
          </div>

          <button type="submit" class="btn-submit">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            Aggiungi servizio
          </button>
        </form>
      </div>

      <!-- ===============================
           TAB: Utente
      ================================ -->
      <div id="tab-user" class="tab-panel <?= $activeTab === 'user' ? 'tab-panel--active' : '' ?>" role="tabpanel">
        <form method="POST" action="add.php" novalidate>
          <input type="hidden" name="tab" value="user">

          <div class="form-grid">
            <div class="field-group field-group--full">
              <label class="field-label" for="new_username">Username <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="text" id="new_username" name="new_username" placeholder="Scegli uno username"
                  value="<?= htmlspecialchars($_POST['new_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="new_password">Password <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="password" id="new_password" name="new_password" placeholder="Min. 8 caratteri" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="confirm_password">Conferma password <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                <input class="field-input" type="password" id="confirm_password" name="confirm_password" placeholder="Ripeti la password" required>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-submit">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/></svg>
            Crea utente
          </button>
        </form>
      </div>

    </div>
  </main>

  <script src="main.js"></script>
  <script>
    function switchTab(tab, btn) {
      document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('tab-btn--active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('tab-panel--active'));
      btn.classList.add('tab-btn--active');
      btn.setAttribute('aria-selected', 'true');
      document.getElementById('tab-' + tab).classList.add('tab-panel--active');
    }
  </script>
</body>
</html>
