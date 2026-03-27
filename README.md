# Home Server Dashboard

Pannello di controllo per server domestico.  
Tecnologie: HTML · CSS · JavaScript · PHP · MySQL (senza framework)

---

## Struttura file

```
home-dashboard/
├── index.php          ← Pagina login
├── dashboard.php      ← Dashboard servizi
├── add.php            ← Aggiunta utenti/servizi
├── logout.php         ← Gestione logout
├── auth.php           ← Logica autenticazione
├── config.php         ← Connessione database
├── schema.sql         ← Schema e dati iniziali DB
├── css/
│   ├── style.css      ← Stile globale (glassmorphism dark)
│   ├── login.css      ← Stile pagina login
│   ├── dashboard.css  ← Stile dashboard e cards
│   └── add.css        ← Stile pagina aggiungi
├── js/
│   └── main.js        ← Animazioni, copia, toast
└── video/
    └── background.mp4 ← Video di sfondo (da aggiungere)
```

---

## Installazione

### 1. Requisiti

- PHP 8.0 o superiore
- MySQL 5.7 / MariaDB 10.4 o superiore
- Server web (Apache/Nginx) con mod_rewrite o equivalente

### 2. Database

Importa lo schema nel tuo MySQL:

```bash
mysql -u root -p < schema.sql
```

Oppure esegui direttamente il contenuto di `schema.sql` dal tuo client MySQL.

### 3. Configura la connessione

Apri `config.php` e modifica:

```php
define('DB_USER', 'root');    // ← il tuo utente MySQL
define('DB_PASS', '');        // ← la tua password MySQL
```

### 4. Video di sfondo

Copia il tuo video nella cartella `video/` con il nome `background.mp4`.

Requisiti consigliati per il video:
- Formato: MP4 (H.264)
- Risoluzione: 1920×1080 o superiore
- Durata: 15–60 secondi (loop automatico)
- Dimensione: < 20 MB per performance ottimali

Se non hai un video, puoi rimuovere il tag `<video>` dai file PHP e lasciare solo l'overlay.

### 5. Permessi cartelle

```bash
chmod 644 *.php css/*.css js/*.js
chmod 755 css/ js/ video/
```

### 6. Accesso

Naviga su `http://tuo-server/home-dashboard/`

Credenziali di default (dati di esempio in schema.sql):
- Username: `admin`
- Password: `admin123`

**⚠️ Cambia subito la password dopo il primo accesso!**

---

## Aggiungere un nuovo utente

1. Accedi alla dashboard
2. Vai su **Aggiungi → Nuovo utente**
3. Inserisci username e password (min. 8 caratteri)

Oppure dalla riga di comando:

```php
// Genera hash da usare direttamente in MySQL
echo password_hash('tua_password', PASSWORD_BCRYPT, ['cost' => 12]);
```

```sql
INSERT INTO users (username, password) VALUES ('nuovoutente', '<hash_generato>');
```

---

## Sicurezza

Il progetto implementa:

| Misura | Implementazione |
|--------|-----------------|
| Hash password | `password_hash()` con bcrypt cost 12 |
| SQL Injection | Prepared statements PDO ovunque |
| XSS | `htmlspecialchars()` su ogni output HTML |
| Session fixation | `session_regenerate_id(true)` al login |
| Cookie sicuri | `httponly`, `samesite=Strict` |
| Protezione pagine | `requireLogin()` su ogni pagina protetta |

---

## Personalizzazione

### Cambiare i colori accent delle card

Nel file `css/style.css`, modifica le variabili `--accent-*`:

```css
:root {
  --accent-blue:   #3b82f6;
  --accent-teal:   #14b8a6;
  --accent-purple: #8b5cf6;
  /* ... */
}
```

### Cambiare l'overlay del video

In `css/style.css`, sezione `.video-overlay`:

```css
.video-overlay {
  background: linear-gradient(
    135deg,
    rgba(4, 8, 20, 0.82) 0%,   ← più alto = overlay più scuro
    rgba(6, 12, 28, 0.72) 50%,
    rgba(4, 8, 20, 0.88) 100%
  );
}
```
