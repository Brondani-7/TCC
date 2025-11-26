<?php
// delete_fangame.php - Processar exclusão de fangame
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Você precisa estar logado para excluir um fangame.";
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);

// Verificar se o ID do jogo foi enviado via POST
if (!isset($_POST['game_id']) || !is_numeric($_POST['game_id'])) {
    $_SESSION['error'] = "ID do jogo inválido.";
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_POST['game_id']);

// Verificar se o usuário é o desenvolvedor do jogo
if (!isGameDeveloper($pdo, $gameId, $user['CustomerID'])) {
    $_SESSION['error'] = "Você não tem permissão para excluir este fangame.";
    header('Location: game.php?id=' . $gameId);
    exit;
}

// Buscar informações do jogo antes de excluir (para limpeza de arquivos)
$game = getFangame($pdo, $gameId);
if (!$game) {
    $_SESSION['error'] = "Fangame não encontrado.";
    header('Location: fangames.php');
    exit;
}

// Iniciar transação para garantir consistência
$pdo->beginTransaction();

try {
    // 1. Deletar screenshots do jogo
    $stmt = $pdo->prepare("
        SELECT ScreenshotID, ScreenshotPath 
        FROM game_screenshots 
        WHERE GameID = ?
    ");
    $stmt->execute([$gameId]);
    $screenshots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($screenshots as $screenshot) {
        // Remover arquivo físico
        if (!empty($screenshot['ScreenshotPath']) && file_exists($screenshot['ScreenshotPath'])) {
            unlink($screenshot['ScreenshotPath']);
        }
    }
    
    // Deletar registros de screenshots do banco
    $stmt = $pdo->prepare("DELETE FROM game_screenshots WHERE GameID = ?");
    $stmt->execute([$gameId]);
    
    // 2. Deletar arquivo de capa do jogo
    if (!empty($game['GameCover']) && file_exists($game['GameCover'])) {
        unlink($game['GameCover']);
    }
    
    // 3. Deletar arquivo do jogo (se existir)
    if (!empty($game['GameFile']) && file_exists($game['GameFile'])) {
        unlink($game['GameFile']);
    }
    
    // 4. Deletar o jogo do banco de dados
    $stmt = $pdo->prepare("DELETE FROM fangames WHERE GameID = ?");
    $stmt->execute([$gameId]);
    
    // Confirmar transação
    $pdo->commit();
    
    $_SESSION['success'] = "Fangame excluído com sucesso!";
    header('Location: fangames.php');
    exit;
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    $pdo->rollBack();
    
    error_log("Erro ao excluir fangame: " . $e->getMessage());
    $_SESSION['error'] = "Erro ao excluir fangame: " . $e->getMessage();
    header('Location: game.php?id=' . $gameId);
    exit;
}
?>