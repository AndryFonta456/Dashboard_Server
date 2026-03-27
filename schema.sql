-- ============================================================
-- Home Server Dashboard — Schema Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS home_dashboard
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE home_dashboard;

-- Tabella utenti
CREATE TABLE IF NOT EXISTS users (
  id       INT          NOT NULL AUTO_INCREMENT,
  username VARCHAR(64)  NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella servizi
CREATE TABLE IF NOT EXISTS services (
  id           INT          NOT NULL AUTO_INCREMENT,
  service_name VARCHAR(128) NOT NULL,
  url          VARCHAR(512) NOT NULL,
  icon         VARCHAR(512) DEFAULT NULL,
  username     VARCHAR(128) DEFAULT NULL,
  password     VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Utente di esempio (password: admin123)
-- Generata con: password_hash('admin123', PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO users (username, password) VALUES
  ('admin', '$2y$12$Kk8sHFt3zXQnbQn3Gw2ZnOt1vQpL8mNwY5rJkXdAeP7cSvBuIqG3i');

-- ============================================================
-- Servizi di esempio
-- ============================================================
INSERT INTO services (service_name, url, username, password) VALUES
  ('Portainer',    'http://192.168.1.10:9000',  'admin',    'portainer123'),
  ('Jellyfin',     'http://192.168.1.10:8096',  'mediauser','jelly456'),
  ('Nextcloud',    'http://192.168.1.10:8080',  'admin',    'cloud789'),
  ('Grafana',      'http://192.168.1.10:3000',  'admin',    'grafana321'),
  ('Pi-hole',      'http://192.168.1.10:8053',  NULL,       'pihole654'),
  ('Homebridge',   'http://192.168.1.10:8581',  'admin',    'bridge987');
