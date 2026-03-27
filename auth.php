<?php
// ============================================================
// auth.php — Autenticazione utenti
// ============================================================

require_once __DIR__ . '/config.php';

// Configurazione sessione sicura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// -----------------------------------------------------------
// Funzione: verifica se l'utente è loggato
// -----------------------------------------------------------
function isLoggedIn(): bool {
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

// -----------------------------------------------------------
// Funzione: redirect alla login se non autenticato
// -----------------------------------------------------------
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// -----------------------------------------------------------
// Funzione: login
// -----------------------------------------------------------
function attemptLogin(string $username, string $password): bool {
    $db = getDB();

    $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        error_log('Statement prepare failed: ' . $db->error);
        return false;
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user) {
        error_log('attemptLogin: user found=' . $user['username'] . ' id=' . $user['id']);
        if (password_verify($password, $user['password'])) {
            error_log('attemptLogin: password_verify OK for user=' . $user['username']);
            // Rigenera ID sessione per prevenire session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_at'] = time();

            return true;
        }
        error_log('attemptLogin: password_verify FAILED for user=' . $user['username']);
    } else {
        error_log('attemptLogin: user not found for username=' . $username);
    }

    return false;
}

// -----------------------------------------------------------
// Funzione: logout
// -----------------------------------------------------------
function doLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}

// -----------------------------------------------------------
// Gestione POST login (chiamato da index.php)
// -----------------------------------------------------------
function handleLoginPost(): ?string {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        return 'Inserisci username e password.';
    }

    if (attemptLogin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    }

    return 'Credenziali non valide. Riprova.';
}
