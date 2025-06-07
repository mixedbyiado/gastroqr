<?php
require_once 'config.php';

// Controlla se l'utente è loggato
requireLogin();
$user = getCurrentUser();

// Gestione upload foto
if ($_POST && isset($_FILES['bollettino'])) {
    $nome_prodotto = $_POST['nome_prodotto'];
    $data_scadenza = $_POST['data_scadenza'];
    
    // Verifica cartella uploads
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    // Upload file
    $file_extension = pathinfo($_FILES['bollettino']['name'], PATHINFO_EXTENSION);
    $nome_file = time() . '_' . uniqid() . '.' . $file_extension;
    $percorso_completo = 'uploads/' . $nome_file;
    
    if (move_uploaded_file($_FILES['bollettino']['tmp_name'], $percorso_completo)) {
        // Salva prima nel database per ottenere l'ID
        $stmt = $pdo->prepare("INSERT INTO bollettini (user_id, nome_prodotto, data_scadenza, nome_file, percorso_file, qr_data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $nome_prodotto, $data_scadenza, $nome_file, $percorso_completo, '']);
        
        $bollettino_id = $pdo->lastInsertId();
        
        // Costruisci URL per visualizzare il bollettino tramite QR
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script_dir = dirname($_SERVER['REQUEST_URI']);
        $qr_url = $protocol . $host . $script_dir . '/visualizza_qr.php?id=' . $bollettino_id;
        
        // Aggiorna il bollettino con l'URL del QR
        $stmt = $pdo->prepare("UPDATE bollettini SET qr_data = ? WHERE id = ?");
        $stmt->execute([$qr_url, $bollettino_id]);
        
        // Salva dati in sessione per la stampa
        $_SESSION['ultimo_prodotto'] = [
            'id' => $bollettino_id,
            'nome' => $nome_prodotto,
            'data_scadenza' => $data_scadenza,
            'foto' => $percorso_completo,
            'qr_data' => $qr_url
        ];
        
        $success = true;
    } else {
        $error = "Errore nell'upload del file.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
 	<link rel="icon" type="image/png" sizes="32x32" href="/favicon//favicon-32x32.png">
  	<link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
  	<link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="stylesheet" href="style_index.css">
    <title> GastroQR - Gestione Bollettini</title>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                Benvenuto, <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
            </div>
            <div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <h1>GastroQR</h1>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">
            Sistema di gestione bollettini per ristoranti versione v3.5
        </p>
        
        <?php if (isset($success)): ?>
            <div class="success">
                Bollettino caricato con successo! QR Code generato.
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error">
                 <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome_prodotto">Nome Prodotto:</label>
                <input type="text" id="nome_prodotto" name="nome_prodotto" required 
                       placeholder="Es: Pomodori San Marzano">
            </div>
            
            <div class="form-group">
                <label for="data_scadenza">Data di Scadenza:</label>
                <input type="date" id="data_scadenza" name="data_scadenza" required>
            </div>
            
            <div class="form-group">
                <label for="bollettino">Foto Bollettino:</label>
                <input type="file" id="bollettino" name="bollettino" 
                       accept="image/*" capture="camera" required>
                <small style="color: #7f8c8d;">
                    Formati supportati: JPG, PNG, GIF
                </small>
            </div>
            
            <button type="submit"> Carica Bollettino</button>
        </form>
        
        <?php if (isset($_SESSION['ultimo_prodotto'])): ?>
            <div class="qr-section">
                <h3>QR Code Generato</h3>
                <img src="genera_qr.php" alt="QR Code" style="max-width: 200px;">
                <br>
                <strong><?php echo htmlspecialchars($_SESSION['ultimo_prodotto']['nome']); ?></strong>
                <br>
                <span style="color: #e74c3c;">
                    Scadenza: <?php echo htmlspecialchars($_SESSION['ultimo_prodotto']['data_scadenza']); ?>
                </span>
                <br>
                <small style="color: #7f8c8d; margin-top: 10px; display: block;">
                    Il QR code porta alla pagina del bollettino:<br>
                    <a href="<?php echo htmlspecialchars($_SESSION['ultimo_prodotto']['qr_data']); ?>" 
                       target="_blank" 
                       style="background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 11px; text-decoration: none; color: #3498db;">
                        <?php echo htmlspecialchars($_SESSION['ultimo_prodotto']['qr_data']); ?>
                    </a>
                </small>
                
                <br>
                <button onclick="window.open('stampa_etichetta.php', '_blank')" class="print-btn">
                    Stampa Etichetta
                </button>
            </div>
        <?php endif; ?>
        
        <div class="nav-links">
            <a href="visualizza_bollettini.php"> I Miei Bollettini</a>
            <a href="tutti_bollettini.php">Tutti i Bollettini</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php" style="background-color: red;">Amministrazione</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>