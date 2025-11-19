<?php
require_once 'config.php';

$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obter informações da categoria
$stmt = $pdo->prepare("SELECT * FROM forum_categories WHERE CategoryID = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: forum.php');
    exit;
}

// Paginação
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Obter tópicos da categoria
$topics = getCategoryTopics($pdo, $categoryId, $limit, $offset);

// Contar total de tópicos
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM forum_topics WHERE CategoryID = ?");
$stmt->execute([$categoryId]);
$totalTopics = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalTopics / $limit);


require_once 'config.php';

$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// DEBUG
error_log("=== CATEGORY DEBUG ===");
error_log("Category ID recebido: " . $categoryId);

// Obter informações da categoria
$stmt = $pdo->prepare("SELECT * FROM forum_categories WHERE CategoryID = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

error_log("Categoria encontrada: " . print_r($category, true));

if (!$category) {
    error_log("Categoria não encontrada, redirecionando...");
    header('Location: forum.php');
    exit;
}

// Buscar tópicos - VERSÃO CORRIGIDA
try {
    $sql = "
        SELECT 
            ft.*,
            u.CustomerName,
            u.CustomerHandle,
            u.ProfilePhoto,
            (SELECT COUNT(*) FROM forum_posts WHERE TopicID = ft.TopicID) as ReplyCount,
            (SELECT CustomerName FROM usuarios WHERE CustomerID = ft.LastPostBy) as LastPosterName
        FROM forum_topics ft
        LEFT JOIN usuarios u ON ft.CustomerID = u.CustomerID
        WHERE ft.CategoryID = ?
        ORDER BY ft.IsSticky DESC, ft.CreatedAt DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$categoryId]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Tópicos encontrados para categoria $categoryId: " . count($topics));
    
} catch (Exception $e) {
    error_log("ERRO: " . $e->getMessage());
    $topics = [];
}

