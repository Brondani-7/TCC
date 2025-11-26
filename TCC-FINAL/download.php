<?php
// download.php - Processar downloads de fangames
require_once 'config.php';

// Verificar se o ID do jogo foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_GET['id']);

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php?redirect=game.php?id=' . $gameId);
    exit;
}

$user = getCurrentUser($pdo);

// Buscar informações do jogo
$game = getFangame($pdo, $gameId);

if (!$game) {
    header('Location: fangames.php');
    exit;
}

// Incrementar contador de downloads
incrementDownloads($pdo, $gameId);

// Redirecionar para o link apropriado
if (!empty($game['DownloadLink'])) {
    // Redireciona diretamente para o link externo (MediaFire ou outro)
    header('Location: ' . $game['DownloadLink']);
    exit;
} 
// Se tem arquivo local para download
elseif (!empty($game['GameFile'])) {
    $filePath = $game['GameFile'];
    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

// Se não encontrou nenhum método de download, redireciona de volta com erro
header('Location: game.php?id=' . $gameId . '&error=download');
exit;
?>