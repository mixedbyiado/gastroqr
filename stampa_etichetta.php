<?php
require_once 'config.php';

// Controlla se l'utente è loggato
requireLogin();

$prodotto = null;

// Se c'è un ID specifico, carica dal database
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM bollettini WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $bollettino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bollettino) {
        $prodotto = [
            'nome' => $bollettino['nome_prodotto'],
            'data_scadenza' => $bollettino['data_scadenza'],
            'qr_data' => $bollettino['qr_data']
        ];
        
        // Salva in sessione per il QR
        $_SESSION['stampa_prodotto'] = $prodotto;
    }
} elseif (isset($_SESSION['ultimo_prodotto'])) {
    // Usa l'ultimo prodotto dalla sessione
    $prodotto = $_SESSION['ultimo_prodotto'];
    $_SESSION['stampa_prodotto'] = $prodotto;
}

if (!$prodotto) {
    die('Nessun prodotto da stampare');
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stampa Etichetta - <?php echo htmlspecialchars($prodotto['nome']); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.png">
    <link rel="stylesheet" href="style_petichetta.css">
</head>
<body>
    <div class="no-print anteprima">
        <h2> Anteprima Etichetta</h2>
        <p>Questa è l'anteprima dell'etichetta che verrà stampata.</p>
        
        <button onclick="window.open('stampa_ptouch_diretta.php<?php echo isset($_GET['id']) ? '?id=' . $_GET['id'] : ''; ?>', '_blank')" class="stampa-btn btn-secondary">
             Stampa!
        </button>
        
        <button onclick="window.close()" class="stampa-btn indietro-btn">
            Chiudi
        </button>
    </div>
    
    <!-- Etichetta da stampare -->
    <div class="etichetta">
        <!-- QR Code -->
        <div class="qr-section">
            <img src="genera_qr.php?stampa=1" alt="QR Code" class="qr-code">
        </div>
        
        <!-- Testo prodotto -->
        <div class="text-section">
            <div class="nome-prodotto">
                <?php echo htmlspecialchars($prodotto['nome']); ?>
            </div>
            
            <div class="data-scadenza">
                <?php 
                $data = DateTime::createFromFormat('Y-m-d', $prodotto['data_scadenza']);
                echo $data->format('d/m/Y'); 
                ?>
            </div>
        </div>
    </div>
    
    
    <script>
        // Auto-stampa quando richiesto
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            };
        }
    </script>
</body>
</html>