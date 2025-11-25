<?php
require_once 'config.php';

// Processar pesquisa
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['q'])) {
    $searchQuery = htmlspecialchars($_GET['q']);
    // Lógica de busca seria implementada aqui
}

// Obter dados para a página inicial
$recentTopics = getRecentTopics($pdo, 8);
$categories = getForumCategories($pdo);
$user = getCurrentUser($pdo);

// =============================================================================
// DETECÇÃO DE NAVEGADOR E DISPOSITIVO
// =============================================================================
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = false;
$isTablet = false;
$isDesktop = true;
$browser = 'Unknown';
$browserVersion = '';
$os = 'Unknown';

// Detectar dispositivo
if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent)) {
    $isMobile = true;
    $isDesktop = false;
    if (preg_match('/(ipad|tablet|android(?!.*mobile))/i', $userAgent)) {
        $isTablet = true;
        $isMobile = false;
    }
}

// Detectar navegador
if (preg_match('/Firefox\/([0-9.]+)/i', $userAgent, $matches)) {
    $browser = 'Firefox';
    $browserVersion = $matches[1];
} elseif (preg_match('/Chrome\/([0-9.]+)/i', $userAgent, $matches)) {
    $browser = 'Chrome';
    $browserVersion = $matches[1];
} elseif (preg_match('/Safari\/([0-9.]+)/i', $userAgent, $matches)) {
    $browser = 'Safari';
    $browserVersion = $matches[1];
} elseif (preg_match('/Edge\/([0-9.]+)/i', $userAgent, $matches)) {
    $browser = 'Edge';
    $browserVersion = $matches[1];
} elseif (preg_match('/MSIE ([0-9.]+)/i', $userAgent, $matches)) {
    $browser = 'Internet Explorer';
    $browserVersion = $matches[1];
}

// Detectar sistema operacional
if (preg_match('/Windows NT ([0-9.]+)/i', $userAgent, $matches)) {
    $os = 'Windows ' . $matches[1];
} elseif (preg_match('/Mac OS X ([0-9_]+)/i', $userAgent, $matches)) {
    $os = 'macOS ' . str_replace('_', '.', $matches[1]);
} elseif (preg_match('/Android ([0-9.]+)/i', $userAgent, $matches)) {
    $os = 'Android ' . $matches[1];
} elseif (preg_match('/iOS ([0-9_]+)/i', $userAgent, $matches)) {
    $os = 'iOS ' . str_replace('_', '.', $matches[1]);
} elseif (preg_match('/Linux/i', $userAgent)) {
    $os = 'Linux';
}

