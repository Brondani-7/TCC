<?php
require_once 'config.php';
$user = getCurrentUser($pdo);

// Buscar fangames do banco
$stmt = $pdo->query("
    SELECT f.*, u.CustomerName as DeveloperName 
    FROM fangames f 
    LEFT JOIN usuarios u ON f.DeveloperID = u.CustomerID 
    ORDER BY f.Downloads DESC 
    LIMIT 12
");
$fangames = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar fangames em destaque
$stmt = $pdo->query("
    SELECT f.*, u.CustomerName as DeveloperName 
    FROM fangames f 
    LEFT JOIN usuarios u ON f.DeveloperID = u.CustomerID 
    WHERE f.Rating >= 4.5 
    ORDER BY f.Rating DESC 
    LIMIT 2
");
$featuredGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar franquias para o filtro
$stmt = $pdo->query("
    SELECT DISTINCT Franchise 
    FROM fangames 
    ORDER BY Franchise
");
$franchises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fangames | BONFIRE GAMES</title>
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
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .header-icon:hover {
            background-color: var(--primary);
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
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            background: linear-gradient(90deg, var(--primary), #ff7e5f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-description {
            color: var(--gray);
            max-width: 600px;
        }
        
        .upload-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .upload-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filter-select, .search-box {
            background-color: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            padding: 10px 15px;
            color: var(--light);
            outline: none;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 250px;
        }
        
        .search-box i {
            margin-right: 10px;
            color: var(--gray);
        }
        
        .search-box input {
            background: transparent;
            border: none;
            color: var(--light);
            width: 100%;
            outline: none;
        }
        
        /* Featured Section */
        .featured-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .featured-games {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .featured-game {
            background: linear-gradient(135deg, var(--dark), #2a3b4c);
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .featured-game:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }
        
        .featured-cover {
            width: 150px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }
        
        .featured-info {
            padding: 20px;
            flex: 1;
        }
        
        .featured-info .game-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .featured-info .game-description {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .game-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.85rem;
        }
        
        .game-author {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .game-stats {
            display: flex;
            gap: 15px;
        }
        
        .game-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .game-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .game-tag {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        /* Games Grid */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .game-card {
            background-color: var(--dark);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .game-cover {
            height: 160px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            position: relative;
        }
        
        .game-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--success);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .game-info {
            padding: 15px;
        }
        
        .game-info .game-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .game-info .game-description {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Modal */
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
            width: 500px;
            max-width: 90%;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-content h2 {
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: var(--light);
            outline: none;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .cancel-btn, .save-btn {
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
        
        .save-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .save-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Mensagens de sucesso/erro */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        /* Responsividade */
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
            
            .featured-games {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .filter-bar {
                flex-direction: column;
            }
            
            .search-box {
                min-width: 100%;
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
                <a href="fangames.php" class="nav-link active">
                    <i class="fas fa-gamepad"></i>
                    <span>Fangames</span>
                </a>
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Fangames</div>
                <div class="header-actions">
                    <div class="header-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="user-avatar">
                         <a href="login.php" class="nav-link">
                        <i class="fas fa-user"></i>
                       </a>
                    </div>
                </div>
            </div>
            
            <div class="fangames-content">
                <!-- Mensagens de sucesso/erro -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success_message'] ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Descubra Fangames Incríveis</h1>
                        <p class="page-description">Explore jogos criados por fãs baseados em suas franquias favoritas. Da comunidade, para a comunidade.</p>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <button class="upload-button" onclick="openUploadModal()">
                        <i class="fas fa-plus"></i>
                        Adicionar Fangame
                    </button>
                    <?php else: ?>
                    <button class="upload-button" onclick="alert('Faça login para adicionar um fangame!')">
                        <i class="fas fa-plus"></i>
                        Adicionar Fangame
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="filter-bar">
                    <select class="filter-select" id="genreFilter">
                        <option value="">Todos os Gêneros</option>
                        <option value="RPG">RPG</option>
                        <option value="Plataforma">Plataforma</option>
                        <option value="Aventura">Aventura</option>
                        <option value="Puzzle">Puzzle</option>
                        <option value="Ação">Ação</option>
                        <option value="Estratégia">Estratégia</option>
                    </select>
                    <select class="filter-select" id="franchiseFilter">
                        <option value="">Qualquer Franquia</option>
                        <?php foreach ($franchises as $franchise): ?>
                        <option value="<?= htmlspecialchars($franchise['Franchise']) ?>"><?= htmlspecialchars($franchise['Franchise']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">Qualquer Status</option>
                        <option value="Completo">Completo</option>
                        <option value="Em Desenvolvimento">Em Desenvolvimento</option>
                        <option value="Demo">Demo</option>
                    </select>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar fangames..." id="searchInput">
                    </div>
                </div>
                
                <?php if (!empty($featuredGames)): ?>
                <div class="featured-section">
                    <h2 class="section-title">
                        <i class="fas fa-crown"></i>
                        Fangames em Destaque
                    </h2>
                    <div class="featured-games">
                        <?php foreach ($featuredGames as $game): ?>
                        <div class="featured-game" onclick="openGame(<?= $game['GameID'] ?>)">
                            <div class="featured-cover">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <div class="featured-info">
                                <div class="game-title"><?= htmlspecialchars($game['GameTitle']) ?></div>
                                <div class="game-description"><?= htmlspecialchars($game['GameDescription']) ?></div>
                                <div class="game-meta">
                                    <div class="game-author">
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($game['DeveloperName']) ?>
                                    </div>
                                    <div class="game-stats">
                                        <div class="game-stat">
                                            <i class="fas fa-download"></i>
                                            <?= number_format($game['Downloads']) ?>
                                        </div>
                                        <div class="game-stat">
                                            <i class="fas fa-star"></i>
                                            <?= number_format($game['Rating'], 1) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="game-tags">
                                    <div class="game-tag"><?= htmlspecialchars($game['Franchise']) ?></div>
                                    <div class="game-tag"><?= htmlspecialchars($game['Genre']) ?></div>
                                    <div class="game-tag"><?= htmlspecialchars($game['Status']) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <h2 class="section-title">
                    <i class="fas fa-rocket"></i>
                    Fangames Populares
                </h2>
                <div class="games-grid" id="gamesGrid">
                    <?php foreach ($fangames as $game): ?>
                    <div class="game-card" onclick="openGame(<?= $game['GameID'] ?>)">
                        <div class="game-cover">
                            <i class="fas fa-gamepad"></i>
                            <?php if (strtotime($game['CreatedAt']) > strtotime('-7 days')): ?>
                            <div class="game-badge">NOVO</div>
                            <?php endif; ?>
                        </div>
                        <div class="game-info">
                            <div class="game-title"><?= htmlspecialchars($game['GameTitle']) ?></div>
                            <div class="game-description"><?= htmlspecialchars($game['GameDescription']) ?></div>
                            <div class="game-meta">
                                <div class="game-author">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($game['DeveloperName']) ?>
                                </div>
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <i class="fas fa-download"></i>
                                        <?= number_format($game['Downloads']) ?>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-star"></i>
                                        <?= number_format($game['Rating'], 1) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="game-tags">
                                <div class="game-tag"><?= htmlspecialchars($game['Franchise']) ?></div>
                                <div class="game-tag"><?= htmlspecialchars($game['Genre']) ?></div>
                                <div class="game-tag"><?= htmlspecialchars($game['Status']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Upload -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <h2>Adicionar Fangame</h2>
            <form id="uploadForm" action="upload_game.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="gameTitle">Título do Jogo *</label>
                    <input type="text" id="gameTitle" name="gameTitle" required placeholder="Digite o título do jogo">
                </div>
                <div class="form-group">
                    <label for="gameDescription">Descrição *</label>
                    <textarea id="gameDescription" name="gameDescription" required placeholder="Descreva seu fangame..."></textarea>
                </div>
                <div class="form-group">
                    <label for="gameGenre">Gênero *</label>
                    <select id="gameGenre" name="gameGenre" required>
                        <option value="">Selecione um gênero</option>
                        <option value="RPG">RPG</option>
                        <option value="Plataforma">Plataforma</option>
                        <option value="Aventura">Aventura</option>
                        <option value="Puzzle">Puzzle</option>
                        <option value="Ação">Ação</option>
                        <option value="Estratégia">Estratégia</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gameFranchise">Franquia *</label>
                    <input type="text" id="gameFranchise" name="gameFranchise" required placeholder="Ex: Pokémon, Mario, Sonic...">
                </div>
                <div class="form-group">
                    <label for="gameStatus">Status *</label>
                    <select id="gameStatus" name="gameStatus" required>
                        <option value="Em Desenvolvimento">Em Desenvolvimento</option>
                        <option value="Demo">Demo</option>
                        <option value="Completo">Completo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gameFile">Arquivo do Jogo *</label>
                    <input type="file" id="gameFile" name="gameFile" accept=".zip,.rar,.7z" required>
                    <small style="color: var(--gray); font-size: 0.8rem; margin-top: 5px; display: block;">
                        Formatos aceitos: ZIP, RAR, 7Z (máx. 100MB)
                    </small>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeUploadModal()">Cancelar</button>
                    <button type="submit" class="save-btn">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Enviar Fangame
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openGame(gameId) {
            window.location.href = 'game.php?id=' + gameId;
        }

        function openUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
            // Limpar formulário
            document.getElementById('uploadForm').reset();
        }

        // Filtros em tempo real
        document.getElementById('searchInput').addEventListener('input', filterGames);
        document.getElementById('genreFilter').addEventListener('change', filterGames);
        document.getElementById('franchiseFilter').addEventListener('change', filterGames);
        document.getElementById('statusFilter').addEventListener('change', filterGames);

        function filterGames() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const genreFilter = document.getElementById('genreFilter').value;
            const franchiseFilter = document.getElementById('franchiseFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            const gameCards = document.querySelectorAll('.game-card');
            
            gameCards.forEach(card => {
                const title = card.querySelector('.game-title').textContent.toLowerCase();
                const description = card.querySelector('.game-description').textContent.toLowerCase();
                const genre = card.querySelector('.game-tags .game-tag:nth-child(2)').textContent;
                const franchise = card.querySelector('.game-tags .game-tag:nth-child(1)').textContent;
                const status = card.querySelector('.game-tags .game-tag:nth-child(3)').textContent;
                
                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesGenre = !genreFilter || genre === genreFilter;
                const matchesFranchise = !franchiseFilter || franchise === franchiseFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesSearch && matchesGenre && matchesFranchise && matchesStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('uploadModal');
            if (event.target === modal) {
                closeUploadModal();
            }
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeUploadModal();
            }
        });
    </script>
</body>
</html>