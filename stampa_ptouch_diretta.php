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
    <title>Stampa P-touch - <?php echo htmlspecialchars($prodotto['nome']); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.png">
    <link rel="stylesheet" href="style_pdiretta.css">

</head>
<body>
    <!-- Anteprima visibile a schermo -->
    <div class="preview-container">
        <h2> Anteprima Etichetta 24mm</h2>
        
        <div style="height: 200px; display: flex; align-items: center; justify-content: center; margin: 40px 0; overflow: hidden;">
            <div class="preview-label">
                <div class="ptouch-label">
                    <div class="qr-container">
                        <img src="genera_qr.php?stampa=1" alt="QR Code" class="qr-code">
                    </div>
                    
                    <div class="content-area">
                        <div class="product-name">
                            <?php echo htmlspecialchars($prodotto['nome']); ?>
                        </div>
                        
                        <div class="expiry-date">
                            <?php 
                            $data = DateTime::createFromFormat('Y-m-d', $prodotto['data_scadenza']);
                            echo $data->format('d/m/Y'); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin: 30px 0;">
            <button onclick="printLabel()" class="btn">
                Stampa su P-touch
            </button>
            
            
            <button onclick="window.close()" class="btn" style="background-color: #95a5a6;">
                    Chiudi
            </button>
        </div>
    </div>
    
    <!-- Etichetta effettiva per la stampa (nascosta a schermo) -->
    <div class="ptouch-label" style="display: none;" id="printable-label">
        <div class="qr-container">
            <img src="genera_qr.php?stampa=1" alt="QR Code" class="qr-code">
        </div>
        
        <div class="content-area">
            <div class="product-name">
                <?php echo htmlspecialchars($prodotto['nome']); ?>
            </div>
            
            <div class="expiry-date">
                <?php 
                $data = DateTime::createFromFormat('Y-m-d', $prodotto['data_scadenza']);
                echo $data->format('d/m/Y'); 
                ?>
            </div>
        </div>
    </div>
    
    <div class="instructions">
        <h3>📋 Istruzioni Dettagliate P-touch</h3>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <strong>⚠️ IMPORTANTE - Impostazioni per Layout Orizzontale:</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Formato carta:</strong> Personalizzato → <strong>80mm x 24mm</strong></li>
                <li><strong>Orientamento:</strong> <strong>Orizzontale/Landscape</strong></li>
                <li><strong>Nastro P-touch:</strong> TZe-251 (24mm nero/bianco)</li>
                <li><strong>Margini:</strong> 0mm tutti i lati</li>
            </ol>
        </div>
        
        <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <strong>🖨️ Procedura di Stampa:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Seleziona stampante <strong>P-touch</strong></li>
                <li>Proprietà → Formato: <strong>Personalizzato</strong></li>
                <li>Larghezza: <strong>80mm</strong>, Altezza: <strong>24mm</strong></li>
                <li>Orientamento: <strong>Orizzontale</strong></li>
                <li>Scala: <strong>100% (NO adatta alla pagina)</strong></li>
            </ul>
        </div>
        
        <div style="background: #d4edda; padding: 15px; border-radius: 5px;">
            <strong>💡 Layout Orizzontale - Vantaggi:</strong><br>
            ✅ <strong>Più compatibile</strong> con stampanti P-touch<br>
            ✅ <strong>QR più grande</strong> e leggibile (18mm)<br>
            ✅ <strong>Testo ben distribuito</strong> su 2 righe<br>
            ✅ <strong>Lunghezza ottimizzata</strong> (80mm)<br><br>
            
            <strong>🏷️ Dimensioni Finali:</strong><br>
            Larghezza: 80mm | Altezza: 24mm | QR: 18x18mm
        </div>
    </div>
    
    <script>
        function printLabel() {
            // Mostra solo l'etichetta per la stampa
            document.getElementById('printable-label').style.display = 'block';
            
            // Stampa
            window.print();
            
            // Nasconde di nuovo l'etichetta dopo la stampa
            setTimeout(function() {
                document.getElementById('printable-label').style.display = 'none';
            }, 1000);
        }
        
        // Auto-print se richiesto
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(printLabel, 500);
            };
        }
    </script>
</body>
</html>