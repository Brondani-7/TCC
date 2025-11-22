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

// Obter tópicos recentes
$recentTopics = getRecentTopics($pdo, 10);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BONFIRE GAMES - Fórum</title>
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
        
        .new-topic-btn {
            background: var(--gamejolt-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .new-topic-btn:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        /* Categories Grid */
        .categories-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .category-card {
            background: var(--secondary);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--gamejolt-green);
        }
        
        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .category-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 5px;
        }
        
        .category-description {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .category-stats {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .category-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .section-title {
            font-size: 1.3rem;
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
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
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
        }
        
        .topic-title:hover {
            color: var(--gamejolt-green);
        }
        
        .topic-meta {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            gap: 15px;
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
            .category-header, .topic-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .category-stats, .topic-stats {
                width: 100%;
                justify-content: space-between;
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
                <a href="forum.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
                <?php if ($user): ?>
                <a href="perfil.php" class="nav-link">
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
                <div class="page-title">Fóruns da Comunidade</div>
                <div class="header-actions">
                    <?php if (!$user): ?>
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
</body>
</html>