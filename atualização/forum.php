<?php
require_once 'config.php';
$user = getCurrentUser($pdo);

// Buscar posts do fórum
$stmt = $pdo->query("
    SELECT p.*, u.CustomerName, u.CustomerHandle 
    FROM posts p 
    LEFT JOIN usuarios u ON p.CustomerID = u.CustomerID 
    ORDER BY p.Data DESC 
    LIMIT 10
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar novo post
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postContent']) && isLoggedIn()) {
    $postContent = htmlspecialchars($_POST['postContent']);
    
    $stmt = $pdo->prepare("INSERT INTO posts (CustomerID, PostMessage) VALUES (?, ?)");
    $stmt->execute([$_SESSION['customer_id'], $postContent]);
    
    header("Location: forum.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BONFIRE Community</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* (Manter todo o CSS original do forumatt.html) */
        <?php include 'styles/forum.css'; ?>
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-gamepad"></i>
                <span>BONFIRE GAMES</span>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Início</span>
                </a>
                <a href="forum.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Comunidade</span>
                </a>
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notificações</span>
                </a>
                <?php if (isLoggedIn()): ?>
                <a href="perfil.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Bonfirers Community</div>
                <div class="header-actions">
                    <div class="header-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="header-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="forum-content">
                <?php if (isLoggedIn()): ?>
                <div class="create-post">
                    <div class="post-input">
                        <div class="post-avatar">
                            <?php if ($user['ProfilePhoto']): ?>
                            <img src="<?= htmlspecialchars($user['ProfilePhoto']) ?>" alt="Avatar">
                            <?php else: ?>
                            <span><?= htmlspecialchars($user['ProfileIcon'] ?? '👤') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="post-editor">
                            <form method="POST">
                                <textarea name="postContent" placeholder="O que está acontecendo no mundo dos jogos?" required></textarea>
                                <div class="post-actions">
                                    <div class="post-tools">
                                        <div class="post-tool">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <div class="post-tool">
                                            <i class="fas fa-smile"></i>
                                        </div>
                                        <div class="post-tool">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </div>
                                    <button type="submit" class="post-button">Publicar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="forum-posts">
                    <?php foreach ($posts as $post): ?>
                    <div class="forum-post">
                        <div class="post-header">
                            <div class="post-avatar">
                                <?php if ($user && $user['ProfilePhoto']): ?>
                                <img src="<?= htmlspecialchars($user['ProfilePhoto']) ?>" alt="Avatar">
                                <?php else: ?>
                                <span><?= htmlspecialchars($user['ProfileIcon'] ?? '👤') ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="post-user-info">
                                <div class="post-user">
                                    <?= htmlspecialchars($post['CustomerName']) ?>
                                    <?php if ($post['CustomerID'] == 1): // Exemplo de MOD ?>
                                    <span class="post-user-badge">MOD</span>
                                    <?php endif; ?>
                                </div>
                                <div class="post-meta">
                                    <span>@<?= htmlspecialchars($post['CustomerHandle']) ?></span>
                                    <i class="fas fa-circle"></i>
                                    <span><?= date('d/m/Y H:i', strtotime($post['Data'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <p><?= nl2br(htmlspecialchars($post['PostMessage'])) ?></p>
                        </div>
                        
                        <div class="post-stats">
                            <div class="post-stat">
                                <i class="fas fa-heart"></i>
                                <span><?= $post['Likes'] ?></span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-retweet"></i>
                                <span><?= $post['Retweets'] ?></span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-comment"></i>
                                <span><?= $post['Comments'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="trending-topics">
                <div class="trending-title">
                    <i class="fas fa-hashtag"></i>
                    <span>Franquias Populares</span>
                </div>
                <?php
                $stmt = $pdo->query("
                    SELECT Franchise, COUNT(*) as count 
                    FROM fangames 
                    GROUP BY Franchise 
                    ORDER BY count DESC 
                    LIMIT 5
                ");
                $franchises = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($franchises as $franchise): ?>
                <div class="trending-item">
                    <div class="trending-name"><?= htmlspecialchars($franchise['Franchise']) ?></div>
                    <div class="trending-stats"><?= $franchise['count'] ?> fangames</div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="online-users">
                <div class="online-title">
                    <i class="fas fa-circle"></i>
                    <span>Bonfirers Online</span>
                </div>
                <?php
                // Usuários recentemente ativos (exemplo)
                $stmt = $pdo->query("
                    SELECT CustomerName, CustomerHandle 
                    FROM usuarios 
                    ORDER BY CreatedAt DESC 
                    LIMIT 5
                ");
                $onlineUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($onlineUsers as $user): ?>
                <div class="online-user">
                    <div class="online-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="online-info">
                        <div class="online-name"><?= htmlspecialchars($user['CustomerName']) ?></div>
                        <div class="online-handle">@<?= htmlspecialchars($user['CustomerHandle']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>