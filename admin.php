<?php
require_once 'config.php';

// Controlla se l'utente è loggato e admin
requireLogin();
requireAdmin();
$user = getCurrentUser();

$success = '';
$error = '';

// Gestione creazione nuovo utente
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nome = trim($_POST['nome']);
    $ruolo = $_POST['ruolo'];
    
    // Validazione
    if (empty($username) || empty($password) || empty($nome)) {
        $error = 'Tutti i campi sono obbligatori';
    } elseif (strlen($username) < 3) {
        $error = 'Username deve essere almeno 3 caratteri';
    } elseif (strlen($password) < 6) {
        $error = 'Password deve essere almeno 6 caratteri';
    } else {
        // Controlla se username esiste già
        $stmt = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = 'Username già esistente';
        } else {
            // Crea nuovo utente
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utenti (username, password, nome, ruolo) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $password_hash, $nome, $ruolo])) {
                $success = 'Utente creato con successo!';
            } else {
                $error = 'Errore nella creazione dell\'utente';
            }
        }
    }
}

// Gestione toggle attivo/disattivo
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'toggle_user') {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE utenti SET attivo = NOT attivo WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $success = 'Stato utente aggiornato';
    }
}

// Carica tutti gli utenti
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(b.id) as bollettini_count,
           MAX(b.data_creazione) as ultimo_bollettino
    FROM utenti u 
    LEFT JOIN bollettini b ON u.id = b.user_id 
    GROUP BY u.id 
    ORDER BY u.data_creazione DESC
");
$stmt->execute();
$utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiche generali
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as totale_utenti,
        SUM(CASE WHEN attivo = 1 THEN 1 ELSE 0 END) as utenti_attivi,
        SUM(CASE WHEN ruolo = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN ruolo = 'cuoco' THEN 1 ELSE 0 END) as cuochi_count
    FROM utenti
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroQR - Amministrazione Utenti</title>
    <link rel="stylesheet" href="style_admin.css">   
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="admin-badge">
                🛡️ ADMIN - <?php echo htmlspecialchars($user['nome']); ?>
            </div>
            <div>
                <a href="logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        
        <h1>⚙️ Amministrazione Utenti GastroQR</h1>
        
        <div class="nav-section">
            <a href="index.php" class="nav-btn"> Homepage</a>
            <a href="tutti_bollettini.php" class="nav-btn"> Tutti i Bollettini</a>
        </div>
        
        <!-- Statistiche -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['totale_utenti']; ?></div>
                <div class="stat-label">Utenti Totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['utenti_attivi']; ?></div>
                <div class="stat-label">Utenti Attivi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['admin_count']; ?></div>
                <div class="stat-label">Amministratori</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['cuochi_count']; ?></div>
                <div class="stat-label">Cuochi</div>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form creazione utente -->
        <div class="form-section">
            <h3> Crea Nuovo Utente</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="es: cuoco3" minlength="3">
                    </div>
                    
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" required 
                               placeholder="es: Giovanni Bianchi">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Minimo 6 caratteri" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="ruolo">Ruolo:</label>
                        <select id="ruolo" name="ruolo" required>
                            <option value="cuoco">Cuoco</option>
                            <option value="admin"> Amministratore</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                     Crea Utente
                </button>
            </form>
        </div>
        
        <!-- Lista utenti -->
        <div class="form-section">
            <h3>👥 Gestione Utenti</h3>
            
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nome</th>
                        <th>Ruolo</th>
                        <th>Stato</th>
                        <th>Bollettini</th>
                        <th>Ultimo Accesso</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utenti as $utente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($utente['username']); ?></td>
                            <td><?php echo htmlspecialchars($utente['nome']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $utente['ruolo']; ?>">
                                    <?php echo $utente['ruolo'] == 'admin' ? ' Admin' : 'Cuoco'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $utente['attivo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $utente['attivo'] ? 'Attivo' : ' Disattivo'; ?>
                                </span>
                            </td>
                            <td><?php echo $utente['bollettini_count']; ?></td>
                            <td>
                                <?php 
                                if ($utente['ultimo_bollettino']) {
                                    echo date('d/m/Y', strtotime($utente['ultimo_bollettino']));
                                } else {
                                    echo 'Mai';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($utente['id'] != $user['id']): // Non può disattivare se stesso ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_user">
                                        <input type="hidden" name="user_id" value="<?php echo $utente['id']; ?>">
                                        <button type="submit" class="btn btn-small <?php echo $utente['attivo'] ? 'btn-warning' : 'btn'; ?>"
                                                onclick="return confirm('Sei sicuro di voler <?php echo $utente['attivo'] ? 'disattivare' : 'attivare'; ?> questo utente?')">
                                            <?php echo $utente['attivo'] ? ' Disattiva' : 'Attiva'; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-admin">Tu</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="nav-btn">← Torna alla Homepage</a>
        </div>
    </div>
</body>
</html>