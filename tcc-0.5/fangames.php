<?php
// fangames.php - Versão Integrada com Banco de Dados
require_once 'config.php';

// Verificar se o usuário está logado e obter dados
$user = null;
if (isLoggedIn()) {
    $user = getCurrentUser($pdo);
}

// Parâmetros de busca e filtros
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '';
$franchise = filter_input(INPUT_GET, 'franchise', FILTER_SANITIZE_STRING) ?? '';
$genre = filter_input(INPUT_GET, 'genre', FILTER_SANITIZE_STRING) ?? '';
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Buscar fangames com base nos filtros
$fangames = [];
$totalFangames = 0;

try {
    // Construir query base usando a função do config.php
    $fangames = searchFangames($pdo, $search, $franchise, $genre, $status, $limit, $offset);
    
    // Contar total de fangames (para paginação)
    $countQuery = "SELECT COUNT(*) FROM fangames WHERE 1=1";
    $countParams = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (GameTitle LIKE ? OR GameDescription LIKE ? OR Tags LIKE ?)";
        $searchTerm = "%$search%";
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }
    
    if (!empty($franchise)) {
        $countQuery .= " AND Franchise = ?";
        $countParams[] = $franchise;
    }
    
    if (!empty($genre)) {
        $countQuery .= " AND Genre = ?";
        $countParams[] = $genre;
    }
    
    if (!empty($status)) {
        $countQuery .= " AND Status = ?";
        $countParams[] = $status;
    }
    
    // Executar contagem
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($countParams);
    $totalFangames = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Erro ao buscar fangames: " . $e->getMessage());
    $error = "Erro ao carregar fangames. Tente novamente mais tarde.";
}

// Obter opções de filtro usando funções do config.php
$franchises = getUniqueFranchises($pdo);
$genres = getUniqueGenres($pdo);

