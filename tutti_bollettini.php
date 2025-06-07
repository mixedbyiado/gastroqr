<?php
require_once 'config.php';

// Controlla se l'utente è loggato
requireLogin();
$user = getCurrentUser();

// Legge tutti i bollettini dal database con info utente
$stmt = $pdo->prepare("
    SELECT b.*, u.nome as nome_utente, u.username 
    FROM bollettini b 
    JOIN utenti u ON b.user_id = u.id 
    ORDER BY b.data_creazione DESC
");
$stmt->execute();
$bollettini = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiche
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as totale,
        COUNT(DISTINCT user_id) as utenti_attivi,
        DATE(MIN(data_creazione)) as primo_bollettino
    FROM bollettini
");
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroQR - Tutti i Bollettini</title>
    <link rel="stylesheet" href="style_allbollets.css">

</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                 <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
            </div>
            <div>
                <a href="logout.php" class="logout-btn"> Logout</a>
            </div>
        </div>
        
        <h1>Tutti i Bollettini del Ristorante</h1>
        
        <div class="header-actions">
            <a href="index.php" class="btn btn-success"> Nuovo Bollettino</a>
            <a href="visualizza_bollettini.php" class="btn">I Miei Bollettini</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="btn" style="background-color: #e74c3c;">Amministrazione</a>
            <?php endif; ?>
        </div>
        
        <!-- Statistiche generali -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['totale']; ?></div>
                <div class="stat-label">Bollettini Totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['utenti_attivi']; ?></div>
                <div class="stat-label">Cuochi Attivi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    if ($stats['primo_bollettino']) {
                        echo date('d/m/Y', strtotime($stats['primo_bollettino'])); 
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
                <div class="stat-label">Primo Bollettino</div>
            </div>
        </div>
        
        <?php if (empty($bollettini)): ?>
            <div class="no-bollettini">
                <h3>📭 Nessun bollettino nel sistema</h3>
                <p>Il ristorante non ha ancora bollettini caricati.</p>
                <a href="index.php" class="btn btn-success">Carica il primo bollettino</a>
            </div>
        <?php else: ?>
            <div class="bollettini-grid">
                <?php foreach ($bollettini as $bollettino): ?>
                    <?php
                    // Calcola se il prodotto è scaduto o in scadenza
                    $data_scadenza = new DateTime($bollettino['data_scadenza']);
                    $oggi = new DateTime();
                    $differenza = $oggi->diff($data_scadenza);
                    $giorni_alla_scadenza = $differenza->days;
                    
                    if ($data_scadenza < $oggi) {
                        $stato_scadenza = 'expired';
                        $testo_scadenza = 'SCADUTO';
                    } elseif ($giorni_alla_scadenza <= 2) {
                        $stato_scadenza = 'warning';
                        $testo_scadenza = 'SCADE PRESTO';
                    } else {
                        $stato_scadenza = '';
                        $testo_scadenza = '';
                    }
                    ?>
                    
                    <div class="bollettino-card">
                        <?php if ($testo_scadenza): ?>
                            <div class="scadenza-warning <?php echo $stato_scadenza == 'expired' ? 'scadenza-expired' : ''; ?>">
                                <?php echo $testo_scadenza; ?>
                            </div>
                        <?php endif; ?>
                        
                        <img src="<?php echo htmlspecialchars($bollettino['percorso_file']); ?>" 
                             alt="Bollettino" 
                             onclick="window.open('<?php echo htmlspecialchars($bollettino['percorso_file']); ?>', '_blank')">
                        
                        <div class="bollettino-utente">
                            <?php echo htmlspecialchars($bollettino['nome_utente']); ?>
                        </div>
                        
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