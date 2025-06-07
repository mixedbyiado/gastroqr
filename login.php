<?php
require_once 'config.php';

// Se già loggato, reindirizza
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

// Gestione login
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password, nome, attivo FROM utenti WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Controlla se l'utente è attivo
            if (!$user['attivo']) {
                $error = 'Account disattivato. Contatta l\'amministratore.';
            } else {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nome'] = $user['nome'];
                
                header('Location: index.php');
                exit();
            }
        } else {
            $error = 'Username o password non corretti';
        }
    } else {
        $error = 'Inserisci username e password';
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroQR - Login</title>
    <link rel="stylesheet" href="style_login.css">

</head>
<body>
    <div class="login-container">
        <div class="logo">
            
            <h1>GastroQR</h1>
            <p>Sistema di gestione bollettini</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Inserisci il tuo username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Inserisci la tua password">
            </div>
            
            <button type="submit" class="login-btn">
                Accedi al Sistema
            </button>
        </form>
        </div>
    </div>
</body>
</html>