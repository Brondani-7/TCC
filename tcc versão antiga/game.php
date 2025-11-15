<?php
// game.php
require_once 'config.php';

// Verificar se o ID do jogo foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_GET['id']);
$game = getFangame($pdo, $gameId);
$user = isLoggedIn() ? getCurrentUser($pdo) : null;

// Verificar se o jogo existe
if (!$game) {
    header('Location: fangames.php');
    exit;
}

// Verificar se o usuário é o desenvolvedor
$isDeveloper = $user && isGameDeveloper($pdo, $gameId, $user['CustomerID']);

// Processar download - MODIFICADO: Permitir para qualquer usuário logado
if (isset($_GET['download']) && $user) {
    if (!empty($game['GameFile']) && file_exists($game['GameFile'])) {
        // Incrementar contador de downloads
        incrementDownloads($pdo, $gameId);
        
        // Forçar download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($game['GameFile']) . '"');
        header('Content-Length: ' . filesize($game['GameFile']));
        readfile($game['GameFile']);
        exit;
    } else {
        $error = "Arquivo do jogo não encontrado.";
    }
}

// Processar atualizações do desenvolvedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isDeveloper) {
    if (isset($_POST['update_game'])) {
        $gameTitle = trim($_POST['game_title']);
        $gameDescription = trim($_POST['game_description']);
        $status = $_POST['status'];
        $downloadLink = trim($_POST['download_link']);
        $systemRequirements = trim($_POST['system_requirements']);
        
        if (!empty($gameTitle)) {
            $stmt = $pdo->prepare("
                UPDATE fangames 
                SET GameTitle = ?, GameDescription = ?, Status = ?, DownloadLink = ?, SystemRequirements = ?, UpdatedAt = NOW() 
                WHERE GameID = ?
            ");
            $success = $stmt->execute([$gameTitle, $gameDescription, $status, $downloadLink, $systemRequirements, $gameId]);
            
            if ($success) {
                $message = "Jogo atualizado com sucesso!";
                $game = getFangame($pdo, $gameId); // Recarregar dados
            } else {
                $error = "Erro ao atualizar o jogo.";
            }
        }
    }
    
    // Processar upload de screenshots
    if (isset($_FILES['screenshots']) && $isDeveloper) {
        $uploadedScreenshots = [];
        
        foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['screenshots']['type'][$key];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = 'uploads/games/screenshots/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['screenshots']['name'][$key], PATHINFO_EXTENSION);
                    $filename = 'screenshot_' . $gameId . '_' . time() . '_' . $key . '.' . $file_extension;
                    $target_file = $upload_dir . $filename;
                    
                    if ($_FILES['screenshots']['size'][$key] <= 5 * 1024 * 1024) {
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $uploadedScreenshots[] = $target_file;
                        }
                    }
                }
            }
        }
        
        if (!empty($uploadedScreenshots)) {
            $message = "Screenshots adicionadas com sucesso!";
        }
    }
}

