<?php
// game.php - Página de detalhes do fangame
require_once 'config.php';


// Verificar se o ID do jogo foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fangames.php');
    exit;
}


$gameId = intval($_GET['id']);

// Buscar informações do jogo
$game = getFangame($pdo, $gameId);

if (!$game) {
    header('Location: fangames.php');
    exit;
}

// Verificar se o usuário está logado
$user = null;
if (isLoggedIn()) {
    $user = getCurrentUser($pdo);
}

// Verificar se é o desenvolvedor do jogo
$isDeveloper = false;
if ($user) {
    $isDeveloper = isGameDeveloper($pdo, $gameId, $user['CustomerID']);
}

// Processar download
if (isset($_GET['download']) && $user) {
    if (incrementDownloads($pdo, $gameId)) {
        // Redirecionar para o link de download
        if (!empty($game['DownloadLink'])) {
            header('Location: ' . $game['DownloadLink']);
            exit;
        } elseif (!empty($game['GameFile'])) {
            // Forçar download do arquivo
            $filePath = $game['GameFile'];
            if (file_exists($filePath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }
    }
}

// Buscar screenshots (simulado por enquanto)
$screenshots = getGameScreenshots($pdo, $gameId);

// Incrementar visualizações (opcional)
// incrementGameViews($pdo, $gameId);
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
            position: relative;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--secondary);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            min-width: 180px;
            z-index: 1000;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--light);
            text-decoration: none;
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .user-dropdown-item:last-child {
            border-bottom: none;
        }
        
        .user-dropdown-item:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .user-dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }
        
        .user-avatar-container {
            position: relative;
            display: inline-block;
        }
        
        /* Game Header */
        .game-header {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .game-cover-section {
            position: relative;
        }
        
        .game-cover {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .game-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .game-cover-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
            color: white;
            font-size: 4rem;
        }
        
        .game-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }
        
        .status-lançado {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .status-em-desenvolvimento {
            background: var(--gamejolt-orange);
            color: white;
        }
        
        .status-pausado {
            background: var(--warning);
            color: white;
        }
        
        .status-cancelado {
            background: var(--danger);
            color: white;
        }
        
        .game-info-section {
            padding: 20px 0;
        }
        
        .game-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 10px;
            line-height: 1.2;
        }
        
        .game-developer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .dev-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }
        
        .dev-info {
            display: flex;
            flex-direction: column;
        }
        
        .dev-name {
            color: var(--light);
            font-weight: 500;
        }
        
        .dev-handle {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .game-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--secondary);
            border-radius: 12px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--gamejolt-green);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .game-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .meta-tag {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--light);
        }
        
        .download-section {
            margin-top: 30px;
        }
        
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: var(--gamejolt-green);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        .download-btn.disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }
        
        .login-prompt {
            background: var(--secondary);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }
        
        /* Game Content */
        .game-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .game-description-section {
            background: var(--secondary);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
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
            line-height: 1.8;
            color: var(--light);
            font-size: 1.1rem;
        }
        
        .game-description p {
            margin-bottom: 15px;
        }
        
        /* Screenshots Section */
        .screenshots-section {
            background: var(--secondary);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .screenshots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .screenshot-item {
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .screenshot-item:hover {
            transform: scale(1.05);
        }
        
        .screenshot-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        /* System Requirements */
        .requirements-section {
            background: var(--secondary);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .requirements-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .requirements-column h4 {
            color: var(--gamejolt-green);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .requirement-item {
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }
        
        .requirement-label {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .requirement-value {
            color: var(--light);
            font-weight: 500;
        }
        
        /* Sidebar Info */
        .game-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .info-card {
            background: var(--secondary);
            padding: 25px;
            border-radius: 15px;
        }
        
        .info-list {
            list-style: none;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .info-value {
            color: var(--light);
            font-weight: 500;
            text-align: right;
        }
        
        .tags-section {
            background: var(--secondary);
            padding: 25px;
            border-radius: 15px;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            padding: 6px 12px;
            background: rgba(107, 198, 121, 0.2);
            color: var(--gamejolt-green);
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid rgba(107, 198, 121, 0.3);
        }
        
        /* Developer Actions */
        .developer-actions {
            background: var(--secondary);
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .action-btn:hover {
            background: var(--primary-dark);
        }
        
        .action-btn.edit {
            background: var(--gamejolt-orange);
        }
        
        .action-btn.edit:hover {
            background: #e6692a;
        }
        
        .action-btn.delete {
            background: var(--danger);
        }
        
        .action-btn.delete:hover {
            background: #c0392b;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .game-header {
                grid-template-columns: 1fr;
            }
            
            .game-cover {
                height: 300px;
                max-width: 400px;
                margin: 0 auto;
            }
            
            .game-content {
                grid-template-columns: 1fr;
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
            
            .requirements-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .game-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .game-title {
                font-size: 2rem;
            }
            
            .screenshots-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .game-cover {
                height: 250px;
            }
            
            .game-title {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
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
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">  
            <div class="header">
                <div class="page-title">Detalhes do Fangame</div>
                <div class="header-actions">
                    <?php if ($user): ?>
                    <div class="user-avatar-container">
                        <div class="user-avatar" id="userAvatar" style="background-image: url('<?php echo htmlspecialchars($user['ProfilePhoto'] ?? ''); ?>')">
                            <?php if(empty($user['ProfilePhoto'])): ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="perfil.php" class="user-dropdown-item">
                                <i class="fas fa-user"></i>
                                Meu Perfil
                            </a>
                            <a href="logout.php" class="user-dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="download-btn" style="text-decoration: none; font-size: 1rem;">
                        <i class="fas fa-sign-in-alt"></i>
                        Fazer Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Game Header -->
            <div class="game-header">
                <div class="game-cover-section">
                    <div class="game-cover">
                        <?php 
                        $coverUrl = getGameCover($game);
                        if (!empty($coverUrl)): ?>
                            <img src="<?php echo htmlspecialchars($coverUrl); ?>" 
                                 alt="<?php echo htmlspecialchars($game['GameTitle']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="game-cover-fallback" style="display: none;">
                                <i class="fas fa-gamepad"></i>
                            </div>
                        <?php else: ?>
                            <div class="game-cover-fallback">
                                <i class="fas fa-gamepad"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="game-status status-<?php echo strtolower(str_replace(' ', '-', $game['Status'])); ?>">
                            <?php echo htmlspecialchars($game['Status']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="game-info-section">
                    <h1 class="game-title"><?php echo htmlspecialchars($game['GameTitle']); ?></h1>
                    
                    <div class="game-developer">
                        <div class="dev-avatar" style="background-image: url('<?php echo htmlspecialchars(getDevAvatar($game)); ?>')">
                            <?php if(empty($game['ProfilePhoto'])): ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="dev-info">
                            <span class="dev-name"><?php echo htmlspecialchars($game['CustomerName']); ?></span>
                            <span class="dev-handle">@<?php echo htmlspecialchars($game['CustomerHandle'] ?? $game['CustomerName']); ?></span>
                        </div>
                    </div>
                    
                    <div class="game-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($game['Downloads'] ?? 0); ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($game['Rating'] ?? 0, 1); ?></div>
                            <div class="stat-label">Avaliação</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $game['FileSize'] ?? 'N/A'; ?></div>
                            <div class="stat-label">Tamanho</div>
                        </div>
                    </div>
                    
                    <div class="game-meta">
                        <span class="meta-tag"><?php echo htmlspecialchars($game['Franchise'] ?? 'Franquia não especificada'); ?></span>
                        <span class="meta-tag"><?php echo htmlspecialchars($game['Genre'] ?? 'Gênero não especificado'); ?></span>
                        <span class="meta-tag">Lançado em: <?php echo date('d/m/Y', strtotime($game['CreatedAt'])); ?></span>
                        <?php if (!empty($game['ReleaseDate'])): ?>
                        <span class="meta-tag">Data de Lançamento: <?php echo date('d/m/Y', strtotime($game['ReleaseDate'])); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="download-section">
    <?php if ($user): ?>
        <?php if (!empty($game['DownloadLink']) || !empty($game['GameFile'])): ?>
            <a href="download.php?id=<?php echo $gameId; ?>" class="download-btn" onclick="trackDownload(<?php echo $gameId; ?>)">
                <i class="fas fa-download"></i>
                Baixar Fangame
            </a>
            <?php if (!empty($game['FileSize'])): ?>
                <div style="margin-top: 10px; color: var(--gray); font-size: 0.9rem;">
                    <i class="fas fa-hdd"></i> Tamanho: <?php echo htmlspecialchars($game['FileSize']); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <button class="download-btn disabled" disabled>
                <i class="fas fa-download"></i>
                Download Indisponível
            </button>
            <div style="margin-top: 10px; color: var(--warning); font-size: 0.9rem;">
                <i class="fas fa-exclamation-triangle"></i> Este fangame ainda não possui arquivo para download.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="login-prompt">
            <p>Faça login para baixar este fangame</p>
            <a href="login.php" class="download-btn" style="text-decoration: none; margin-top: 10px; display: inline-block;">
                <i class="fas fa-sign-in-alt"></i>
                Fazer Login
            </a>
        </div>
    <?php endif; ?>
</div>
                </div>
            </div>
            
            <!-- Game Content -->
            <div class="game-content">
                <div class="game-main">
                    <!-- Description -->
                    <div class="game-description-section">
                        <h3 class="section-title">Descrição</h3>
                        <div class="game-description">
                            <?php 
                            $description = $game['GameDescription'] ?? 'Este fangame ainda não possui uma descrição.';
                            echo nl2br(htmlspecialchars($description)); 
                            ?>
                        </div>
                    </div>
                    
                    <!-- Screenshots -->
                    <?php if (!empty($screenshots)): ?>
                    <div class="screenshots-section">
                        <h3 class="section-title">Screenshots</h3>
                        <div class="screenshots-grid">
                            <?php foreach ($screenshots as $screenshot): ?>
                            <div class="screenshot-item">
                                <img src="<?php echo htmlspecialchars($screenshot); ?>" alt="Screenshot do jogo">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- System Requirements -->
                    <?php if (!empty($game['SystemRequirements'])): ?>
                    <div class="requirements-section">
                        <h3 class="section-title">Requisitos do Sistema</h3>
                        <div class="requirements-grid">
                            <div class="requirements-column">
                                <h4>Mínimos</h4>
                                <div class="requirement-item">
                                    <div class="requirement-value">
                                        <?php echo nl2br(htmlspecialchars($game['SystemRequirements'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="game-sidebar">
                    <!-- Game Info -->
                    <div class="info-card">
                        <h3 class="section-title">Informações</h3>
                        <ul class="info-list">
                            <li class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['Status']); ?></span>
                            </li>
                            <li class="info-item">
                                <span class="info-label">Desenvolvedor</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['CustomerName']); ?></span>
                            </li>
                            <li class="info-item">
                                <span class="info-label">Franquia</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['Franchise'] ?? 'N/A'); ?></span>
                            </li>
                            <li class="info-item">
                                <span class="info-label">Gênero</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['Genre'] ?? 'N/A'); ?></span>
                            </li>
                            <li class="info-item">
                                <span class="info-label">Tamanho</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['FileSize'] ?? 'N/A'); ?></span>
                            </li>
                            <li class="info-item">
                                <span class="info-label">Data de Publicação</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($game['CreatedAt'])); ?></span>
                            </li>
                            <?php if (!empty($game['ReleaseDate'])): ?>
                            <li class="info-item">
                                <span class="info-label">Data de Lançamento</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($game['ReleaseDate'])); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($game['Tags'])): ?>
                    <div class="tags-section">
                        <h3 class="section-title">Tags</h3>
                        <div class="tags-container">
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
                    </div>
                    <?php endif; ?>
                    
                    <!-- Developer Actions -->
                    <?php if ($isDeveloper): ?>
                    <div class="developer-actions">
                        <h3 class="section-title">Ações do Desenvolvedor</h3>
                        <div class="action-buttons">
                            <a href="edit_fangame.php?id=<?php echo $gameId; ?>" class="action-btn edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="delete_fangame.php?id=<?php echo $gameId; ?>" class="action-btn delete" onclick="return confirm('Tem certeza que deseja excluir este fangame?')">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                            <a href="stats_fangame.php?id=<?php echo $gameId; ?>" class="action-btn">
                                <i class="fas fa-chart-bar"></i> Estatísticas
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User dropdown functionality
        const userAvatar = document.getElementById('userAvatar');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userAvatar && userDropdown) {
            userAvatar.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            
            // Fechar dropdown ao clicar fora
            document.addEventListener('click', function() {
                userDropdown.classList.remove('active');
            });
            
            // Prevenir fechamento ao clicar no dropdown
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Image error handling
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.game-cover img').forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback && fallback.classList.contains('game-cover-fallback')) {
                        fallback.style.display = 'flex';
                    }
                });
            });
            
            // Screenshot click to enlarge (basic implementation)
            document.querySelectorAll('.screenshot-item').forEach(item => {
                item.addEventListener('click', function() {
                    const imgSrc = this.querySelector('img').src;
                    // Aqui você pode implementar um lightbox/modal
                    window.open(imgSrc, '_blank');
                });
            });
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
