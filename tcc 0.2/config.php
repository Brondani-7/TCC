<?php
// config.php
session_start();

$host = 'localhost';
$dbname = 'database_tcc';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

// Função para obter dados do usuário logado
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE CustomerID = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para obter um fangame específico
function getFangame($pdo, $gameId) {
    $stmt = $pdo->prepare("
        SELECT f.*, u.CustomerName, u.CustomerHandle, u.ProfilePhoto, u.ProfileBanner 
        FROM fangames f 
        LEFT JOIN usuarios u ON f.DeveloperID = u.CustomerID 
        WHERE f.GameID = ?
    ");
    $stmt->execute([$gameId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para verificar se o usuário é o desenvolvedor do jogo
function isGameDeveloper($pdo, $gameId, $userId) {
    $stmt = $pdo->prepare("SELECT DeveloperID FROM fangames WHERE GameID = ?");
    $stmt->execute([$gameId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $game && $game['DeveloperID'] == $userId;
}

// Função para incrementar downloads
function incrementDownloads($pdo, $gameId) {
    $stmt = $pdo->prepare("UPDATE fangames SET Downloads = Downloads + 1 WHERE GameID = ?");
    return $stmt->execute([$gameId]);
}

// Função para obter screenshots do jogo (você pode criar uma tabela separada para isso)
function getGameScreenshots($pdo, $gameId) {
    // Por enquanto, vamos simular screenshots
    // Você pode criar uma tabela 'game_screenshots' depois
    return [
        'screenshots/screen1.jpg',
        'screenshots/screen2.jpg',
        'screenshots/screen3.jpg'
    ];
}

// Função para obter todos os fangames
function getAllFangames($pdo, $limit = null, $offset = 0) {
    $sql = "SELECT f.*, u.CustomerName, u.CustomerHandle, u.ProfilePhoto 
            FROM fangames f 
            LEFT JOIN usuarios u ON f.DeveloperID = u.CustomerID 
            ORDER BY f.CreatedAt DESC";
    
    if ($limit) {
        $sql .= " LIMIT $offset, $limit";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar fangames com filtros
function searchFangames($pdo, $search = '', $franchise = '', $genre = '', $status = '', $limit = 20, $offset = 0) {
    $sql = "SELECT f.*, u.CustomerName, u.CustomerHandle, u.ProfilePhoto 
            FROM fangames f 
            LEFT JOIN usuarios u ON f.DeveloperID = u.CustomerID 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (f.GameTitle LIKE ? OR f.GameDescription LIKE ? OR f.Tags LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($franchise)) {
        $sql .= " AND f.Franchise = ?";
        $params[] = $franchise;
    }
    
    if (!empty($genre)) {
        $sql .= " AND f.Genre = ?";
        $params[] = $genre;
    }
    
    if (!empty($status)) {
        $sql .= " AND f.Status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY f.CreatedAt DESC LIMIT $offset, $limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter franquias únicas
function getUniqueFranchises($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT Franchise FROM fangames WHERE Franchise IS NOT NULL ORDER BY Franchise");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Função para obter gêneros únicos
function getUniqueGenres($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT Genre FROM fangames WHERE Genre IS NOT NULL ORDER BY Genre");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Função para obter a URL correta da capa do jogo
function getGameCover($game) {
    if (!empty($game['GameCover'])) {
        if (file_exists($game['GameCover'])) {
            return $game['GameCover'];
        }
        $defaultPath = 'uploads/games/covers/' . basename($game['GameCover']);
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
    }
    return null;
}

// Função para obter a URL do avatar do desenvolvedor
function getDevAvatar($user) {
    if (!empty($user['ProfilePhoto'])) {
        if (file_exists($user['ProfilePhoto'])) {
            return $user['ProfilePhoto'];
        }
        $defaultPath = 'uploads/profiles/' . basename($user['ProfilePhoto']);
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
    }
    return null;
}

// Função para fazer logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Função para criar diretórios se não existirem
function createUploadDirs() {
    $dirs = ['uploads/games/covers', 'uploads/games/files', 'uploads/profiles', 'uploads/games/screenshots'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

// Processar logout se solicitado
if (isset($_GET['logout'])) {
    logout();
}

if (isset($_GET['download']) && !$user) {
    $_SESSION['redirect_url'] = 'game.php?id=' . $gameId . '&download=1';
    header('Location: login.php');
    exit;
}
?>