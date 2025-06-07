-- Database per GastroQR
-- Esegui questo script per creare il database e le tabelle

CREATE DATABASE IF NOT EXISTS gastroqr;
USE gastroqr;

-- Tabella utenti
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    ruolo ENUM('cuoco', 'admin') DEFAULT 'cuoco',
    attivo TINYINT(1) DEFAULT 1,
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabella bollettini
CREATE TABLE IF NOT EXISTS bollettini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome_prodotto VARCHAR(200) NOT NULL,
    data_scadenza DATE NOT NULL,
    nome_file VARCHAR(255) NOT NULL,
    percorso_file VARCHAR(500) NOT NULL,
    qr_data TEXT,
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Inserimento utenti di esempio
INSERT INTO utenti (username, password, nome, ruolo) VALUES 
('iadonj', '$2y$10$hDkxsUclV/.wln9dcbAKbO/tkBFDqJQ2hEcYDBcuRYoFJhjIcY.1.', 'Joshua Iadonisi (AMMINISTRATORE)', 'admin');

-- Password per tutti gli utenti: "password"
