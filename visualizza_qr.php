<?php
require_once 'config.php';

// Pagina per visualizzare il bollettino tramite QR code
// Accessibile a tutti (anche senza login) per permettere la scansione

$bollettino = null;
$error = '';

// Verifica se c'è un ID bollettino nell'URL
if (isset($_GET['id'])) {
    $bollettino_id = $_GET['id'];
    
    // Carica bollettino dal database
    $stmt = $pdo->prepare("
        SELECT b.*, u.nome as nome_utente 
        FROM bollettini b 
        JOIN utenti u ON b.user_id = u.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$bollettino_id]);
    $bollettino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bollettino) {
        $error = 'Bollettino non trovato';
    }
} else {
    $error = 'ID bollettino mancante';
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroQR - Visualizza Bollettino</title>

</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🍽️</div>
            <h1>GastroQR</h1>
            <p style="color: #7f8c8d;">Bollettino Prodotto</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                 <?php echo htmlspecialchars($error); ?>
                <br><br>
                <a href="login.php" class="btn"> Accedi al Sistema</a>
            </div>
        <?php else: ?>
            <div class="bollettino-card">
                <div class="bollettino-image">
                    <img src="<?php echo htmlspecialchars($bollettino['percorso_file']); ?>" 
                         alt="Bollettino <?php echo htmlspecialchars($bollettino['nome_prodotto']); ?>">
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"> Prodotto</div>
                        <div class="info-value"><?php echo htmlspecialchars($bollettino['nome_prodotto']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"> Data Scadenza</div>
                        <div class="info-value">
                            <?php echo date('d/m/Y', strtotime($bollettino['data_scadenza'])); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"> Caricato da</div>
                        <div class="info-value"><?php echo htmlspecialchars($bollettino['nome_utente']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"> Data Caricamento</div>
                        <div class="info-value">
                            <?php echo date('d/m/Y H:i', strtotime($bollettino['data_creazione'])); ?>
                        </div>
                    </div>
                </div>
                
                <?php
                // Verifica scadenza
                $data_scadenza = new DateTime($bollettino['data_scadenza']);
                $oggi = new DateTime();
                $differenza = $oggi->diff($data_scadenza);
                $giorni_alla_scadenza = $differenza->days;
                
                if ($data_scadenza < $oggi): ?>
                    <div class="scadenza-warning scadenza-expired">
                        PRODOTTO SCADUTO il <?php echo date('d/m/Y', strtotime($bollettino['data_scadenza'])); ?>
                    </div>
                <?php elseif ($giorni_alla_scadenza <= 2): ?>
                    <div class="scadenza-warning">
                        ATTENZIONE: Scade tra <?php echo $giorni_alla_scadenza; ?> giorni!
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="qr-info">
                <strong>Hai scansionato questo QR code!</strong><br>
                Questo bollettino è stato caricato nel sistema GastroQR per la tracciabilità dei prodotti.
            </div>
            
            <div class="actions">
                <a href="<?php echo htmlspecialchars($bollettino['percorso_file']); ?>" 
                   target="_blank" class="btn">
                     Visualizza Immagine Originale
                </a>
                
                <a href="login.php" class="btn">
                     Accedi al Sistema
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>