// Configurações baseadas no dispositivo
$itemsPerPage = $isMobile ? 5 : ($isTablet ? 8 : 10);
$enableAnimations = !$isMobile; // Desativar animações em mobile para performance
$enableSidebar = !$isMobile; // Sidebar sempre visível apenas em desktop

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Bonfire Games - Comunidade de Fangames</title>
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
            --gamejolt-green: #6bc679;
            --gamejolt-purple: #8b6bc6;
            --gamejolt-blue: #191b21;
            --mobile-breakpoint: 768px;
            --tablet-breakpoint: 1024px;
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
            width: <?php echo $enableSidebar ? '250px' : '100%'; ?>;
            background-color: var(--secondary);
            padding: 20px;
            <?php if ($enableSidebar): ?>
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            <?php else: ?>
            position: relative;
            order: 2;
            <?php endif; ?>
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
            <?php if ($isMobile) echo "font-size: 1.1rem; padding: 15px;"; ?>
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
            <?php if ($enableSidebar): ?>
            margin-left: 250px;
            <?php else: ?>
            margin-left: 0;
            order: 1;
            <?php endif; ?>
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            <?php if ($isMobile): ?>
            flex-direction: column;
            gap: 15px;
            text-align: center;
            <?php endif; ?>
        }

        .page-title {
            font-size: <?php echo $isMobile ? '1.8rem' : '2rem'; ?>;
            font-weight: bold;
            color: var(--light);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            <?php if ($isMobile): ?>
            justify-content: center;
            width: 100%;
            <?php endif; ?>
        }

        .new-topic-btn {
            background: var(--gamejolt-green);
            color: white;
            border: none;
            padding: <?php echo $isMobile ? '12px 20px' : '10px 20px'; ?>;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            <?php if ($isMobile) echo "font-size: 1.1rem;"; ?>
        }

        .new-topic-btn:hover {
            background: #5ab869;
            <?php if ($enableAnimations) echo "transform: translateY(-2px);"; ?>
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--dark) 100%);
            border-radius: 15px;
            padding: <?php echo $isMobile ? '30px 20px' : '50px 40px'; ?>;
            margin-bottom: 40px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .hero-title {
            font-size: <?php echo $isMobile ? '2rem' : '3rem'; ?>;
            font-weight: 900;
            margin-bottom: 15px;
            color: var(--primary);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            position: relative;
            z-index: 10;
        }

        .hero-subtitle {
            font-size: <?php echo $isMobile ? '1.1rem' : '1.3rem'; ?>;
            color: var(--light);
            margin-bottom: 30px;
            opacity: 0.9;
            position: relative;
            z-index: 10;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .hero-stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--gamejolt-green);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Search Container */
        .search-container {
            max-width: 600px;
            margin: 0 auto 40px auto;
        }

        .search-container form {
            display: flex;
            border: 2px solid var(--gamejolt-green);
            border-radius: 30px;
            background-color: rgba(15, 25, 35, 0.9);
            padding: 8px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        .search-container input[type="text"] {
            flex: 1;
            border: none;
            outline: none;
            font-size: 1.1rem;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: 500;
            background: transparent;
            color: var(--light);
        }

        .search-container input[type="text"]::placeholder {
            color: var(--gray);
        }

        .search-container button {
            background-color: var(--gamejolt-green);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 25px;
            margin-left: 10px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-container button:hover {
            background-color: #5ab869;
            transform: translateY(-2px);
        }

        /* Sections */
        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }

        /* Recent Topics */
        .topics-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: <?php 
                if ($isMobile) echo '1fr'; 
                elseif ($isTablet) echo 'repeat(auto-fit, minmax(300px, 1fr))'; 
                else echo 'repeat(auto-fit, minmax(350px, 1fr))'; 
            ?>;
        }

        .topic-card {
            background: var(--secondary);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--gamejolt-green);
            <?php if ($enableAnimations): ?>
            transform: translateY(0);
            <?php endif; ?>
        }

        .topic-card:hover {
            <?php if ($enableAnimations): ?>
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            <?php endif; ?>
        }

        .topic-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--light);
        }

        .topic-title a {
            color: inherit;
            text-decoration: none;
        }

        .topic-title a:hover {
            color: var(--gamejolt-green);
        }

        .topic-meta {
            font-size: 0.8rem;
            color: var(--gray);
            margin-bottom: 10px;
            display: flex;
            gap: 15px;
        }

        .topic-excerpt {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .topic-stats {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .topic-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: <?php 
                if ($isMobile) echo '1fr'; 
                elseif ($isTablet) echo 'repeat(auto-fit, minmax(250px, 1fr))'; 
                else echo 'repeat(auto-fit, minmax(280px, 1fr))'; 
            ?>;
        }

        .category-card {
            background: var(--secondary);
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--gamejolt-purple);
            text-decoration: none;
            color: inherit;
            display: block;
            <?php if ($enableAnimations): ?>
            transform: translateY(0);
            <?php endif; ?>
        }

        .category-card:hover {
            <?php if ($enableAnimations): ?>
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            <?php endif; ?>
            color: inherit;
        }

        .category-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--gamejolt-purple);
        }

        .category-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--light);
        }

        .category-description {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .category-stats {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Browser Notification */
        .browser-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--secondary);
            color: var(--light);
            padding: 15px 20px;
            border-radius: 8px;
            border-left: 4px solid var(--gamejolt-green);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            max-width: 300px;
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(400px);
            transition: transform 0.4s ease;
            <?php if ($isMobile): ?>
            top: 70px;
            right: 10px;
            left: 10px;
            max-width: none;
            <?php endif; ?>
        }
        
        .browser-notification.show {
            transform: translateX(0);
        }
        
        .browser-notification i {
            font-size: 1.5rem;
            color: var(--gamejolt-green);
        }
        
        .browser-notification-content {
            flex: 1;
        }
        
        .browser-notification-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .browser-notification-message {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .browser-notification-close {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }
        
        .browser-notification-close:hover {
            color: var(--light);
            background: rgba(255, 255, 255, 0.1);
        }

        /* Mobile Navigation */
        .mobile-nav-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2rem;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        /* Quick Actions - AUMENTADOS */
        .quick-actions {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .quick-action {
            background: var(--dark);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 25px 30px;
            text-decoration: none;
            color: var(--light);
            flex: 1;
            min-width: 200px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .quick-action:hover {
            background: var(--secondary);
            border-color: var(--gamejolt-green);
            color: var(--light);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .quick-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .quick-action:hover::before {
            left: 100%;
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--gamejolt-green);
        }

        .action-text {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .action-description {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 8px;
            opacity: 0.8;
        }

        /* Browser-specific optimizations */
        <?php if ($browser === 'Chrome'): ?>
        .topic-card, .category-card {
            backdrop-filter: blur(10px);
        }
        <?php endif; ?>
        
        <?php if ($browser === 'Firefox'): ?>
        .topic-card, .category-card {
            background: var(--dark);
        }
        <?php endif; ?>
        
        <?php if ($browser === 'Safari'): ?>
        .topic-card, .category-card {
            -webkit-backdrop-filter: blur(10px);
        }
        <?php endif; ?>

        /* Performance optimizations for mobile */
        <?php if ($isMobile): ?>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        .nav-link, .new-topic-btn, .topic-card, .category-card {
            touch-action: manipulation;
        }
        <?php endif; ?>

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
            .mobile-nav-toggle {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                transition: left 0.3s ease;
                z-index: 999;
                padding-top: 70px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
            
            .logo span, .nav-link span {
                display: inline;
            }
            
            .hero-stats {
                gap: 20px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .quick-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .quick-action {
                min-width: auto;
                padding: 20px;
            }
            
            .bonfire-container {
                height: 140px;
                width: 110px;
            }
            
            .bonfire-base {
                width: 90px;
                height: 45px;
            }
            
            .log-1, .log-2, .log-3 {
                width: 10px;
            }
            
            .log-1 {
                height: 60px;
                left: 10px;
            }
            
            .log-2 {
                height: 70px;
                left: 40px;
            }
            
            .log-3 {
                height: 55px;
                left: 70px;
            }
            
            .flame-main {
                width: 45px;
                height: 90px;
                left: 32px;
                bottom: 45px;
            }
            
            .flame-left {
                width: 35px;
                height: 70px;
                left: 20px;
                bottom: 45px;
            }
            
            .flame-right {
                width: 35px;
                height: 75px;
                left: 50px;
                bottom: 45px;
            }
            
            .browser-notification {
                top: 70px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .bonfire-container {
                height: 120px;
                width: 90px;
            }
            
            .bonfire-base {
                width: 70px;
                height: 35px;
            }
            
            .log-1, .log-2, .log-3 {
                width: 8px;
            }
            
            .log-1 {
                height: 50px;
                left: 8px;
            }
            
            .log-2 {
                height: 55px;
                left: 31px;
            }
            
            .log-3 {
                height: 45px;
                left: 54px;
            }
            
            .flame-main {
                width: 35px;
                height: 70px;
                left: 27px;
                bottom: 35px;
            }
            
            .flame-left {
                width: 25px;
                height: 55px;
                left: 18px;
                bottom: 35px;
            }
            
            .flame-right {
                width: 25px;
                height: 60px;
                left: 40px;
                bottom: 35px;
            }
        }
    </style>
</head>
<body data-device="<?php echo $isMobile ? 'mobile' : ($isTablet ? 'tablet' : 'desktop'); ?>" 
      data-browser="<?php echo strtolower($browser); ?>">
    
    <!-- Browser Notification -->
    <div class="browser-notification" id="browserNotification">
        <i class="fas fa-info-circle"></i>
        <div class="browser-notification-content">
            <div class="browser-notification-title">Informações do Navegador</div>
            <div class="browser-notification-message">
                Você está usando <strong><?php echo $browser . ' ' . $browserVersion; ?></strong> 
                no <strong><?php echo $os; ?></strong>
            </div>
        </div>
        <button class="browser-notification-close" id="browserNotificationClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Mobile Navigation Toggle -->
    <?php if ($isMobile): ?>
    <button class="mobile-nav-toggle" id="mobileNavToggle">
        <i class="fas fa-bars"></i>
    </button>
    <?php endif; ?>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-fire"></i>
                <span>BONFIRE GAMES</span>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link active">
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
                <div class="page-title">Comunidade Bonfire Games</div>
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
                    <a href="login.php" class="new-topic-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Fazer Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="hero-section">
                <h1 class="hero-title">BONFIRE GAMES</h1>
                <p class="hero-subtitle">Sua comunidade de fangames e discussões sobre jogos</p>
            </div>

            <!-- Quick Actions - AUMENTADOS -->
            <div class="quick-actions">
                <a href="forum.php" class="quick-action">
                    <div class="action-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="action-text">Ver Todos os Fóruns</div>
                    <div class="action-description">Participe de discussões e compartilhe ideias</div>
                </a>
                <a href="fangames.php" class="quick-action">
                    <div class="action-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="action-text">Explorar Fangames</div>
                    <div class="action-description">Descubra e jogue fangames incríveis</div>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Detecção de recursos do navegador
        const browserInfo = {
            device: document.body.getAttribute('data-device'),
            browser: document.body.getAttribute('data-browser'),
            touch: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
            connection: navigator.connection ? navigator.connection.effectiveType : 'unknown'
        };

        console.log('Browser Info:', browserInfo);

        // Mostrar notificação do navegador
        const browserNotification = document.getElementById('browserNotification');
        const browserNotificationClose = document.getElementById('browserNotificationClose');
        
        if (browserNotification && browserNotificationClose) {
            // Mostrar notificação após 1 segundo
            setTimeout(() => {
                browserNotification.classList.add('show');
            }, 1000);
            
            // Fechar notificação ao clicar no botão
            browserNotificationClose.addEventListener('click', function() {
                browserNotification.classList.remove('show');
            });
            
            // Fechar automaticamente após 8 segundos
            setTimeout(() => {
                if (browserNotification.classList.contains('show')) {
                    browserNotification.classList.remove('show');
                }
            }, 8000);
        }

        // Mobile navigation
        <?php if ($isMobile): ?>
        const mobileNavToggle = document.getElementById('mobileNavToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (mobileNavToggle && sidebar) {
            mobileNavToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                this.innerHTML = sidebar.classList.contains('active') ? 
                    '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            });

            // Fechar sidebar ao clicar em um link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
                });
            });

            // Fechar sidebar ao clicar fora
            document.addEventListener('click', function(event) {
                if (!sidebar.contains(event.target) && !mobileNavToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
        <?php endif; ?>

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

        // Performance optimizations
        if (browserInfo.device === 'mobile') {
            // Lazy loading para imagens
            const images = document.querySelectorAll('img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        }

        // Touch device optimizations
        if (browserInfo.touch) {
            document.body.classList.add('touch-device');
            
            // Prevenir zoom em inputs (exceto quando focados)
            document.addEventListener('touchstart', function() {}, { passive: true });
        }

        // Viewport height fix para mobile
        function setVH() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', setVH);

        // Efeito de digitação no placeholder
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            const placeholders = [
                "Buscar tópicos, jogos, usuários...",
                "Encontrar discussões sobre fangames..."
            ];
            
            let currentIndex = 0;
            let currentText = '';
            let isDeleting = false;
            let typingSpeed = 100;
            
            function type() {
                const fullText = placeholders[currentIndex];
                
                if (isDeleting) {
                    currentText = fullText.substring(0, currentText.length - 1);
                } else {
                    currentText = fullText.substring(0, currentText.length + 1);
                }
                
                searchInput.placeholder = currentText;
                
                let typeSpeed = typingSpeed;
                
                if (isDeleting) {
                    typeSpeed /= 2;
                }
                
                if (!isDeleting && currentText === fullText) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && currentText === '') {
                    isDeleting = false;
                    currentIndex = (currentIndex + 1) % placeholders.length;
                    typeSpeed = 500;
                }
                
                setTimeout(type, typeSpeed);
            }
            
            // Iniciar apenas se o input não estiver focado
            searchInput.addEventListener('focus', () => {
                searchInput.placeholder = "Buscar tópicos, jogos, usuários...";
            });
            
            searchInput.addEventListener('blur', () => {
                setTimeout(type, 1000);
            });
            
            setTimeout(type, 1000);
        }
    </script>
</body>
</html>