// Obter screenshots (simulado por enquanto)
$screenshots = getGameScreenshots($pdo, $gameId);
$gameCover = getGameCover($game);
$devAvatar = getDevAvatar($game);
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
            padding: 0;
        }
        
        /* Game Header */
        .game-hero {
            position: relative;
            min-height: 500px;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
            display: flex;
            align-items: flex-end;
            overflow: hidden;
        }
        
        .game-hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0.3;
        }
        
        .game-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, var(--gamejolt-blue) 100%);
        }
        
        .game-hero-content {
            position: relative;
            z-index: 2;
            padding: 40px;
            width: 100%;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 30px;
            align-items: end;
        }
        
        .game-cover-large {
            width: 240px;
            height: 320px;
            border-radius: 15px;
            background: var(--dark);
            background-size: cover;
            background-position: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .game-cover-large:hover {
            transform: translateY(-5px);
        }
        
        .game-info-main {
            flex: 1;
        }
        
        .game-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
            line-height: 1.1;
        }
        
        .game-developer-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .dev-avatar-large {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            background-size: cover;
            background-position: center;
            border: 3px solid var(--light);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .dev-info h4 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .dev-info .dev-handle {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .game-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5ab869;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 198, 121, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--light);
            border: 2px solid var(--light);
        }
        
        .btn-outline:hover {
            background: var(--light);
            color: var(--gamejolt-blue);
            transform: translateY(-2px);
        }
        
        /* Game Content */
        .game-content {
            padding: 50px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            align-items: start;
        }
        
        /* Main Column */
        .content-section {
            background: var(--secondary);
            border-radius: 20px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
        }
        
        .section-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .section-title {
            font-size: 1.6rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .game-description {
            line-height: 1.8;
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        /* Screenshots Section */
        .screenshots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .screenshot-item {
            aspect-ratio: 16/9;
            border-radius: 12px;
            background: var(--dark);
            background-size: cover;
            background-position: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .screenshot-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .screenshot-item:hover::before {
            opacity: 1;
        }
        
        .screenshot-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .screenshot-item::after {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .screenshot-item:hover::after {
            opacity: 1;
        }
        
        /* Sidebar Column */
        .sidebar-widget {
            background: var(--secondary);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .widget-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .widget-title i {
            color: var(--primary);
        }
        
        .detail-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .detail-value {
            color: var(--light);
            font-weight: 500;
            text-align: right;
        }
        
        .status-badge {
            padding: 6px 16px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: var(--dark);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--gamejolt-green);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Developer Tools */
        .developer-tools {
            background: linear-gradient(135deg, var(--secondary), var(--dark));
            border-radius: 20px;
            padding: 30px;
            border-left: 4px solid var(--gamejolt-green);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        
        .tools-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .tools-header i {
            color: var(--gamejolt-green);
            font-size: 1.5rem;
        }
        
        .tools-grid {
            display: grid;
            gap: 15px;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--light);
            font-size: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: var(--dark);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gamejolt-green);
            box-shadow: 0 0 0 3px rgba(107, 198, 121, 0.1);
        }
        
        textarea.form-control {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }
        
        /* Alerts */
        .alert {
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            background: rgba(107, 198, 121, 0.15);
            border: 1px solid var(--gamejolt-green);
            color: var(--gamejolt-green);
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: var(--secondary);
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .close-btn {
            background: none;
            border: none;
            color: var(--gray);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: var(--light);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .game-hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .game-cover-large {
                margin: 0 auto;
            }
            
            .game-developer-info {
                justify-content: center;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .game-hero {
                min-height: 400px;
            }
            
            .game-title {
                font-size: 2.5rem;
            }
            
            .game-cover-large {
                width: 200px;
                height: 280px;
            }
        }
        
        @media (max-width: 768px) {
            .game-content {
                padding: 30px 20px;
            }
            
            .game-hero-content {
                padding: 30px 20px;
            }
            
            .game-title {
                font-size: 2rem;
            }
            
            .game-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .screenshots-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-section, .sidebar-widget {
                padding: 25px;
            }
        }
        
        @media (max-width: 480px) {
            .game-hero {
                min-height: 350px;
            }
            
            .game-title {
                font-size: 1.8rem;
            }
            
            .game-cover-large {
                width: 160px;
                height: 240px;
            }
            
            .section-title {
                font-size: 1.3rem;
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
            <!-- Game Hero Section -->
            <div class="game-hero">
                <div class="game-hero-bg" style="background-image: url('<?php echo $gameCover ?: ''; ?>')"></div>
                <div class="game-hero-overlay"></div>
                <div class="game-hero-content">
                    <div class="game-cover-large" style="background-image: url('<?php echo $gameCover ?: ''; ?>')">
                        <?php if(!$gameCover): ?>
                            <i class="fas fa-gamepad"></i>
                        <?php endif; ?>
                    </div>
                    <div class="game-info-main">
                        <h1 class="game-title"><?php echo htmlspecialchars($game['GameTitle']); ?></h1>
                        <div class="game-developer-info">
                            <div class="dev-avatar-large" style="background-image: url('<?php echo $devAvatar ?: ''; ?>')">
                                <?php if(!$devAvatar): ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="dev-info">
                                <h4><?php echo htmlspecialchars($game['CustomerName']); ?></h4>
                                <div class="dev-handle">@<?php echo htmlspecialchars($game['CustomerHandle'] ?? 'dev'); ?></div>
                            </div>
                        </div>
                        <div class="game-actions">
    <?php if (!empty($game['GameFile']) && $user): ?>
        <a href="?id=<?php echo $gameId; ?>&download=1" class="btn btn-primary">
            <i class="fas fa-download"></i> Download do Jogo
        </a>
    <?php elseif (!empty($game['DownloadLink'])): ?>
        <a href="<?php echo htmlspecialchars($game['DownloadLink']); ?>" target="_blank" class="btn btn-primary">
            <i class="fas fa-external-link-alt"></i> Baixar do Link Externo
        </a>
    <?php elseif (!$user): ?>
        <a href="login.php" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i> Faça Login para Baixar
        </a>
    <?php endif; ?>
    
    <?php if ($isDeveloper): ?>
        <button class="btn btn-secondary" onclick="openEditModal()">
            <i class="fas fa-edit"></i> Editar Jogo
        </button>
    <?php endif; ?>
    
    <a href="fangames.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Voltar para Fangames
    </a>
</div>
                    </div>
                </div>
            </div>
            
            <!-- Game Content -->
            <div class="game-content">
                <!-- Alert Messages -->
                <?php if (isset($message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="content-grid">
                    <!-- Main Column -->
                    <div class="main-column">
                        <!-- Description -->
                        <div class="content-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h2 class="section-title">Descrição do Jogo</h2>
                            </div>
                            <div class="game-description">
                                <?php echo nl2br(htmlspecialchars($game['GameDescription'])); ?>
                            </div>
                        </div>
                        
                        <!-- Screenshots -->
                        <div class="content-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <h2 class="section-title">Galeria de Screenshots</h2>
                            </div>
                            <div class="screenshots-grid">
                                <?php foreach ($screenshots as $screenshot): ?>
                                    <div class="screenshot-item" style="background-image: url('<?php echo $screenshot; ?>')"
                                         onclick="openScreenshotModal('<?php echo $screenshot; ?>')">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if ($isDeveloper): ?>
                            <form method="POST" enctype="multipart/form-data" style="margin-top: 25px;">
                                <div class="form-group">
                                    <label class="form-label">Adicionar Novas Screenshots</label>
                                    <input type="file" name="screenshots[]" multiple accept="image/*" class="form-control">
                                    <small style="color: var(--gray); margin-top: 8px; display: block;">
                                        Selecione múltiplas imagens (máx. 5MB cada)
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Screenshots
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        
                        <!-- System Requirements -->
                        <?php if (!empty($game['SystemRequirements'])): ?>
                        <div class="content-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h2 class="section-title">Requisitos do Sistema</h2>
                            </div>
                            <div class="game-description">
                                <?php echo nl2br(htmlspecialchars($game['SystemRequirements'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sidebar Column -->
                    <div class="sidebar-column">
                        <!-- Game Details -->
                        <div class="sidebar-widget">
                            <h3 class="widget-title">
                                <i class="fas fa-info-circle"></i>
                                Informações do Jogo
                            </h3>
                            <div class="detail-list">
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $game['Status'])); ?>">
                                            <?php echo $game['Status']; ?>
                                        </span>
                                    </span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Franquia</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($game['Franchise']); ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Gênero</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($game['Genre']); ?></span>
                                </div>
                                
                                <?php if (!empty($game['Tags'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Tags</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($game['Tags']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($game['ReleaseDate'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Data de Lançamento</span>
                                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($game['ReleaseDate'])); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Publicado em</span>
                                    <span class="detail-value"><?php echo date('d/m/Y \à\s H:i', strtotime($game['CreatedAt'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="sidebar-widget">
                            <h3 class="widget-title">
                                <i class="fas fa-chart-bar"></i>
                                Estatísticas
                            </h3>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo number_format($game['Downloads']); ?></div>
                                    <div class="stat-label">Downloads</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo number_format($game['Rating'], 1); ?></div>
                                    <div class="stat-label">Avaliação</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Developer Tools -->
                        <?php if ($isDeveloper): ?>
                        <div class="developer-tools">
                            <div class="tools-header">
                                <i class="fas fa-tools"></i>
                                <h3>Ferramentas do Desenvolvedor</h3>
                            </div>
                            
                            <div class="tools-grid">
                                <?php if (!empty($game['GameFile'])): ?>
                                    <a href="?id=<?php echo $gameId; ?>&download=1" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Fazer Download
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-secondary" onclick="openEditModal()">
                                    <i class="fas fa-edit"></i> Editar Informações
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Editar Informações do Jogo</h2>
                <button class="close-btn" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="game_title">Título do Jogo</label>
                    <input type="text" id="game_title" name="game_title" class="form-control"
                           value="<?php echo htmlspecialchars($game['GameTitle']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="game_description">Descrição</label>
                    <textarea id="game_description" name="game_description" class="form-control" required><?php echo htmlspecialchars($game['GameDescription']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Em Desenvolvimento" <?php echo $game['Status'] === 'Em Desenvolvimento' ? 'selected' : ''; ?>>Em Desenvolvimento</option>
                        <option value="Lançado" <?php echo $game['Status'] === 'Lançado' ? 'selected' : ''; ?>>Lançado</option>
                        <option value="Pausado" <?php echo $game['Status'] === 'Pausado' ? 'selected' : ''; ?>>Pausado</option>
                        <option value="Cancelado" <?php echo $game['Status'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="download_link">Link de Download Externo</label>
                    <input type="url" id="download_link" name="download_link" class="form-control"
                           value="<?php echo htmlspecialchars($game['DownloadLink'] ?? ''); ?>"
                           placeholder="https://exemplo.com/download">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="system_requirements">Requisitos do Sistema</label>
                    <textarea id="system_requirements" name="system_requirements" class="form-control"
                              placeholder="Descreva os requisitos mínimos do sistema..."><?php echo htmlspecialchars($game['SystemRequirements'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" name="update_game" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Screenshot Modal -->
    <div id="screenshotModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Visualizar Screenshot</h2>
                <button class="close-btn" onclick="closeScreenshotModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <img id="screenshotImage" src="" alt="Screenshot" style="width: 100%; border-radius: 12px; margin-bottom: 20px;">
            <div style="text-align: center;">
                <button class="btn btn-outline" onclick="closeScreenshotModal()">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function openScreenshotModal(src) {
            document.getElementById('screenshotImage').src = src;
            document.getElementById('screenshotModal').style.display = 'flex';
        }
        
        function closeScreenshotModal() {
            document.getElementById('screenshotModal').style.display = 'none';
        }
        
        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const screenshotModal = document.getElementById('screenshotModal');
            
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === screenshotModal) {
                closeScreenshotModal();
            }
        }
        
        // Fechar modais com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditModal();
                closeScreenshotModal();
            }
        });
    </script>
</body>
</html>