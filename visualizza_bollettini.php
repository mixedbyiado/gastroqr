<?php
require_once 'config.php';

// Controlla se l'utente è loggato
requireLogin();
$user = getCurrentUser();

// Legge i bollettini dell'utente corrente dal database
$stmt = $pdo->prepare("SELECT * FROM bollettini WHERE user_id = ? ORDER BY data_creazione DESC");
$stmt->execute([$user['id']]);
$bollettini = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroQR - I Miei Bollettini</title>
    <link rel="stylesheet" href="style_viewbollets.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                    <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
            </div>
            <div>
                <a href="logout.php" class="logout-btn"?> Logout</a>
            </div>
        </div>
        
        <h1>I Miei Bollettini</h1>
        
        <div class="header-actions">
            <a href="index.php" class="btn btn-success"> Nuovo Bollettino</a>
            <a href="tutti_bollettini.php" class="btn"> Tutti i Bollettini</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="btn" style="background-color: #e74c3c;">Amministrazione</a>
            <?php endif; ?>
        </div>
       
        <?php if (empty($bollettini)): ?>
            <div class="no-bollettini">
                <h3>Nessun bollettino trovato</h3>
                <p>Non hai ancora caricato bollettini nel sistema.</p>
                <a href="index.php" class="btn btn-success">Carica il primo bollettino</a>
            </div>
        <?php else: ?>
            <div class="bollettini-grid">
                <?php foreach ($bollettini as $bollettino): ?>
                    <div class="bollettino-card">
                        <img src="<?php echo htmlspecialchars($bollettino['percorso_file']); ?>" 
                             alt="Bollettino" 
                             onclick="window.open('<?php echo htmlspecialchars($bollettino['percorso_file']); ?>', '_blank')">
                        
                        <div class="bollettino-nome">
                            <?php echo htmlspecialchars($bollettino['nome_prodotto']); ?>
                        </div>
                        
                        <div class="bollettino-scadenza">
                            Scadenza: <?php echo date('d/m/Y', strtotime($bollettino['data_scadenza'])); ?>
                        </div>
                        
                        <div class="bollettino-info">
                             Caricato: <?php echo date('d/m/Y H:i', strtotime($bollettino['data_creazione'])); ?>
                        </div>
                        
                        <div class="card-actions">
                            <a href="<?php echo htmlspecialchars($bollettino['percorso_file']); ?>" 
                               target="_blank" class="btn-view">
                                Visualizza
                            </a>
                            <a href="stampa_etichetta.php?id=<?php echo $bollettino['id']; ?>" 
                               target="_blank" class="btn-print">
                                Stampa
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="btn">← Torna alla Homepage</a>
        </div>
    </div>
</body>
</html>