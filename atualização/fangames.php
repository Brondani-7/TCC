<?php
// fangames.php
require_once 'config.php';

// Verificar se usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);

// Variáveis para filtros e busca
$search = isset($_GET['search']) ? $_GET['search'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$franchise = isset($_GET['franchise']) ? $_GET['franchise'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Construir query base
$sql = "SELECT f.*, u.CustomerName, u.CustomerHandle 
        FROM fangames f 
        INNER JOIN usuarios u ON f.DeveloperID = u.CustomerID 
        WHERE 1=1";

$params = [];

// Aplicar filtros
if (!empty($search)) {
    $sql .= " AND (f.GameTitle LIKE ? OR f.GameDescription LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($genre)) {
    $sql .= " AND f.Genre = ?";
    $params[] = $genre;
}

if (!empty($franchise)) {
    $sql .= " AND f.Franchise = ?";
    $params[] = $franchise;
}

if (!empty($status)) {
    $sql .= " AND f.Status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY f.CreatedAt DESC";

// Executar query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar tags únicas para os filtros
$genresStmt = $pdo->query("SELECT DISTINCT Genre FROM fangames WHERE Genre IS NOT NULL AND Genre != '' ORDER BY Genre");
$genres = $genresStmt->fetchAll(PDO::FETCH_COLUMN);

$franchisesStmt = $pdo->query("SELECT DISTINCT Franchise FROM fangames WHERE Franchise IS NOT NULL AND Franchise != '' ORDER BY Franchise");
$franchises = $franchisesStmt->fetchAll(PDO::FETCH_COLUMN);

// Status disponíveis
$statusOptions = ['Completo', 'Em Desenvolvimento', 'Demo'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fangames - GameJolt Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0d1117;
            color: #c9d1d9;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background-color: #161b22;
            border-bottom: 1px solid #30363d;
            padding: 1rem 0;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #58a6ff;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #c9d1d9;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #58a6ff;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #c9d1d9;
            transition: color 0.3s;
        }

        .profile-link:hover {
            color: #58a6ff;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #30363d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 2px solid #58a6ff;
            transition: border-color 0.3s;
        }

        .profile-link:hover .profile-icon {
            border-color: #58a6ff;
        }

        .logout-btn {
            background-color: #da3633;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #f85149;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #58a6ff;
        }

        .page-subtitle {
            color: #8b949e;
            font-size: 1.1rem;
        }

        /* Filters */
        .filters {
            background-color: #161b22;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #8b949e;
            font-size: 0.9rem;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 0.75rem;
            background-color: #0d1117;
            border: 1px solid #30363d;
            border-radius: 4px;
            color: #c9d1d9;
            font-size: 0.9rem;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #58a6ff;
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            background-color: #238636;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .filter-button:hover {
            background-color: #2ea043;
        }

        /* Games Grid */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .game-card {
            background-color: #161b22;
            border: 1px solid #30363d;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, border-color 0.3s;
        }

        .game-card:hover {
            transform: translateY(-4px);
            border-color: #58a6ff;
        }

        .game-header {
            padding: 1.5rem;
            border-bottom: 1px solid #30363d;
        }

        .game-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #58a6ff;
        }

        .game-description {
            color: #8b949e;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .game-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #8b949e;
        }

        .game-developer {
            color: #58a6ff;
        }

        .game-body {
            padding: 1rem 1.5rem;
        }

        .game-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .game-tag {
            background-color: #30363d;
            color: #c9d1d9;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
        }

        .game-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #8b949e;
        }

        .game-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .download-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #238636;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }

        .download-button:hover {
            background-color: #2ea043;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #8b949e;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .games-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                gap: 1rem;
            }

            .user-section {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">GameJolt Clone</a>
                <div class="nav-links">
                    <a href="index.php">Início</a>
                    <a href="fangames.php" style="color: #58a6ff;">Fangames</a>
                    <a href="foruns.php">Fóruns</a>
                    
                    <div class="user-section">
                        <a href="profile.php" class="profile-link">
                            <div class="profile-icon"><?php echo htmlspecialchars($user['ProfileIcon'] ?? '👤'); ?></div>
                        </a>
                        <a href="?logout=true" class="logout-btn">Sair</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Descubra Fangames</h1>
                <p class="page-subtitle">Explore uma coleção diversificada de fangames criados pela comunidade</p>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" action="fangames.php">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="search">Buscar</label>
                            <input type="text" id="search" name="search" class="filter-input" 
                                   placeholder="Buscar por título ou descrição..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="genre">Gênero</label>
                            <select id="genre" name="genre" class="filter-select">
                                <option value="">Todos os gêneros</option>
                                <?php foreach ($genres as $genreOption): ?>
                                    <option value="<?php echo htmlspecialchars($genreOption); ?>" 
                                        <?php echo $genre === $genreOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($genreOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="franchise">Franquia</label>
                            <select id="franchise" name="franchise" class="filter-select">
                                <option value="">Todas as franquias</option>
                                <?php foreach ($franchises as $franchiseOption): ?>
                                    <option value="<?php echo htmlspecialchars($franchiseOption); ?>" 
                                        <?php echo $franchise === $franchiseOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($franchiseOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="filter-select">
                                <option value="">Todos os status</option>
                                <?php foreach ($statusOptions as $statusOption): ?>
                                    <option value="<?php echo htmlspecialchars($statusOption); ?>" 
                                        <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($statusOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="filter-button">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Lista de Games -->
            <?php if (count($games) > 0): ?>
                <div class="games-grid">
                    <?php foreach ($games as $game): ?>
                        <div class="game-card">
                            <div class="game-header">
                                <h3 class="game-title"><?php echo htmlspecialchars($game['GameTitle']); ?></h3>
                                <p class="game-description"><?php echo htmlspecialchars($game['GameDescription'] ?? 'Sem descrição disponível.'); ?></p>
                                <div class="game-meta">
                                    <span class="game-developer">por <?php echo htmlspecialchars($game['CustomerHandle'] ?? $game['CustomerName']); ?></span>
                                    <span><?php echo date('d/m/Y', strtotime($game['CreatedAt'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="game-body">
                                <div class="game-tags">
                                    <?php if (!empty($game['Genre'])): ?>
                                        <span class="game-tag"><?php echo htmlspecialchars($game['Genre']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($game['Franchise'])): ?>
                                        <span class="game-tag"><?php echo htmlspecialchars($game['Franchise']); ?></span>
                                    <?php endif; ?>
                                    
                                    <span class="game-tag"><?php echo htmlspecialchars($game['Status']); ?></span>
                                    
                                    <?php if (!empty($game['FileSize'])): ?>
                                        <span class="game-tag"><?php echo htmlspecialchars($game['FileSize']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <span>⭐ <?php echo number_format($game['Rating'], 1); ?></span>
                                    </div>
                                    <div class="game-stat">
                                        <span>⬇️ <?php echo number_format($game['Downloads']); ?></span>
                                    </div>
                                </div>
                                
                                <button class="download-button" onclick="downloadGame(<?php echo $game['GameID']; ?>)">
                                    Baixar Fangame
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h3>Nenhum fangame encontrado</h3>
                    <p>Tente ajustar os filtros ou buscar por outros termos.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
<!-- No loop dos jogos, modifique o título para link: -->
<h3 class="game-title">
    <a href="game_details.php?id=<?php echo $game['GameID']; ?>" style="color: inherit; text-decoration: none;">
        <?php echo htmlspecialchars($game['GameTitle']); ?>
    </a>
</h3>

<!-- E modifique o botão de download: -->
<a href="game_details.php?id=<?php echo $game['GameID']; ?>&download=true" class="download-button">
    Baixar Fangame
</a>
    <script>
        function downloadGame(gameId) {
            if (confirm('Deseja baixar este fangame?')) {
                // Aqui você pode implementar a lógica de download
                // Por exemplo, redirecionar para uma página de download
                window.location.href = 'download.php?game_id=' + gameId;
            }
        }

        // Adicionar funcionalidade de busca em tempo real (opcional)
        document.getElementById('search').addEventListener('input', function() {
            // Você pode implementar busca em tempo real aqui se desejar
        });
    </script>
</body>
</html>