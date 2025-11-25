<?php
// download.php - Processar downloads de fangames
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'] ?? 'fangames.php';
    $_SESSION['error'] = 'Você precisa estar logado para baixar fangames.';
    header('Location: login.php');
    exit;
}

// Verificar se o ID do jogo foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID do jogo inválido.';
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_GET['id']);
$userId = $_SESSION['customer_id'];

// Buscar informações do jogo
$game = getFangame($pdo, $gameId);

if (!$game) {
    $_SESSION['error'] = 'Fangame não encontrado.';
    header('Location: fangames.php');
    exit;
}

// Verificar se o jogo tem arquivo para download
if (empty($game['GameFile']) && empty($game['DownloadLink'])) {
    $_SESSION['error'] = 'Este fangame não possui arquivo para download.';
    header('Location: game.php?id=' . $gameId);
    exit;
}

// Processar o download
try {
    // Se for um link externo, redirecionar
    if (!empty($game['DownloadLink']) && empty($game['GameFile'])) {
        // Incrementar contador de downloads
        incrementDownloads($pdo, $gameId);
        
        // Registrar download no log
        logDownload($pdo, $gameId, $userId);
        
        // Redirecionar para o link externo
        header('Location: ' . $game['DownloadLink']);
        exit;
    }
    
    // Se for um arquivo local, fazer download
    if (!empty($game['GameFile']) && file_exists($game['GameFile'])) {
        $filePath = $game['GameFile'];
        $fileName = basename($filePath);
        
        // Verificar se o arquivo é válido
        if (!validateGameFile($filePath)) {
            throw new Exception('Arquivo de download inválido ou corrompido.');
        }
        
        // Incrementar contador de downloads
        incrementDownloads($pdo, $gameId);
        
        // Registrar download no log
        logDownload($pdo, $gameId, $userId);
        
        // Configurar headers para download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Limpar buffers
        ob_clean();
        flush();
        
        // Ler e enviar o arquivo
        readfile($filePath);
        
        exit;
    } else {
        throw new Exception('Arquivo não encontrado no servidor.');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Erro ao processar download: ' . $e->getMessage();
    header('Location: game.php?id=' . $gameId);
    exit;
}