<?php
require_once 'config.php';
$user = getCurrentUser($pdo);

// Obter categorias
$categories = getForumCategories($pdo);

// Processar criação de novo tópico
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_topic']) && isLoggedIn()) {
    $categoryId = intval($_POST['category_id']);
    $title = trim($_POST['topic_title']);
    $description = trim($_POST['topic_description']);
    $content = trim($_POST['post_content']);
    
    if (!empty($title) && !empty($content)) {
        $topicId = createForumTopic($pdo, $categoryId, $title, $description, $_SESSION['customer_id']);
        
        if ($topicId) {
            createForumPost($pdo, $topicId, $content, $_SESSION['customer_id']);
            header("Location: topic.php?id=" . $topicId);
            exit;
        } else {
            $error = "Erro ao criar tópico. Tente novamente.";
        }
    } else {
        $error = "Título e conteúdo são obrigatórios.";
    }
}

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

// Log de informações do usuário (apenas para debug)
error_log("User Agent: $userAgent");
error_log("Device: " . ($isMobile ? 'Mobile' : ($isTablet ? 'Tablet' : 'Desktop')));
error_log("Browser: $browser $browserVersion");
error_log("OS: $os");

// Configurações baseadas no dispositivo
$itemsPerPage = $isMobile ? 5 : ($isTablet ? 8 : 10);
$enableAnimations = !$isMobile; // Desativar animações em mobile para performance
$enableSidebar = !$isMobile; // Sidebar sempre visível apenas em desktop

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fóruns | BONFIRE GAMES </title>
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
            <?php if ($isMobile) echo "overflow-x: hidden;"; ?>
        }
        
        .container {
            display: flex;
            min-height: 100vh;
            <?php if (!$enableSidebar) echo "flex-direction: column;"; ?>
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
        
        /* Categories Grid */
        .categories-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
            grid-template-columns: <?php 
                if ($isMobile) echo '1fr'; 
                elseif ($isTablet) echo 'repeat(auto-fit, minmax(300px, 1fr))'; 
                else echo 'repeat(auto-fit, minmax(350px, 1fr))'; 
            ?>;
        }
        
        .category-card {
            background: var(--secondary);
            border-radius: 10px;
            padding: <?php echo $isMobile ? '15px' : '20px'; ?>;
            transition: all 0.3s ease;
            border-left: 4px solid var(--gamejolt-green);
            <?php if ($enableAnimations): ?>
            transform: translateY(0);
            <?php endif; ?>
        }
        
        .category-card:hover {
            <?php if ($enableAnimations): ?>
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            <?php endif; ?>
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            <?php if ($isMobile): ?>
            flex-direction: column;
            gap: 10px;
            <?php endif; ?>
        }
        
        .category-title {
            font-size: <?php echo $isMobile ? '1.2rem' : '1.3rem'; ?>;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 5px;
        }
        
        .category-description {
            color: var(--gray);
            font-size: 0.9rem;
            <?php if ($isMobile) echo "font-size: 0.85rem;"; ?>
        }
        
        .category-stats {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--gray);
            <?php if ($isMobile): ?>
            justify-content: space-between;
            width: 100%;
            <?php endif; ?>
        }
        
        .category-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .section-title {
            font-size: <?php echo $isMobile ? '1.2rem' : '1.3rem'; ?>;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .topics-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .topic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: <?php echo $isMobile ? '12px' : '15px'; ?>;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
            <?php if ($isMobile): ?>
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
            <?php endif; ?>
        }
        
        .topic-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .topic-info {
            flex: 1;
        }
        
        .topic-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--light);
            text-decoration: none;
            display: block;
            font-size: <?php echo $isMobile ? '1.1rem' : 'inherit'; ?>;
        }
        
        .topic-title:hover {
            color: var(--gamejolt-green);
        }
        
        .topic-meta {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            gap: 15px;
            <?php if ($isMobile): ?>
            flex-wrap: wrap;
            gap: 8px;
            <?php endif; ?>
        }
        
        .topic-stats {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--gray);
            <?php if ($isMobile): ?>
            width: 100%;
            justify-content: space-around;
            <?php endif; ?>
        }
        
        .topic-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        /* Sticky topics */
        .topic-sticky {
            border-left: 4px solid var(--warning);
        }
        
        .sticky-badge {
            background: var(--warning);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            margin-right: 8px;
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
        
        /* Browser-specific optimizations */
        <?php if ($browser === 'Chrome'): ?>
        .category-card, .topic-item {
            backdrop-filter: blur(10px);
        }
        <?php endif; ?>
        
        <?php if ($browser === 'Firefox'): ?>
        .category-card, .topic-item {
            background: var(--dark);
        }
        <?php endif; ?>
        
        <?php if ($browser === 'Safari'): ?>
        .category-card, .topic-item {
            -webkit-backdrop-filter: blur(10px);
        }
        <?php endif; ?>
        
        /* Performance optimizations for mobile */
        <?php if ($isMobile): ?>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        .nav-link, .new-topic-btn, .category-card {
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
            
            .category-header, .topic-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .category-stats, .topic-stats {
                width: 100%;
                justify-content: space-between;
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
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Início</span>
                </a>
                <a href="fangames.php" class="nav-link">
                    <i class="fas fa-gamepad"></i>
                    <span>Fangames</span>
                </a>
                <a href="forum.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Fóruns da Comunidade</div>
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
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Device Info (Debug - pode remover em produção) -->
            <div style="display: none;" class="device-info">
                Dispositivo: <?php echo $isMobile ? 'Mobile' : ($isTablet ? 'Tablet' : 'Desktop'); ?> | 
                Navegador: <?php echo $browser . ' ' . $browserVersion; ?> | 
                SO: <?php echo $os; ?>
            </div>
            
            <!-- Categories -->
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-header">
                        <div>
                            <h3 class="category-title">
                                <a href="category.php?id=<?= $category['CategoryID'] ?>" style="color: inherit; text-decoration: none;">
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </a>
                            </h3>
                            <p class="category-description"><?= htmlspecialchars($category['CategoryDescription']) ?></p>
                        </div>
                        <div class="category-stats">
                            <div class="category-stat">
                                <i class="fas fa-comments"></i>
                                <span><?= $category['topic_count'] ?> tópicos</span>
                            </div>
                            <div class="category-stat">
                                <i class="fas fa-message"></i>
                                <span><?= $category['post_count'] ?> posts</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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

        // Otimizações baseadas no navegador
        if (browserInfo.browser === 'safari') {
            // Safari-specific optimizations
            document.documentElement.style.setProperty('--smooth-scroll', 'auto');
        }

        if (browserInfo.connection === 'slow-2g' || browserInfo.connection === '2g') {
            // Desativar animações para conexões lentas
            document.documentElement.style.setProperty('--animation-duration', '0s');
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

        // Browser-specific feature detection
        function supportsWebP() {
            const canvas = document.createElement('canvas');
            if (canvas.getContext && canvas.getContext('2d')) {
                return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
            }
            return false;
        }

        // Aplicar WebP se suportado
        if (supportsWebP()) {
            document.body.classList.add('webp-supported');
        }

        // Touch device optimizations
        if (browserInfo.touch) {
            document.body.classList.add('touch-device');
            
            // Prevenir zoom em inputs (exceto quando focados)
            document.addEventListener('touchstart', function() {}, { passive: true });
        }

        // Network-aware loading
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', function() {
                const connection = navigator.connection;
                if (connection.saveData === true || connection.effectiveType.includes('2g')) {
                    // Data saver mode - reduzir qualidade de imagens, desativar vídeos, etc.
                    document.body.classList.add('data-saver');
                }
            });
        }

        // Viewport height fix para mobile
        function setVH() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', setVH);
    </script>
</body>
</html>
