<?php
require_once 'config.php';

$topicId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = getCurrentUser($pdo);

// Obter informações do tópico
$topic = getTopicInfo($pdo, $topicId);

if (!$topic) {
    header('Location: forum.php');
    exit;
}

// Incrementar visualizações
incrementTopicViews($pdo, $topicId);

// Obter posts do tópico
$posts = getTopicPosts($pdo, $topicId);

// Processar ações via AJAX - CORRIGIDO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && isLoggedIn()) {
        $response = ['success' => false];
        
        switch ($_POST['action']) {
            case 'like':
                if (isset($_POST['post_id'])) {
                    $result = togglePostLike($pdo, $_POST['post_id'], $_SESSION['customer_id']);
                    $response = $result;
                }
                break;
                
            case 'edit':
                if (isset($_POST['post_id']) && isset($_POST['content'])) {
                    $result = editForumPost($pdo, $_POST['post_id'], $_POST['content'], $_SESSION['customer_id']);
                    $response = $result;
                } else {
                    $response = ['success' => false, 'error' => 'Dados incompletos para edição'];
                }
                break;
                
            case 'delete':
                if (isset($_POST['post_id'])) {
                    $result = deleteForumPost($pdo, $_POST['post_id'], $_SESSION['customer_id']);
                    $response = ['success' => $result];
                    if ($result) {
                        $response['redirect'] = "topic.php?id=$topicId";
                    }
                }
                break;
                
            default:
                $response = ['success' => false, 'error' => 'Ação desconhecida'];
        }
        
        echo json_encode($response);
        exit;
    }
}

// Processar nova resposta (formulário normal)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_reply']) && isLoggedIn()) {
    $content = trim($_POST['post_content']);
    
    if (!empty($content)) {
        if (createForumPost($pdo, $topicId, $content, $_SESSION['customer_id'])) {
            header("Location: topic.php?id=" . $topicId);
            exit;
        } else {
            $error = "Erro ao enviar resposta. Tente novamente.";
        }
    } else {
        $error = "O conteúdo da resposta não pode estar vazio.";
    }
}