// Calcular total de páginas
$totalPages = ceil($totalFangames / $limit);
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
            --gamejolt-blue: #191b21;
            --gamejolt-green: #6bc679;
            --gamejolt-purple: #8b6bc6;
            --gamejolt-orange: #ff7a33;
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
        
        /* Search and Filters */
        .search-section {
            background: var(--secondary);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 20px;
            background: var(--dark);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            color: var(--light);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--gamejolt-green);
        }
        
        .search-btn {
            padding: 12px 25px;
            background: var(--gamejolt-green);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .filter-select {
            padding: 10px 15px;
            background: var(--dark);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--light);
            outline: none;
            cursor: pointer;
        }
        
        /* Games Grid */
        .games-section {
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .games-count {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .game-card {
            background: var(--secondary);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
        
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .game-cover {
            height: 180px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
        }
        
        .game-cover-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .game-card:hover .game-cover-image {
            transform: scale(1.05);
        }
        
        .game-cover .default-cover {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
            color: white;
            font-size: 3rem;
        }
        
        .game-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }
        
        .status-lançado {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .status-em-desenvolvimento {
            background: var(--gamejolt-orange);
            color: white;
        }
        
        .status-pausado {
            background: var(--warning);
            color: white;
        }
        
        .status-cancelado {
            background: var(--danger);
            color: white;
        }
        
        .game-info {
            padding: 20px;
        }
        
        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .game-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 5px;
            line-height: 1.3;
        }
        
        .game-developer {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .dev-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
        }
        
        .dev-name {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .game-description {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .game-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .game-franchise, .game-genre {
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .game-stats {
            display: flex;
            gap: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }
        
        .game-stat {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        .game-stat i {
            color: var(--gamejolt-green);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .pagination-btn {
            padding: 10px 15px;
            background: var(--secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--light);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .pagination-btn:hover:not(.disabled) {
            background: var(--primary);
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-pages {
            display: flex;
            gap: 5px;
        }
        
        .page-number {
            padding: 10px 15px;
            background: var(--secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--light);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .page-number.active {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .page-number:hover:not(.active) {
            background: var(--primary);
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
        
        /* Image Fallback */
        .image-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gamejolt-purple), var(--gamejolt-orange));
            color: white;
            font-size: 3rem;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .games-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
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
            
            .filters {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .games-grid {
                grid-template-columns: 1fr;
            }
            
            .search-bar {
                flex-direction: column;
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
            
            .pagination {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .game-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .game-stats {
                flex-wrap: wrap;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
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
                <a href="fangames.php" class="nav-link active">
                    <i class="fas fa-gamepad"></i>
                    <span>Fangames</span>
                </a>
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">  
            <div class="header">
                <div class="page-title">Descobrir Fangames</div>
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
                    <a href="login.php" class="search-btn" style="text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i>
                        Fazer Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Search Section -->
            <div class="search-section">
                <form method="GET" action="fangames.php">
                    <div class="search-bar">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Buscar fangames..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="filters">
                        <div class="filter-group">
                            <label class="filter-label">Franquia</label>
                            <select name="franchise" class="filter-select">
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
                            <label class="filter-label">Gênero</label>
                            <select name="genre" class="filter-select">
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
                            <label class="filter-label">Status</label>
                            <select name="status" class="filter-select">
                                <option value="">Todos os status</option>
                                <option value="Lançado" <?php echo $status === 'Lançado' ? 'selected' : ''; ?>>Lançado</option>
                                <option value="Em Desenvolvimento" <?php echo $status === 'Em Desenvolvimento' ? 'selected' : ''; ?>>Em Desenvolvimento</option>
                                <option value="Pausado" <?php echo $status === 'Pausado' ? 'selected' : ''; ?>>Pausado</option>
                                <option value="Cancelado" <?php echo $status === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">&nbsp;</label>
                            <a href="fangames.php" class="search-btn" style="text-decoration: none; text-align: center;">
                                <i class="fas fa-times"></i> Limpar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Games Section -->
            <div class="games-section">
                <div class="section-header">
                    <div class="section-title">
                        <?php if (!empty($search)): ?>
                            Fangames para "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Fangames Recentes
                        <?php endif; ?>
                    </div>
                    <div class="games-count">
                        <?php echo $totalFangames; ?> fangame<?php echo $totalFangames != 1 ? 's' : ''; ?> encontrado<?php echo $totalFangames != 1 ? 's' : ''; ?>
                    </div>
                </div>
                
                <?php if (!empty($fangames)): ?>
                    <div class="games-grid">
                        <?php foreach ($fangames as $game): ?>
                        <div class="game-card" onclick="window.location.href='game.php?id=<?php echo $game['GameID']; ?>'">
                            <div class="game-cover">
                                <?php 
                                $coverUrl = getGameCover($game);
                                if (!empty($coverUrl)): ?>
                                    <img src="<?php echo htmlspecialchars($coverUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($game['GameTitle']); ?>" 
                                         class="game-cover-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="image-fallback" style="display: none;">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="image-fallback">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="game-status status-<?php echo strtolower(str_replace(' ', '-', $game['Status'])); ?>">
                                    <?php echo htmlspecialchars($game['Status']); ?>
                                </div>
                            </div>
                            
                            <div class="game-info">
                                <div class="game-header">
                                    <div>
                                        <h3 class="game-title"><?php echo htmlspecialchars($game['GameTitle']); ?></h3>
                                        <div class="game-developer">
                                            <div class="dev-avatar" style="background-image: url('<?php echo htmlspecialchars(getDevAvatar($game)); ?>')">
                                                <?php if(empty($game['ProfilePhoto'])): ?>
                                                    <i class="fas fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="dev-name">@<?php echo htmlspecialchars($game['CustomerHandle'] ?? $game['CustomerName']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="game-description">
                                    <?php 
                                    $description = $game['GameDescription'] ?? 'Sem descrição disponível.';
                                    if (strlen($description) > 150) {
                                        $description = substr($description, 0, 150) . '...';
                                    }
                                    echo htmlspecialchars($description); 
                                    ?>
                                </p>
                                
                                <div class="game-meta">
                                    <span class="game-franchise"><?php echo htmlspecialchars($game['Franchise'] ?? 'N/A'); ?></span>
                                    <span class="game-genre"><?php echo htmlspecialchars($game['Genre'] ?? 'N/A'); ?></span>
                                </div>
                                
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <i class="fas fa-download"></i>
                                        <span><?php echo number_format($game['Downloads'] ?? 0); ?> downloads</span>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo number_format($game['Rating'] ?? 0, 1); ?></span>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('d/m/Y', strtotime($game['CreatedAt'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                           class="pagination-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                        
                        <div class="pagination-pages">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                           class="pagination-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-gamepad"></i>
                        <h3>Nenhum fangame encontrado</h3>
                        <p><?php echo !empty($search) ? 'Tente ajustar seus filtros de busca.' : 'Seja o primeiro a publicar um fangame!'; ?></p>
                        <?php if ($user): ?>
                        <a href="add_fangame.php" class="search-btn" style="margin-top: 20px; text-decoration: none;">
                            <i class="fas fa-plus"></i> Publicar Primeiro Fangame
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit form quando filtros mudam
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });

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
        
        // Adicionar loading state nos cliques
        document.querySelectorAll('.game-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.opacity = '0.7';
            });
        });
        
        // Verificar se as imagens carregam corretamente
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.game-cover-image').forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback && fallback.classList.contains('image-fallback')) {
                        fallback.style.display = 'flex';
                    }
                });
            });
        });
        
        // Efeito de hover na imagem
        document.querySelectorAll('.game-card').forEach(card => {
            const img = card.querySelector('.game-cover-image');
            if (img) {
                card.addEventListener('mouseenter', function() {
                    img.style.transform = 'scale(1.05)';
                });
                card.addEventListener('mouseleave', function() {
                    img.style.transform = 'scale(1)';
                });
            }
        });
    </script>
</body>
</html>