// Resto do código...
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['CategoryName']) ?> | BONFIRE GAMES</title>
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
        }
        
        .breadcrumb a {
            color: var(--gamejolt-green);
            text-decoration: none;
        }
        
        .topics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: var(--dark);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .topics-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .topics-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .topics-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .topic-title-cell {
            width: 50%;
        }
        
        .topic-stats-cell {
            width: 15%;
            text-align: center;
        }
        
        .topic-lastpost-cell {
            width: 35%;
        }
        
        .topic-title {
            font-weight: bold;
            color: var(--light);
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
        }
        
        .topic-title:hover {
            color: var(--gamejolt-green);
        }
        
        .topic-meta {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .sticky-badge {
            background: var(--warning);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            margin-right: 8px;
        }
        
        .locked-badge {
            background: var(--danger);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            margin-right: 8px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .page-link {
            padding: 8px 12px;
            background: var(--dark);
            color: var(--light);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .page-link:hover, .page-link.active {
            background: var(--primary);
        }
        
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
            .topics-table {
                display: block;
                overflow-x: auto;
            }
            
            .topics-table th,
            .topics-table td {
                white-space: nowrap;
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
                <div>
                    <div class="breadcrumb">
                        <a href="forum.php">Fórum</a> &gt; 
                        <span><?= htmlspecialchars($category['CategoryName']) ?></span>
                    </div>
                    <div class="page-title"><?= htmlspecialchars($category['CategoryName']) ?></div>
                    <p style="color: var(--gray); margin-top: 5px;"><?= htmlspecialchars($category['CategoryDescription']) ?></p>
                </div>
                <div class="header-actions">
                    <a href="forum.php" class="new-topic-btn">
                        <i class="fas fa-arrow-left"></i>
                        Voltar ao Fórum
                    </a>
                    <?php if (isLoggedIn()): ?>
                    <button class="new-topic-btn" onclick="openCreateTopicModal()">
                        <i class="fas fa-plus"></i>
                        Novo Tópico
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tópicos -->
            <?php if (!empty($topics)): ?>
            <table class="topics-table">
                <thead>
                    <tr>
                        <th class="topic-title-cell">Tópico</th>
                        <th class="topic-stats-cell">Respostas</th>
                        <th class="topic-stats-cell">Visualizações</th>
                        <th class="topic-lastpost-cell">Última Mensagem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topics as $topic): ?>
                    <tr>
                        <td class="topic-title-cell">
                            <a href="topic.php?id=<?= $topic['TopicID'] ?>" class="topic-title">
                                <?php if ($topic['IsSticky']): ?>
                                <span class="sticky-badge">Fixado</span>
                                <?php endif; ?>
                                <?php if ($topic['IsLocked']): ?>
                                <span class="locked-badge">Trancado</span>
                                <?php endif; ?>
                                <?= htmlspecialchars($topic['TopicTitle']) ?>
                            </a>
                            <div class="topic-meta">
                                por <?= htmlspecialchars($topic['CustomerName']) ?> • 
                                <?= date('d/m/Y H:i', strtotime($topic['CreatedAt'])) ?>
                            </div>
                            <?php if (!empty($topic['TopicDescription'])): ?>
                            <div style="font-size: 0.9rem; color: var(--gray); margin-top: 5px;">
                                <?= htmlspecialchars($topic['TopicDescription']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="topic-stats-cell"><?= $topic['ReplyCount'] ?></td>
                        <td class="topic-stats-cell"><?= $topic['ViewCount'] ?></td>
                        <td class="topic-lastpost-cell">
                            <?php if ($topic['LastPostAt']): ?>
                            <div style="font-size: 0.9rem;">
                                por <?= htmlspecialchars($topic['LastPosterName']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--gray);">
                                <?= date('d/m/Y H:i', strtotime($topic['LastPostAt'])) ?>
                            </div>
                            <?php else: ?>
                            <div style="color: var(--gray);">Nenhuma resposta</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="category.php?id=<?= $categoryId ?>&page=<?= $page - 1 ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="category.php?id=<?= $categoryId ?>&page=<?= $i ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="page-link">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="category.php?id=<?= $categoryId ?>&page=<?= $page + 1 ?>" class="page-link">
                    Próxima <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>Nenhum tópico encontrado</h3>
                <p>Seja o primeiro a criar um tópico nesta categoria!</p>
                <?php if (isLoggedIn()): ?>
                <button class="new-topic-btn" onclick="openCreateTopicModal()" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i>
                    Criar Primeiro Tópico
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Criar Tópico -->
    <?php if (isLoggedIn()): ?>
    <div id="createTopicModal" class="modal">
        <div class="modal-content">
            <h2>Criar Novo Tópico</h2>
            <form method="POST" action="forum.php">
                <input type="hidden" name="create_topic" value="1">
                <input type="hidden" name="category_id" value="<?= $categoryId ?>">
                
                <div class="form-group">
                    <label for="topic_title">Título do Tópico *</label>
                    <input type="text" id="topic_title" name="topic_title" placeholder="Digite o título do tópico" required>
                </div>
                
                <div class="form-group">
                    <label for="topic_description">Descrição (opcional)</label>
                    <input type="text" id="topic_description" name="topic_description" placeholder="Breve descrição do tópico">
                </div>
                
                <div class="form-group">
                    <label for="post_content">Conteúdo *</label>
                    <textarea id="post_content" name="post_content" placeholder="Digite o conteúdo do seu post" required></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeCreateTopicModal()">Cancelar</button>
                    <button type="submit" class="create-btn">
                        <i class="fas fa-plus"></i>
                        Criar Tópico
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function openCreateTopicModal() {
            document.getElementById('createTopicModal').style.display = 'flex';
        }

        function closeCreateTopicModal() {
            document.getElementById('createTopicModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('createTopicModal');
            if (event.target === modal) {
                closeCreateTopicModal();
            }
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCreateTopicModal();
            }
        });

        // Adicionar estilo para modal
        const style = document.createElement('style');
        style.textContent = `
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }
            
            .modal-content {
                background-color: var(--dark);
                border-radius: 10px;
                width: 600px;
                max-width: 90%;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                max-height: 90vh;
                overflow-y: auto;
            }
            
            .modal-content h2 {
                margin-bottom: 20px;
                color: var(--gamejolt-green);
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: var(--light);
            }
            
            .form-group input, .form-group textarea {
                width: 100%;
                padding: 12px 15px;
                background-color: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 5px;
                color: var(--light);
                outline: none;
            }
            
            .form-group textarea {
                min-height: 150px;
                resize: vertical;
            }
            
            .modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 20px;
            }
            
            .cancel-btn, .create-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            
            .cancel-btn {
                background-color: transparent;
                color: var(--gray);
                border: 1px solid var(--gray);
            }
            
            .cancel-btn:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            
            .create-btn {
                background-color: var(--gamejolt-green);
                color: white;
            }
            
            .create-btn:hover {
                background-color: #5ab869;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>