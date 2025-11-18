<?php
// game.php - Página de detalhes do fangame
require_once 'config.php';

// Verificar se o ID do jogo foi fornecido
$gameId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$gameId) {
    header('Location: fangames.php');
    exit;
}

// Buscar dados do jogo
$game = null;
$developer = null;
$relatedGames = [];

try {
    // Buscar informações do jogo
    $stmt = $pdo->prepare("
        SELECT f.*, u.CustomerName, u.CustomerHandle, u.ProfilePhoto, u.CustomerBio
        FROM fangames f 
        JOIN usuarios u ON f.DeveloperID = u.CustomerID 
        WHERE f.GameID = ?
    ");
    $stmt->execute([$gameId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$game) {
        header('Location: fangames.php');
        exit;
    }
    
    // Buscar jogos relacionados (mesma franquia ou gênero)
    $stmt = $pdo->prepare("
        SELECT f.GameID, f.GameTitle, f.GameCover, f.Franchise, f.Genre, f.Status, 
               u.CustomerName, u.CustomerHandle
        FROM fangames f 
        JOIN usuarios u ON f.DeveloperID = u.CustomerID 
        WHERE f.GameID != ? AND (f.Franchise = ? OR f.Genre = ?)
        ORDER BY f.CreatedAt DESC 
        LIMIT 4
    ");
    $stmt->execute([$gameId, $game['Franchise'], $game['Genre']]);
    $relatedGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Incrementar contador de visualizações
    $stmt = $pdo->prepare("UPDATE fangames SET Downloads = Downloads + 1 WHERE GameID = ?");
    $stmt->execute([$gameId]);
    
} catch (PDOException $e) {
    error_log("Erro ao carregar jogo: " . $e->getMessage());
    $error = "Erro ao carregar informações do jogo.";
}

// Verificar se usuário está logado
$user = isLoggedIn() ? getCurrentUser($pdo) : null;
$isDeveloper = $user && $user['CustomerID'] == $game['DeveloperID'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['GameTitle']); ?> | BONFIRE GAMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4655;
            --primary-dark: #e63e4c;
            --secondary: #0f1923;
            --dark: #1a2b3c;
            --light: #ece8e1;
            --gray: #768079;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --gamejolt-blue: #191b21;
            --gamejolt-green: #6bc679;
            --gamejolt-purple: #8b6bc6;
            --gamejolt-orange: #ff7a33;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--gamejolt-blue);
            color: var(--light);
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--secondary);
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }
        
        .nav-links {
            margin-bottom: 30px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--light);
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary);
            color: white;
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            border-radius: 50%;
            cursor: pointer;
            background-size: cover;
            background-position: center;
        }
        
        /* Game Hero Section */
        .game-hero {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .game-cover-large {
            border-radius: 15px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .game-cover-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .game-cover-large .image-fallback {
            font-size: 4rem;
            color: white;
        }
        
        .game-info-sidebar {
            background: var(--secondary);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .game-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .btn {
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .game-meta {
            margin-bottom: 25px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .meta-label {
            color: var(--gray);
            font-weight: 500;
        }
        
        .meta-value {
            color: var(--light);
            font-weight: 600;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-released {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .status-development {
            background: var(--gamejolt-orange);
            color: white;
        }
        
        .status-paused {
            background: var(--warning);
            color: white;
        }
        
        .status-cancelled {
            background: var(--danger);
            color: white;
        }
        
        .developer-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .developer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .developer-details h4 {
            color: var(--light);
            margin-bottom: 5px;
        }
        
        .developer-details p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Game Details Section */
        .game-details {
            background: var(--secondary);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .game-description {
            color: var(--light);
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .game-description p {
            margin-bottom: 15px;
        }
        
        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .tag {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--light);
        }
        
        .system-requirements {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--gamejolt-green);
        }
        
        .system-requirements h4 {
            color: var(--gamejolt-green);
            margin-bottom: 15px;
        }
        
        .system-requirements pre {
            color: var(--light);
            white-space: pre-wrap;
            font-family: inherit;
            line-height: 1.6;
        }
        
        /* Related Games */
        .related-games {
            margin-bottom: 40px;
        }
        
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .related-game-card {
            background: var(--secondary);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .related-game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .related-game-cover {
            height: 150px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
        }
        
        .related-game-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .related-game-info {
            padding: 15px;
        }
        
        .related-game-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 8px;
        }
        
        .related-game-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        /* Comments Section */
        .comments-section {
            background: var(--secondary);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .comment-form {
            margin-bottom: 30px;
        }
        
        .comment-input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--light);
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
        }
        
        .comment-input:focus {
            outline: none;
            border-color: var(--gamejolt-green);
        }
        
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .comment {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .comment-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .comment-author {
            font-weight: bold;
            color: var(--light);
        }
        
        .comment-date {
            color: var(--gray);
            font-size: 0.8rem;
        }
        
        .comment-content {
            color: var(--light);
            line-height: 1.6;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .game-hero {
                grid-template-columns: 1fr;
            }
            
            .game-info-sidebar {
                order: -1;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 15px 10px;
            }
            
            .logo span, .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .games-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .game-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .game-details, .comments-section {
                padding: 20px;
            }
            
            .developer-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-fire"></i>
                <span>BONFIRE GAMES</span>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Início</span>
                </a>
                <a href="fangames.php" class="nav-link">
                    <i class="fas fa-gamepad"></i>
                    <span>Fangames</span>
                </a>
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
                <?php if ($user): ?>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
                <?php else: ?>
                <a href="login.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title"><?php echo htmlspecialchars($game['GameTitle']); ?></div>
                <div class="header-actions">
                    <?php if ($user): ?>
                    <a href="fangames.php" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <?php if ($isDeveloper): ?>
                    <a href="edit_game.php?id=<?php echo $gameId; ?>" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php endif; ?>
                    <div class="user-avatar" style="background-image: url('<?php echo htmlspecialchars($user['ProfilePhoto'] ?? ''); ?>')">
                        <?php if(empty($user['ProfilePhoto'])): ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i> Fazer Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Game Hero Section -->
            <div class="game-hero">
                <div class="game-info-sidebar">
                    <div class="game-actions">
                        <?php if ($game['GameFile'] || $game['DownloadLink']): ?>
                        <a href="<?php echo $game['GameFile'] ? htmlspecialchars($game['GameFile']) : htmlspecialchars($game['DownloadLink']); ?>" 
                           class="btn btn-primary" target="_blank" download>
                            <i class="fas fa-download"></i> Baixar Jogo
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-secondary">
                            <i class="fas fa-star"></i> Avaliar
                        </button>
                        
                        <button class="btn btn-secondary">
                            <i class="fas fa-share"></i> Compartilhar
                        </button>
                        
                        <?php if ($isDeveloper): ?>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir Jogo
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="game-meta">
                        <div class="meta-item">
                            <span class="meta-label">Status</span>
                            <span class="meta-value status-badge status-<?php echo strtolower(str_replace(' ', '-', $game['Status'])); ?>">
                                <?php echo htmlspecialchars($game['Status']); ?>
                            </span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Franquia</span>
                            <span class="meta-value"><?php echo htmlspecialchars($game['Franchise']); ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Gênero</span>
                            <span class="meta-value"><?php echo htmlspecialchars($game['Genre']); ?></span>
                        </div>
                        
                        <?php if ($game['ReleaseDate']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Data de Lançamento</span>
                            <span class="meta-value"><?php echo date('d/m/Y', strtotime($game['ReleaseDate'])); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <span class="meta-label">Downloads</span>
                            <span class="meta-value"><?php echo number_format($game['Downloads']); ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Avaliação</span>
                            <span class="meta-value">
                                <i class="fas fa-star" style="color: var(--gamejolt-green);"></i>
                                <?php echo number_format($game['Rating'], 1); ?>/5.0
                            </span>
                        </div>
                    </div>
                    
                    <div class="developer-info">
                        <div class="developer-avatar" style="background-image: url('<?php echo htmlspecialchars($game['ProfilePhoto'] ?? ''); ?>')">
                            <?php if(empty($game['ProfilePhoto'])): ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="developer-details">
                            <h4><?php echo htmlspecialchars($game['CustomerName']); ?></h4>
                            <p>@<?php echo htmlspecialchars($game['CustomerHandle']); ?></p>
                            <?php if ($game['CustomerBio']): ?>
                            <p style="margin-top: 5px; font-size: 0.8rem;"><?php echo htmlspecialchars($game['CustomerBio']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="game-cover-large">
                    <?php if (!empty($game['GameCover']) && file_exists($game['GameCover'])): ?>
                        <img src="<?php echo htmlspecialchars($game['GameCover']); ?>" 
                             alt="<?php echo htmlspecialchars($game['GameTitle']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="image-fallback" style="display: none;">
                            <i class="fas fa-gamepad"></i>
                        </div>
                    <?php else: ?>
                        <div class="image-fallback">
                            <i class="fas fa-gamepad"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Game Details Section -->
            <div class="game-details">
                <h2 class="section-title">Sobre o Jogo</h2>
                
                <div class="game-description">
                    <?php echo nl2br(htmlspecialchars($game['GameDescription'])); ?>
                </div>
                
                <?php if ($game['Tags']): ?>
                <div class="tags">
                    <?php 
                    $tags = explode(',', $game['Tags']);
                    foreach ($tags as $tag): 
                        $tag = trim($tag);
                        if (!empty($tag)):
                    ?>
                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php endif; ?>
                
                <?php if ($game['SystemRequirements']): ?>
                <div class="system-requirements">
                    <h4><i class="fas fa-cog"></i> Requisitos do Sistema</h4>
                    <pre><?php echo htmlspecialchars($game['SystemRequirements']); ?></pre>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Related Games -->
            <?php if (!empty($relatedGames)): ?>
            <div class="related-games">
                <h2 class="section-title">Jogos Relacionados</h2>
                <div class="games-grid">
                    <?php foreach ($relatedGames as $relatedGame): ?>
                    <div class="related-game-card" onclick="window.location.href='game.php?id=<?php echo $relatedGame['GameID']; ?>'">
                        <div class="related-game-cover">
                            <?php if (!empty($relatedGame['GameCover']) && file_exists($relatedGame['GameCover'])): ?>
                                <img src="<?php echo htmlspecialchars($relatedGame['GameCover']); ?>" 
                                     alt="<?php echo htmlspecialchars($relatedGame['GameTitle']); ?>">
                            <?php else: ?>
                                <div class="image-fallback" style="height: 100%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="related-game-info">
                            <h3 class="related-game-title"><?php echo htmlspecialchars($relatedGame['GameTitle']); ?></h3>
                            <div class="related-game-meta">
                                <span><?php echo htmlspecialchars($relatedGame['Franchise']); ?></span>
                                <span>@<?php echo htmlspecialchars($relatedGame['CustomerHandle']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Comments Section -->
            <div class="comments-section">
                <h2 class="section-title">Comentários e Avaliações</h2>
                
                <?php if ($user): ?>
                <div class="comment-form">
                    <textarea class="comment-input" placeholder="Deixe seu comentário sobre este jogo..."></textarea>
                    <button class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar Comentário
                    </button>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 30px;">
                    <p>Faça <a href="login.php" style="color: var(--gamejolt-green);">login</a> para deixar um comentário</p>
                </div>
                <?php endif; ?>
                
                <div class="comments-list">
                    <!-- Exemplo de comentário -->
                    <div class="comment">
                        <div class="comment-header">
                            <div class="comment-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="comment-author">Jogador123</div>
                                <div class="comment-date">15/08/2023</div>
                            </div>
                        </div>
                        <div class="comment-content">
                            Jogo incrível! A história é muito envolvente e os gráficos estão ótimos. Recomendo para todos os fãs da franquia!
                        </div>
                    </div>
                    
                    <!-- Exemplo de comentário -->
                    <div class="comment">
                        <div class="comment-header">
                            <div class="comment-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="comment-author">FangameLover</div>
                                <div class="comment-date">12/08/2023</div>
                            </div>
                        </div>
                        <div class="comment-content">
                            Gostei bastante da jogabilidade, mas achei que poderia ter mais conteúdo pós-game. No geral, é um ótimo fangame!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Efeitos interativos
        document.addEventListener('DOMContentLoaded', function() {
            // Efeito de hover nos cards de jogos relacionados
            document.querySelectorAll('.related-game-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Sistema de avaliação por estrelas (simplificado)
            const ratingButtons = document.querySelectorAll('.btn-secondary');
            ratingButtons.forEach(btn => {
                if (btn.textContent.includes('Avaliar')) {
                    btn.addEventListener('click', function() {
                        const rating = prompt('De 1 a 5, qual sua avaliação para este jogo?');
                        if (rating && rating >= 1 && rating <= 5) {
                            alert(`Obrigado por avaliar com ${rating} estrelas!`);
                        }
                    });
                }
            });
            
            // Sistema de compartilhamento
            document.querySelectorAll('.btn-secondary').forEach(btn => {
                if (btn.textContent.includes('Compartilhar')) {
                    btn.addEventListener('click', function() {
                        if (navigator.share) {
                            navigator.share({
                                title: '<?php echo htmlspecialchars($game['GameTitle']); ?>',
                                text: 'Confira este incrível fangame!',
                                url: window.location.href
                            });
                        } else {
                            // Fallback para copiar link
                            navigator.clipboard.writeText(window.location.href).then(() => {
                                alert('Link copiado para a área de transferência!');
                            });
                        }
                    });
                }
            });
            
            // Verificar se as imagens carregam corretamente
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    const fallback = this.nextElementSibling;
                    if (fallback && fallback.classList.contains('image-fallback')) {
                        this.style.display = 'none';
                        fallback.style.display = 'flex';
                    }
                });
            });
        });
    </script>
</body>
</html>