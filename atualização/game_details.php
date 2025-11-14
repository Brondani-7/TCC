<?php
// game_details.php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_GET['id']);
$user = getCurrentUser($pdo);

// Buscar dados do jogo
$stmt = $pdo->prepare("
    SELECT f.*, u.CustomerName, u.CustomerHandle, u.ProfileIcon 
    FROM fangames f 
    INNER JOIN usuarios u ON f.DeveloperID = u.CustomerID 
    WHERE f.GameID = ?
");
$stmt->execute([$gameId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header('Location: fangames.php');
    exit;
}

// Incrementar downloads quando baixar
if (isset($_GET['download'])) {
    $updateStmt = $pdo->prepare("UPDATE fangames SET Downloads = Downloads + 1 WHERE GameID = ?");
    $updateStmt->execute([$gameId]);
    
    if ($game['GameFile'] && file_exists($game['GameFile'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($game['GameFile']) . '"');
        readfile($game['GameFile']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['GameTitle']); ?> - BONFIRE GAMES</title>
    <style>
        /* Adicione os estilos do perfil.php aqui ou crie um CSS separado */
    </style>
</head>
<body>
    <!-- Estrutura similar ao perfil.php para manter consistência -->
    <div class="container">
        <!-- Header e navegação similares -->
        
        <div class="game-details">
            <h1><?php echo htmlspecialchars($game['GameTitle']); ?></h1>
            <div class="game-meta">
                <span>por <?php echo htmlspecialchars($game['CustomerHandle'] ?? $game['CustomerName']); ?></span>
                <span><?php echo date('d/m/Y', strtotime($game['CreatedAt'])); ?></span>
            </div>
            
            <div class="game-info">
                <p><?php echo nl2br(htmlspecialchars($game['GameDescription'])); ?></p>
                
                <div class="game-stats">
                    <div class="stat">⭐ <?php echo number_format($game['Rating'], 1); ?></div>
                    <div class="stat">⬇️ <?php echo number_format($game['Downloads']); ?></div>
                    <div class="stat"><?php echo htmlspecialchars($game['Status']); ?></div>
                </div>
                
                <div class="game-tags">
                    <span class="tag"><?php echo htmlspecialchars($game['Genre']); ?></span>
                    <span class="tag"><?php echo htmlspecialchars($game['Franchise']); ?></span>
                </div>
                
                <a href="game_details.php?id=<?php echo $gameId; ?>&download=true" class="download-btn">
                    Baixar Fangame
                </a>
            </div>
        </div>
    </div>
</body>
</html>