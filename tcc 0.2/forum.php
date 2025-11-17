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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BONFIRE GAMES - Fórum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff4655;
            --secondary-color: #0f1923;
            --accent-color: #1a2b3c;
            --text-color: #ece8e1;
            --highlight-color: #ff4655;
            --gray-color: #768079;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--accent-color);
            padding: 20px 0;
            border-bottom: 3px solid var(--highlight-color);
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            color: var(--highlight-color);
        }
        
        nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
        }
        
        nav ul li {
            margin: 0 15px;
        }
        
        nav ul li a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        nav ul li a:hover, nav ul li a.active {
            background-color: var(--highlight-color);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-top: 30px;
        }
        
        .forum-section {
            background-color: var(--accent-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .forum-section h2 {
            color: var(--highlight-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        .forum-category {
            margin-bottom: 20px;
        }
        
        .forum-category h3 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .forum-thread {
            background-color: var(--secondary-color);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            transition: transform 0.2s;
        }
        
        .forum-thread:hover {
            transform: translateY(-3px);
        }
        
        .thread-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .thread-meta {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .sidebar {
            background-color: var(--accent-color);
            border-radius: 8px;
            padding: 20px;
        }
        
        .sidebar-section {
            margin-bottom: 25px;
        }
        
        .sidebar-section h3 {
            color: var(--highlight-color);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .status-label {
            font-weight: bold;
            color: var(--text-color);
        }
        
        .status-item span:last-child {
            color: var(--gray-color);
        }
        
        .game-info {
            background-color: var(--secondary-color);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .game-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--highlight-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--highlight-color);
            color: white;
            flex: 2;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--text-color);
            flex: 1;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .screenshot-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .screenshot {
            background-color: var(--secondary-color);
            height: 100px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: var(--gray-color);
            transition: all 0.3s;
        }
        
        .screenshot:hover {
            background-color: var(--highlight-color);
            color: white;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid var(--accent-color);
            color: var(--gray-color);
            font-size: 0.9rem;
        }
        
        /* Estilos para posts do fórum */
        .create-post {
            background-color: var(--accent-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .create-post h2 {
            color: var(--highlight-color);
            margin-bottom: 15px;
        }
        
        .post-input {
            display: flex;
            gap: 15px;
        }
        
        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            color: var(--text-color);
        }
        
        .post-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .post-editor {
            flex: 1;
        }
        
        .post-editor textarea {
            width: 100%;
            background-color: var(--secondary-color);
            border: none;
            border-radius: 6px;
            padding: 15px;
            color: var(--text-color);
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .post-editor textarea:focus {
            outline: 2px solid var(--highlight-color);
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .post-tools {
            display: flex;
            gap: 15px;
        }
        
        .post-tool {
            color: var(--highlight-color);
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s;
        }
        
        .post-tool:hover {
            color: var(--text-color);
        }
        
        .post-button {
            background-color: var(--highlight-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .post-button:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .forum-posts {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .forum-post {
            background-color: var(--accent-color);
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.2s;
        }
        
        .forum-post:hover {
            transform: translateY(-3px);
        }
        
        .post-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .post-user-info {
            flex: 1;
        }
        
        .post-user {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
        }
        
        .post-user-badge {
            background-color: var(--highlight-color);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .post-meta {
            font-size: 0.85rem;
            color: var(--gray-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .post-meta i {
            font-size: 0.4rem;
        }
        
        .post-content {
            margin-bottom: 15px;
            color: var(--text-color);
            line-height: 1.5;
        }
        
        .post-stats {
            display: flex;
            gap: 20px;
        }
        
        .post-stat {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray-color);
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .post-stat:hover {
            color: var(--highlight-color);
        }
        
        .trending-topics, .online-users {
            background-color: var(--accent-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .trending-title, .online-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--highlight-color);
        }
        
        .trending-item, .online-user {
            padding: 10px 0;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        .trending-item:last-child, .online-user:last-child {
            border-bottom: none;
        }
        
        .trending-name {
            font-weight: bold;
            color: var(--text-color);
        }
        
        .trending-stats {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .online-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .online-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
        }
        
        .online-info {
            flex: 1;
        }
        
        .online-name {
            font-weight: bold;
            color: var(--text-color);
        }
        
        .online-handle {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            nav ul li {
                margin: 5px 0;
            }
            
            .post-input {
                flex-direction: column;
            }
            
            .post-avatar {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">BONFIRE GAMES</div>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="forum.php" class="active">Fórum</a></li>
                    <li><a href="perfil.php">Perfil</a></li>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <div class="forum-content">
                <?php if (isLoggedIn()): ?>
                <div class="create-post">
                    <h2>Criar Nova Postagem</h2>
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
                
                <div class="forum-section">
                    <h2>Últimas Discussões</h2>
                    
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
                                    <span><?= $post['Likes'] ?? 0 ?></span>
                                </div>
                                <div class="post-stat">
                                    <i class="fas fa-retweet"></i>
                                    <span><?= $post['Retweets'] ?? 0 ?></span>
                                </div>
                                <div class="post-stat">
                                    <i class="fas fa-comment"></i>
                                    <span><?= $post['Comments'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="sidebar-section">
                    <h3>Status</h3>
                    <div class="game-info">
                        <div class="game-title">Sonic Adventure 3</div>
                        <div class="status-item">
                            <span class="status-label">Franquia:</span>
                            <span>Sonic</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Gênero:</span>
                            <span>Terror</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Publicado em:</span>
                            <span>12/11/2025 às 20:15</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Avaliação:</span>
                            <span>0.0</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary">DOWNLOAD</button>
                        <button class="btn btn-secondary">AVALIAÇÃO</button>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3>Screenshots</h3>
                    <div class="screenshot-gallery">
                        <div class="screenshot">AddiSmart Screenshot</div>
                        <div class="screenshot">Escape/Fabrics</div>
                        <div class="screenshot">Neuhum</div>
                        <div class="screenshot">+ Adicionar</div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3>Franquias Populares</h3>
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
                
                <div class="sidebar-section">
                    <h3>Bonfirers Online</h3>
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
    </div>
    
    <footer>
        <div class="container">
            <p>Bonfire Games &copy; 2025 - Todos os direitos reservados</p>
        </div>
    </footer>
</body>
</html>
