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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fangames | BONFIRE GAMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* (Manter todo o CSS original do fangames.html) */
        <?php include 'styles/fangames.css'; ?>
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
                <a href="#" class="nav-link">
                    <i class="fas fa-fire"></i>
                    <span>Populares</span>
                </a>
                <a href="fangames.php" class="nav-link active">
                    <i class="fas fa-star"></i>
                    <span>Fangames</span>
                </a>
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Forum</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </div>
            
            <div class="trending-topics">
                <div class="trending-title">
                    <i class="fas fa-hashtag"></i>
                    <span>Franquias Populares</span>
                </div>
                <?php
                // Buscar franquias populares
                $stmt = $pdo->query("
                    SELECT Franchise, COUNT(*) as count 
                    FROM fangames 
                    GROUP BY Franchise 
                    ORDER BY count DESC 
                    LIMIT 4
                ");
                $franchises = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($franchises as $franchise): ?>
                <div class="trending-item">
                    <div class="trending-name"><?= htmlspecialchars($franchise['Franchise']) ?></div>
                    <div class="trending-stats"><?= $franchise['count'] ?> fangames</div>
                </div>
                <?php endforeach; ?>
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
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            
            <div class="fangames-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Descubra Fangames Incríveis</h1>
                        <p class="page-description">Explore jogos criados por fãs baseados em suas franquias favoritas. Da comunidade, para a comunidade.</p>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <button class="upload-button" onclick="openUploadModal()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Enviar Fangame
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
                    Fangames Recentes
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
        
        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="widget">
                <div class="widget-title">
                    <i class="fas fa-trophy"></i>
                    <span>Top Criadores</span>
                </div>
                <div class="top-creators">
                    <?php
                    $stmt = $pdo->query("
                        SELECT u.CustomerName, COUNT(f.GameID) as gameCount, SUM(f.Downloads) as totalDownloads
                        FROM usuarios u 
                        LEFT JOIN fangames f ON u.CustomerID = f.DeveloperID 
                        GROUP BY u.CustomerID 
                        ORDER BY totalDownloads DESC 
                        LIMIT 4
                    ");
                    $topCreators = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($topCreators as $creator): ?>
                    <div class="creator-item">
                        <div class="creator-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="creator-info">
                            <div class="creator-name"><?= htmlspecialchars($creator['CustomerName']) ?></div>
                            <div class="creator-stats"><?= $creator['gameCount'] ?> fangames · <?= number_format($creator['totalDownloads']) ?> downloads</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="widget">
                <div class="widget-title">
                    <i class="fas fa-chart-bar"></i>
                    <span>Estatísticas</span>
                </div>
                <div class="stats-grid">
                    <?php
                    $stats = $pdo->query("
                        SELECT 
                            COUNT(*) as totalGames,
                            COUNT(DISTINCT DeveloperID) as totalCreators,
                            SUM(Downloads) as totalDownloads,
                            (SELECT COUNT(*) FROM posts) as totalComments
                        FROM fangames
                    ")->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stat-item">
                        <span class="stat-value"><?= number_format($stats['totalGames']) ?></span>
                        <span class="stat-label">Fangames</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= number_format($stats['totalCreators']) ?></span>
                        <span class="stat-label">Criadores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= number_format($stats['totalDownloads']) ?></span>
                        <span class="stat-label">Downloads</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= number_format($stats['totalComments']) ?></span>
                        <span class="stat-label">Comentários</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Upload -->
    <div id="uploadModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Enviar Fangame</h2>
            <form id="uploadForm" action="upload_game.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="gameTitle">Título do Jogo</label>
                    <input type="text" id="gameTitle" name="gameTitle" required>
                </div>
                <div class="form-group">
                    <label for="gameDescription">Descrição</label>
                    <textarea id="gameDescription" name="gameDescription" required></textarea>
                </div>
                <div class="form-group">
                    <label for="gameGenre">Gênero</label>
                    <select id="gameGenre" name="gameGenre" required>
                        <option value="">Selecione um gênero</option>
                        <option value="RPG">RPG</option>
                        <option value="Plataforma">Plataforma</option>
                        <option value="Aventura">Aventura</option>
                        <option value="Puzzle">Puzzle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gameFranchise">Franquia</label>
                    <input type="text" id="gameFranchise" name="gameFranchise" required>
                </div>
                <div class="form-group">
                    <label for="gameStatus">Status</label>
                    <select id="gameStatus" name="gameStatus" required>
                        <option value="Em Desenvolvimento">Em Desenvolvimento</option>
                        <option value="Demo">Demo</option>
                        <option value="Completo">Completo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gameFile">Arquivo do Jogo</label>
                    <input type="file" id="gameFile" name="gameFile" accept=".zip,.rar,.7z" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeUploadModal()">Cancelar</button>
                    <button type="submit" class="save-btn">Enviar</button>
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
        }

        // Filtros em tempo real
        document.getElementById('searchInput').addEventListener('input', filterGames);
        document.getElementById('genreFilter').addEventListener('change', filterGames);
        document.getElementById('franchiseFilter').addEventListener('change', filterGames);
        document.getElementById('statusFilter').addEventListener('change', filterGames);

        function filterGames() {
            // Implementar filtro AJAX aqui
            console.log('Filtrando jogos...');
        }
    </script>
</body>
</html>