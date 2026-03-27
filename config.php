<?php
// ============================================================
// config.php — Connessione al database (PDO)
// ============================================================

require_once __DIR__ . '/_db.php';

$mysql_hostname = $mysql_hostname ?? '127.0.0.1';
$mysql_username = $mysql_username ?? 'root';
$mysql_password = $mysql_password ?? '';
$mysql_db       = $mysql_db       ?? 'home_dashboard';
$mysql_charset  = $mysql_charset  ?? 'utf8mb4';

define('DB_CHARSET', $mysql_charset);

// Durata sessione in secondi (8 ore)
define('SESSION_LIFETIME', 28800);

function getDB(): mysqli {
    static $mysqli = null;

    if ($mysqli === null) {
        global $mysql_hostname, $mysql_username, $mysql_password, $mysql_db;

        $mysqli = new mysqli($mysql_hostname, $mysql_username, $mysql_password, $mysql_db);

        if ($mysqli->connect_error) {
            error_log('DB connection failed: ' . $mysqli->connect_error);
            http_response_code(503);
            die('Servizio temporaneamente non disponibile. Controlla la configurazione DB.');
        }

        if (!$mysqli->set_charset(DB_CHARSET)) {
            error_log('DB charset set failed: ' . $mysqli->error);
            // non blocchiamo con 503 in produzione, ma possiamo continuare con fallback
        }
    }

    return $mysqli;
}
