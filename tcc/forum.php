<?php
// Simulação de dados de posts e comentários. Em um ambiente real, isso seria feito com banco de dados.
//$posts = [
//];

// Lidar com o envio do formulário de criação de post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $link = $_POST['link'] ?? '';

    // Aqui você faria o código para salvar o post no banco de dados, por exemplo.
    // Para simplificação, vamos adicionar os dados diretamente ao array de posts:
    $posts[] = [
        'user' => 'NovoUsuario',
        'time' => 'Agora',
        'content' => $content,
        'media' => $link,
        'tags' => explode(',', $tags),
        'upvotes' => 0,
        'comments' => []
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forum Bonfire Games - Posts e Discussões</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            background: url('img/forum.gif') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            line-height: 1.6;
        }

        .top-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0,0,0,0.9);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.7);
            z-index: 1000;
        }

        .header .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .header nav ul {
            display: flex;
            list-style: none;
        }

        .header nav ul li {
            margin-left: 20px;
        }

        .header nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .header nav ul li a:hover {
            text-decoration: underline;
        }

        /* Main Container */
        .main-container {
            margin-top: 80px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Post Styles */
        .post {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .post-user {
            font-weight: bold;
            color: #007bff;
        }

        .post-time {
            margin-left: 10px;
            color: #777;
            font-size: 0.9rem;
        }

        .post-content {
            margin-bottom: 10px;
        }

        .post-media img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .post-media video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .post-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .post-link:hover {
            text-decoration: underline;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }

        .tag {
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #495057;
        }

        .post-actions {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }

        .upvote-btn, .comments-btn {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .upvote-btn:hover {
            background-color: #e9ecef;
        }

        .comments-btn:hover {
            background-color: #e9ecef;
        }

        .upvote-count {
            margin-right: 5px;
        }

        .upvote-count, .comments-count {
            font-weight: bold;
        }

        /* Comments */
        .comments-section {
            display: none;
            margin-top: 15px;
            padding-left: 20px;
            border-left: 2px solid #dee2e6;
        }

        .comment {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .comment-user {
            font-weight: bold;
            color: #007bff;
            font-size: 0.9rem;
        }

        .comment-time {
            margin-left: 10px;
            color: #777;
            font-size: 0.8rem;
        }

        .add-comment {
            margin-top: 10px;
        }

        .add-comment textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        .add-comment button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-top: 5px;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-comment button:hover {
            background-color: #0056b3;
        }

        /* Create Post */
        .create-post {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .create-post h2 {
            margin-bottom: 10px;
        }

        .create-post textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
            margin-bottom: 10px;
        }

        .create-post input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .create-post button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .create-post button:hover {
            background-color: #1e7e34;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header nav ul {
                margin-top: 10px;
            }
            .header nav ul li {
                margin-left: 15px;
                margin-right: 10px;
            }
            .post-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="top-menu">
            <div class="menu-logo">BONFIRE GAMES</div>
            <nav class="menu-nav">
                <ul>
                    <li><a href="index.php">Firelink</a></li>
                    <li><a href="#">Fansgames</a></li>
                    <li><a href="forum.php">Bonfires</a></li>
                    <li><a href="#">Sobre</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container" id="posts">
        <div class="create-post">
            <h2>Criar Novo Post</h2>
            <form method="POST">
                <input type="text" name="title" placeholder="Título do Post" required />
                <textarea name="content" placeholder="Conteúdo do Post..." rows="4" required></textarea>
                <input type="text" name="tags" placeholder="Tags (separadas por vírgula, com # ex: #fanart, #game)" />
                <input type="url" name="link" placeholder="Link para jogo ou mídia (opcional)" />
                <button type="submit">Postar</button>
            </form>
        </div>

        <?php foreach ($posts as $postId => $post): ?>
        <div class="post" id="post-<?php echo $postId; ?>">
            <div class="post-header">
                <span class="post-user"><?php echo $post['user']; ?></span>
                <span class="post-time"><?php echo $post['time']; ?></span>
            </div>
            <div class="post-content">
                <p><?php echo $post['content']; ?></p>
                <?php if ($post['media']): ?>
                <div class="post-media">
                    <?php if (strpos($post['media'], 'youtube.com') !== false): ?>
                    <iframe width="560" height="315" src="<?php echo $post['media']; ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <?php else: ?>
                    <img src="<?php echo $post['media']; ?>" alt="Post image" />
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="post-tags">
                <?php foreach ($post['tags'] as $tag): ?>
                <span class="tag"><?php echo $tag; ?></span>
                <?php endforeach; ?>
            </div>
            <div class="post-actions">
                <button class="upvote-btn">
                    <span class="upvote-count"><?php echo $post['upvotes']; ?></span> Upvote
                </button>
                <button class="comments-btn">
                    <span class="comments-count"><?php echo count($post['comments']); ?></span> Comentários
                </button>
            </div>
            <div class="comments-section">
                <?php foreach ($post['comments'] as $comment): ?>
                <div class="comment">
                    <div class="comment-user"><?php echo $comment['user']; ?> <span class="comment-time"><?php echo $comment['time']; ?></span></div>
                    <p><?php echo $comment['text']; ?></p>
                </div>
                <?php endforeach; ?>
                <div class="add-comment">
                    <textarea placeholder="Adicione seu comentário..."></textarea>
                    <button>Comentar</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
