<?php
session_start();

// Verifica se ci sono dati per il QR
$qr_data = "Nessun prodotto";

// Se stiamo stampando, usa i dati di stampa
if (isset($_GET['stampa']) && isset($_SESSION['stampa_prodotto'])) {
    $qr_data = $_SESSION['stampa_prodotto']['qr_data'];
} elseif (isset($_SESSION['ultimo_prodotto'])) {
    // Altrimenti usa l'ultimo prodotto
    $qr_data = $_SESSION['ultimo_prodotto']['qr_data'];
}

// Genera QR usando cURL (più compatibile con hosting condivisi)
$qr_size = '200x200';
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $qr_size . '&data=' . urlencode($qr_data);

// Prova prima con cURL se disponibile
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qr_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; GastroQR/1.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $qr_image = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($qr_image !== false && $http_code == 200) {
        // Successo con cURL
        header('Content-Type: image/png');
        echo $qr_image;
        exit;
    }
}

// Fallback a file_get_contents se cURL fallisce
if (ini_get('allow_url_fopen')) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (compatible; GastroQR/1.0)'
        ]
    ]);
    
    $qr_image = @file_get_contents($qr_url, false, $context);
    
    if ($qr_image !== false) {
        header('Content-Type: image/png');
        echo $qr_image;
        exit;
    }
}

// Se tutto fallisce, genera un QR locale semplice (fallback)
header('Content-Type: image/svg+xml');
$qr_text = htmlspecialchars($qr_data);
echo '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="200" height="200" fill="white"/>
    <rect x="20" y="20" width="160" height="160" fill="none" stroke="black" stroke-width="2"/>
    <text x="100" y="100" text-anchor="middle" dominant-baseline="middle" font-size="12" font-family="Arial">
        QR Code
    </text>
    <text x="100" y="120" text-anchor="middle" dominant-baseline="middle" font-size="8" font-family="Arial">
        ' . (strlen($qr_text) > 30 ? substr($qr_text, 0, 30) . '...' : $qr_text) . '
    </text>
    <text x="100" y="180" text-anchor="middle" dominant-baseline="middle" font-size="8" font-family="Arial" fill="red">
        (Errore API - Placeholder)
    </text>
</svg>';
?>