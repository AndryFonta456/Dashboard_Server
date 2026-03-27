<?php
// setup.php — import schema in XAMPP (esegui una sola volta)

require_once __DIR__ . '/config.php';

try {
    // Connessione al server MySQL senza database (per creare DB se non esiste)
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Importa schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!is_readable($schemaFile)) {
        throw new RuntimeException('Impossibile leggere schema.sql. Controlla che il file esista.');
    }

    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);

    // Aggiungi colonna icon per retrocompatibilità (serve per mostrare le immagini dei servizi)
    $pdo->exec('ALTER TABLE services ADD COLUMN IF NOT EXISTS icon VARCHAR(512) DEFAULT NULL');

    echo '<h1>Setup completato</h1>';
    echo '<p>Database e tabelle creati con successo.</p>';
    echo '<p><a href="index.php">Vai al login</a></p>';
} catch (Exception $e) {
    echo '<h1>Errore setup</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
}