// Obter informações de likes para cada post
if ($user && !empty($posts)) {
    foreach ($posts as &$post) {
        $post['HasLiked'] = hasUserLikedPost($pdo, $post['PostID'], $user['CustomerID']);
        // Garantir que LikesCount existe
        if (!isset($post['LikesCount'])) {
            $post['LikesCount'] = 0;
        }
    }
    unset($post); // Quebrar referência
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($topic['TopicTitle']) ?> | BONFIRE GAMES</title>
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
        }
        
        /* Adicione ao CSS existente */
        .edited-indicator {
            color: var(--gray);
            font-size: 0.8rem;
        }

        .edit-form {
            transition: all 0.3s ease;
        }

        .save-btn.success {
            background: var(--success) !important;
        }

        /* Loading state para botões */
        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '...';
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--secondary);
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
            background-color: var(--dark);
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
        
        .breadcrumb {
            margin-bottom: 20px;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: var(--gamejolt-green);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        /* Topic Header */
        .topic-header {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--gamejolt-green);
        }
        
        .topic-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--light);
            line-height: 1.3;
        }
        
        .topic-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        .topic-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .topic-description {
            color: var(--gray);
            font-size: 1rem;
            margin-top: 10px;
            line-height: 1.5;
        }
        
        /* Posts */
        .posts-container {
            margin-bottom: 30px;
        }
        
        .post-card {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .post-author {
            width: 180px;
            flex-shrink: 0;
            text-align: center;
        }
        
        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 15px auto;
            background-size: cover;
            background-position: center;
            border: 3px solid var(--gamejolt-green);
        }
        
        .author-name {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--light);
            font-size: 1.1rem;
        }
        
        .author-handle {
            color: var(--gamejolt-green);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .author-stats {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .post-content {
            flex: 1;
            min-width: 0;
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .post-meta {
            color: var(--gray);
            font-size: 0.9rem;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .post-number {
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .post-text {
            line-height: 1.7;
            font-size: 1rem;
            color: var(--light);
            margin-bottom: 20px;
            white-space: pre-wrap;
        }
        
        .post-text p {
            margin-bottom: 15px;
        }
        
        .post-text p:last-child {
            margin-bottom: 0;
        }
        
        .post-actions {
            display: flex;
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .post-action {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray);
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            background: none;
        }
        
        .post-action:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
        }
        
        .like-action.liked {
            color: var(--primary);
        }
        
        .like-action.liked:hover {
            background: rgba(255, 70, 85, 0.2);
        }
        
        .reply-action {
            background: rgba(107, 198, 121, 0.2);
            color: var(--gamejolt-green);
            border: 1px solid var(--gamejolt-green);
        }
        
        .reply-action:hover {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .edit-action {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        
        .edit-action:hover {
            background: var(--warning);
            color: white;
        }
        
        .delete-action {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .delete-action:hover {
            background: var(--danger);
            color: white;
        }
        
        /* Edit Form */
        .edit-form {
            display: none;
            margin-top: 15px;
        }
        
        .edit-form textarea {
            width: 100%;
            min-height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 15px;
            color: var(--light);
            resize: vertical;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 10px;
            outline: none;
        }
        
        .edit-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .cancel-btn {
            background: transparent;
            color: var(--gray);
            border: 1px solid var(--gray);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .save-btn {
            background: var(--gamejolt-green);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* Reply Form */
        .reply-section {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .reply-section h3 {
            margin-bottom: 20px;
            color: var(--gamejolt-green);
            font-size: 1.3rem;
        }
        
        .reply-form textarea {
            width: 100%;
            min-height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 15px;
            color: var(--light);
            resize: vertical;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 15px;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .reply-form textarea:focus {
            border-color: var(--gamejolt-green);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .reply-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .reply-btn {
            background: var(--gamejolt-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .reply-btn:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            border-left: 4px solid;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border-color: var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background-color: rgba(107, 198, 121, 0.2);
            border-color: var(--gamejolt-green);
            color: var(--gamejolt-green);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--light);
        }
        
        /* Badges */
        .sticky-badge {
            background: var(--warning);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .locked-badge {
            background: var(--danger);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 10px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--dark);
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .modal-cancel {
            background: transparent;
            color: var(--gray);
            border: 1px solid var(--gray);
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .modal-confirm {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* Responsive */
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
            .post-card {
                flex-direction: column;
                text-align: center;
            }
            
            .post-author {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .author-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .post-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .post-meta {
                justify-content: center;
            }
            
            .topic-meta {
                flex-direction: column;
                gap: 10px;
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
            
            .post-actions {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .topic-title {
                font-size: 1.4rem;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .modal-actions {
                flex-direction: column;
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
                <?php if (isLoggedIn()): ?>
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
                <div class="page-title">Tópico</div>
                <div class="header-actions">
                    <a href="category.php?id=<?= $topic['CategoryID'] ?>" class="new-topic-btn">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
            
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="forum.php">Fórum</a> &gt; 
                <a href="category.php?id=<?= $topic['CategoryID'] ?>"><?= htmlspecialchars($topic['CategoryName']) ?></a> &gt; 
                <span><?= htmlspecialchars($topic['TopicTitle']) ?></span>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Tópico Header -->
            <div class="topic-header">
                <h1 class="topic-title">
                    <?php if ($topic['IsSticky']): ?>
                    <span class="sticky-badge"><i class="fas fa-thumbtack"></i> Fixado</span>
                    <?php endif; ?>
                    <?php if ($topic['IsLocked']): ?>
                    <span class="locked-badge"><i class="fas fa-lock"></i> Trancado</span>
                    <?php endif; ?>
                    <?= htmlspecialchars($topic['TopicTitle']) ?>
                </h1>
                
                <?php if (!empty($topic['TopicDescription'])): ?>
                <p class="topic-description"><?= htmlspecialchars($topic['TopicDescription']) ?></p>
                <?php endif; ?>
                
                <div class="topic-meta">
                    <div class="topic-meta-item">
                        <i class="fas fa-user"></i>
                        <span>por <?= htmlspecialchars($topic['CustomerName']) ?></span>
                    </div>
                    <div class="topic-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?= date('d/m/Y H:i', strtotime($topic['CreatedAt'])) ?></span>
                    </div>
                    <div class="topic-meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?= $topic['ViewCount'] ?> visualizações</span>
                    </div>
                    <div class="topic-meta-item">
                        <i class="fas fa-comments"></i>
                        <span><?= $topic['ReplyCount'] ?> respostas</span>
                    </div>
                </div>
            </div>
            
            <!-- Posts -->
            <div class="posts-container">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $index => $post): ?>
                    <div class="post-card" id="post-<?= $post['PostID'] ?>">
                        <div class="post-author">
                            <div class="author-avatar" style="background-image: url('<?= !empty($post['ProfilePhoto']) ? htmlspecialchars($post['ProfilePhoto']) : '' ?>'); font-size: <?= empty($post['ProfilePhoto']) ? '2rem' : '0' ?>">
                                <?= empty($post['ProfilePhoto']) ? ($post['ProfileIcon'] ?? '👤') : '' ?>
                            </div>
                            <div class="author-name"><?= htmlspecialchars($post['CustomerName']) ?></div>
                            <div class="author-handle">@<?= htmlspecialchars($post['CustomerHandle']) ?></div>
                            <div class="author-stats">
                                Membro desde <?= date('m/Y', strtotime($post['JoinDate'])) ?>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <div class="post-header">
                                <div class="post-meta">
                                    <span class="post-number">#<?= $index + 1 ?></span>
                                    <span>Postado em <?= date('d/m/Y H:i', strtotime($post['CreatedAt'])) ?></span>
                                    <?php if ($post['IsEdited']): ?>
                                    <span><i class="fas fa-edit"></i> Editado em <?= date('d/m/Y H:i', strtotime($post['EditedAt'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="post-text" id="post-text-<?= $post['PostID'] ?>">
                                <?= nl2br(htmlspecialchars($post['PostContent'])) ?>
                            </div>
                            
                            <!-- Edit Form (hidden by default) -->
                            <div class="edit-form" id="edit-form-<?= $post['PostID'] ?>">
                                <textarea id="edit-textarea-<?= $post['PostID'] ?>"><?= htmlspecialchars($post['PostContent']) ?></textarea>
                                <div class="edit-actions">
                                    <button type="button" class="cancel-btn" onclick="cancelEdit(<?= $post['PostID'] ?>)">Cancelar</button>
                                    <button type="button" class="save-btn" onclick="saveEdit(<?= $post['PostID'] ?>)">Salvar</button>
                                </div>
                            </div>
                            
                            <div class="post-actions">
                                <button class="post-action like-action <?= $post['HasLiked'] ? 'liked' : '' ?>" 
                                        onclick="toggleLike(<?= $post['PostID'] ?>, this)">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="like-count"><?= $post['LikesCount'] ?? 0 ?></span>
                                </button>
                                
                                <button class="post-action reply-action" onclick="replyToComment(<?= $post['PostID'] ?>, '<?= htmlspecialchars($post['CustomerName']) ?>')">
                                    <i class="fas fa-reply"></i>
                                    <span>Responder</span>
                                </button>
                                
                                <button class="post-action" onclick="copyLink('post-<?= $post['PostID'] ?>', this)">
                                    <i class="fas fa-link"></i>
                                    <span>Copiar Link</span>
                                </button>
                                
                                <?php if ($user && ($user['CustomerID'] == $post['CustomerID'] || $user['CustomerID'] == 1)): ?>
                                <button class="post-action edit-action" onclick="startEdit(<?= $post['PostID'] ?>)">
                                    <i class="fas fa-edit"></i>
                                    <span>Editar</span>
                                </button>
                                
                                <button class="post-action delete-action" onclick="confirmDelete(<?= $post['PostID'] ?>)">
                                    <i class="fas fa-trash"></i>
                                    <span>Deletar</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Nenhuma resposta ainda</h3>
                        <p>Seja o primeiro a responder a este tópico!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulário de Resposta -->
            <?php if (isLoggedIn() && !$topic['IsLocked']): ?>
            <div class="reply-section">
                <h3><i class="fas fa-reply"></i> Responder ao Tópico</h3>
                <form method="POST" class="reply-form">
                    <input type="hidden" name="create_reply" value="1">
                    <textarea name="post_content" placeholder="Digite sua resposta aqui... Você pode mencionar outros usuários usando @nome." required></textarea>
                    <div class="reply-actions">
                        <button type="submit" class="reply-btn">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Resposta
                        </button>
                    </div>
                </form>
            </div>
            <?php elseif ($topic['IsLocked']): ?>
            <div class="alert alert-error">
                <i class="fas fa-lock"></i>
                Este tópico está trancado. Não é possível enviar novas respostas.
            </div>
            <?php else: ?>
            <div class="reply-section" style="text-align: center;">
                <h3>Faça login para responder</h3>
                <p style="color: var(--gray); margin-bottom: 20px;">Você precisa estar logado para responder a este tópico.</p>
                <a href="login.php" class="new-topic-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Fazer Login
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Confirmação de Delete -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 15px; color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <p style="margin-bottom: 20px; color: var(--light);">Tem certeza que deseja deletar este post? Esta ação não pode ser desfeita.</p>
            <div class="modal-actions">
                <button class="modal-cancel" onclick="closeDeleteModal()">Cancelar</button>
                <button class="modal-confirm" id="confirmDeleteBtn">Deletar</button>
            </div>
        </div>
    </div>

    <script>
        let postToDelete = null;

        // Função para curtir/descurtir
        async function toggleLike(postId, button) {
            if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
                alert('Faça login para curtir posts.');
                return;
            }

            const likeCount = button.querySelector('.like-count');
            const icon = button.querySelector('i');
            
            try {
                const response = await fetch('topic.php?id=<?= $topicId ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=like&post_id=${postId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Atualizar contador
                    likeCount.textContent = result.likesCount;
                    
                    // Atualizar estilo
                    if (result.action === 'like') {
                        button.classList.add('liked');
                        icon.style.transform = 'scale(1.2)';
                    } else {
                        button.classList.remove('liked');
                        icon.style.transform = 'scale(1)';
                    }
                    
                    // Efeito visual
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 300);
                    
                } else {
                    alert('Erro ao curtir: ' + result.error);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao curtir o post.');
            }
        }

        // Função para iniciar edição - MELHORADA
        function startEdit(postId) {
            const postText = document.getElementById(`post-text-${postId}`);
            const editForm = document.getElementById(`edit-form-${postId}`);
            const editTextarea = document.getElementById(`edit-textarea-${postId}`);
            
            // Esconder texto, mostrar formulário
            postText.style.display = 'none';
            editForm.style.display = 'block';
            
            // Preencher textarea com o conteúdo atual (sem tags HTML)
            const currentContent = postText.textContent || postText.innerText;
            editTextarea.value = currentContent;
            
            // Focar e selecionar todo o texto
            editTextarea.focus();
            editTextarea.setSelectionRange(0, editTextarea.value.length);
            
            // Auto-expand textarea
            editTextarea.style.height = 'auto';
            editTextarea.style.height = (editTextarea.scrollHeight) + 'px';
        }

        // Função para cancelar edição - CORRIGIDA
        function cancelEdit(postId) {
            const postText = document.getElementById(`post-text-${postId}`);
            const editForm = document.getElementById(`edit-form-${postId}`);
            
            postText.style.display = 'block';
            editForm.style.display = 'none';
            
            // Restaurar conteúdo original se necessário
            const originalContent = postText.innerHTML;
            const editTextarea = document.getElementById(`edit-textarea-${postId}`);
            editTextarea.value = originalContent.replace(/<br\s*\/?>/gi, '\n');
        }

        
        // Função para salvar edição - CORRIGIDA
        async function saveEdit(postId) {
            const editTextarea = document.getElementById(`edit-textarea-${postId}`);
            const postText = document.getElementById(`post-text-${postId}`);
            const newContent = editTextarea.value.trim();
            
            if (!newContent) {
                alert('O conteúdo não pode estar vazio.');
                return;
            }
            
            try {
                const response = await fetch('topic.php?id=<?= $topicId ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=edit&post_id=${postId}&content=${encodeURIComponent(newContent)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Atualizar conteúdo
                    postText.innerHTML = newContent.replace(/\n/g, '<br>');
                    
                    // Esconder formulário, mostrar texto
                    postText.style.display = 'block';
                    document.getElementById(`edit-form-${postId}`).style.display = 'none';
                    
                    // Adicionar/atualizar indicador de edição
                    const postMeta = postText.closest('.post-content').querySelector('.post-meta');
                    let editedIndicator = postMeta.querySelector('.edited-indicator');
                    
                    if (!editedIndicator) {
                        editedIndicator = document.createElement('span');
                        editedIndicator.className = 'edited-indicator';
                        editedIndicator.innerHTML = ' <i class="fas fa-edit"></i> Editado agora';
                        postMeta.appendChild(editedIndicator);
                    } else {
                        editedIndicator.innerHTML = ' <i class="fas fa-edit"></i> Editado agora';
                    }
                    
                    // Feedback visual de sucesso
                    const saveBtn = event.target;
                    const originalText = saveBtn.textContent;
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Salvo!';
                    saveBtn.style.background = 'var(--success)';
                    
                    setTimeout(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.style.background = '';
                    }, 2000);
                    
                } else {
                    alert('Erro ao editar o post: ' + (result.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro de conexão ao editar o post.');
            }
        }

        // Função para confirmar deleção
        function confirmDelete(postId) {
            postToDelete = postId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        // Função para fechar modal de delete
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            postToDelete = null;
        }

        // Função para executar deleção
        async function executeDelete() {
            if (!postToDelete) return;
            
            try {
                const response = await fetch('topic.php?id=<?= $topicId ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&post_id=${postToDelete}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        // Recarregar a página para refletir as mudanças
                        window.location.reload();
                    }
                } else {
                    alert('Erro ao deletar o post.');
                    closeDeleteModal();
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao deletar o post.');
                closeDeleteModal();
            }
        }

        // Função para responder a comentário
        function replyToComment(postId, authorName) {
            const textarea = document.querySelector('textarea[name="post_content"]');
            if (textarea) {
                // Adiciona menção ao autor do comentário
                textarea.value += `@${authorName} `;
                textarea.focus();
                textarea.scrollIntoView({ behavior: 'smooth' });
                
                // Destaca o textarea temporariamente
                textarea.style.borderColor = 'var(--gamejolt-green)';
                textarea.style.boxShadow = '0 0 0 2px rgba(107, 198, 121, 0.3)';
                
                setTimeout(() => {
                    textarea.style.borderColor = '';
                    textarea.style.boxShadow = '';
                }, 2000);
            }
        }

        // Função para copiar link
        function copyLink(postElementId, button) {
            const url = window.location.href + '#' + postElementId;
            navigator.clipboard.writeText(url).then(() => {
                // Feedback visual
                const span = button.querySelector('span');
                const originalText = span.textContent;
                span.textContent = 'Link Copiado!';
                button.style.background = 'var(--gamejolt-green)';
                button.style.color = 'white';
                
                setTimeout(() => {
                    span.textContent = originalText;
                    button.style.background = '';
                    button.style.color = '';
                }, 2000);
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-expand textarea
            const textarea = document.querySelector('textarea[name="post_content"]');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
            
            // Configurar botão de confirmação de delete
            document.getElementById('confirmDeleteBtn').addEventListener('click', executeDelete);
            
            // Fechar modal ao clicar fora
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
            
            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDeleteModal();
                }
            });
        });

        // Scroll para âncora se houver na URL
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    setTimeout(() => {
                        element.scrollIntoView({ behavior: 'smooth' });
                        
                        // Destaca o post temporariamente
                        element.style.boxShadow = '0 0 0 3px var(--gamejolt-green)';
                        setTimeout(() => {
                            element.style.boxShadow = '';
                        }, 3000);
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>