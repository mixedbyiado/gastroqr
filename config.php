<?php
// Configurazione database
$host = 'insert_your_host';
$dbname = 'gastroqr';
$username = 'insert_your_dbusername';
$password = 'insert_your_dbpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Errore connessione database: " . $e->getMessage());
}

// Funzione per verificare se l'utente è loggato
function isLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']);
}

// Funzione per reindirizzare al login se non loggato
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Funzione per ottenere info utente
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT id, username, nome, ruolo FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Funzione per verificare se l'utente è admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['ruolo'] === 'admin';
}

// Funzione per richiedere privilegi admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?>