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

// Adicione estas funções ao config.php

// Função para curtir/descurtir um post
function togglePostLike($pdo, $postId, $userId) {
    $pdo->beginTransaction();
    
    try {
        // Verificar se já curtiu
        $stmt = $pdo->prepare("SELECT LikeID FROM forum_likes WHERE PostID = ? AND CustomerID = ?");
        $stmt->execute([$postId, $userId]);
        $existingLike = $stmt->fetch();
        
        if ($existingLike) {
            // Remover like
            $stmt = $pdo->prepare("DELETE FROM forum_likes WHERE PostID = ? AND CustomerID = ?");
            $stmt->execute([$postId, $userId]);
            $action = 'unlike';
        } else {
            // Adicionar like
            $stmt = $pdo->prepare("INSERT INTO forum_likes (PostID, CustomerID) VALUES (?, ?)");
            $stmt->execute([$postId, $userId]);
            $action = 'like';
        }
        
        // Atualizar contador
        $stmt = $pdo->prepare("UPDATE forum_posts SET LikesCount = (SELECT COUNT(*) FROM forum_likes WHERE PostID = ?) WHERE PostID = ?");
        $stmt->execute([$postId, $postId]);
        
        // Obter nova contagem
        $stmt = $pdo->prepare("SELECT LikesCount FROM forum_posts WHERE PostID = ?");
        $stmt->execute([$postId]);
        $newCount = $stmt->fetch(PDO::FETCH_ASSOC)['LikesCount'];
        
        $pdo->commit();
        return ['success' => true, 'action' => $action, 'likesCount' => $newCount];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Função para verificar se usuário curtiu um post
function hasUserLikedPost($pdo, $postId, $userId) {
    $stmt = $pdo->prepare("SELECT LikeID FROM forum_likes WHERE PostID = ? AND CustomerID = ?");
    $stmt->execute([$postId, $userId]);
    return $stmt->fetch() !== false;
}

// Função para editar post
function editForumPost($pdo, $postId, $newContent, $userId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE forum_posts 
            SET PostContent = ?, UpdatedAt = NOW(), IsEdited = 1, EditedBy = ?, EditedAt = NOW() 
            WHERE PostID = ? AND CustomerID = ?
        ");
        $result = $stmt->execute([$newContent, $userId, $postId, $userId]);
        return $result;
    } catch (Exception $e) {
        return false;
    }
}

// Função para deletar post
function deleteForumPost($pdo, $postId, $userId) {
    $pdo->beginTransaction();
    
    try {
        // Verificar se é o autor ou admin
        $stmt = $pdo->prepare("SELECT CustomerID FROM forum_posts WHERE PostID = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            throw new Exception("Post não encontrado");
        }
        
        // Permitir se for o autor ou admin (CustomerID = 1)
        if ($post['CustomerID'] != $userId && $userId != 1) {
            throw new Exception("Sem permissão para deletar este post");
        }
        
        // Deletar likes primeiro
        $stmt = $pdo->prepare("DELETE FROM forum_likes WHERE PostID = ?");
        $stmt->execute([$postId]);
        
        // Deletar post
        $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE PostID = ?");
        $stmt->execute([$postId]);
        
        // Atualizar contador do tópico
        $stmt = $pdo->prepare("
            UPDATE forum_topics 
            SET ReplyCount = GREATEST(0, ReplyCount - 1) 
            WHERE TopicID = (SELECT TopicID FROM forum_posts WHERE PostID = ?)
        ");
        $stmt->execute([$postId]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Função para obter informações completas do post com likes
function getPostWithLikes($pdo, $postId, $currentUserId = null) {
    $stmt = $pdo->prepare("
        SELECT fp.*, 
               u.CustomerName, 
               u.CustomerHandle,
               u.ProfilePhoto,
               u.ProfileIcon,
               u.CreatedAt as JoinDate,
               (SELECT COUNT(*) FROM forum_likes WHERE PostID = fp.PostID) as LikesCount,
               ? as HasLiked
        FROM forum_posts fp
        LEFT JOIN usuarios u ON fp.CustomerID = u.CustomerID
        WHERE fp.PostID = ?
    ");
    
    $hasLiked = $currentUserId ? hasUserLikedPost($pdo, $postId, $currentUserId) : false;
    $stmt->execute([$hasLiked ? 1 : 0, $postId]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

// Função para obter screenshots do jogo
function getGameScreenshots($pdo, $gameId) {
    // Por enquanto, vamos simular screenshots
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

// =============================================================================
// FUNÇÕES DO FÓRUM - CORRIGIDAS
// =============================================================================

// Função para verificar se as tabelas do fórum existem
function checkForumTables($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM forum_categories");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para obter categorias do fórum
function getForumCategories($pdo) {
    // Verificar se as tabelas existem
    if (!checkForumTables($pdo)) {
        return [];
    }
    
    try {
        $stmt = $pdo->query("
            SELECT fc.*, 
                   (SELECT COUNT(*) FROM forum_topics ft WHERE ft.CategoryID = fc.CategoryID) as topic_count,
                   (SELECT COUNT(*) FROM forum_posts fp 
                    JOIN forum_topics ft ON fp.TopicID = ft.TopicID 
                    WHERE ft.CategoryID = fc.CategoryID) as post_count
            FROM forum_categories fc 
            ORDER BY fc.CategoryID
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Função para obter tópicos de uma categoria - CORRIGIDA
function getCategoryTopics($pdo, $categoryId, $limit = 20, $offset = 0) {
    if (!checkForumTables($pdo)) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   u.CustomerName, 
                   u.CustomerHandle,
                   u.ProfilePhoto,
                   lu.CustomerName as LastPosterName,
                   lu.CustomerHandle as LastPosterHandle
            FROM forum_topics ft
            LEFT JOIN usuarios u ON ft.CustomerID = u.CustomerID
            LEFT JOIN usuarios lu ON ft.LastPostBy = lu.CustomerID
            WHERE ft.CategoryID = ?
            ORDER BY ft.IsSticky DESC, ft.UpdatedAt DESC
            LIMIT ?, ?
        ");
        $stmt->execute([$categoryId, $offset, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Função para obter tópicos recentes - CORRIGIDA
function getRecentTopics($pdo, $limit = 10) {
    if (!checkForumTables($pdo)) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   fc.CategoryName,
                   u.CustomerName,
                   u.CustomerHandle,
                   lu.CustomerName as LastPosterName
            FROM forum_topics ft
            LEFT JOIN forum_categories fc ON ft.CategoryID = fc.CategoryID
            LEFT JOIN usuarios u ON ft.CustomerID = u.CustomerID
            LEFT JOIN usuarios lu ON ft.LastPostBy = lu.CustomerID
            ORDER BY ft.UpdatedAt DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Função para criar novo tópico
function createForumTopic($pdo, $categoryId, $title, $description, $userId) {
    if (!checkForumTables($pdo)) {
        return false;
    }
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO forum_topics (CategoryID, TopicTitle, TopicDescription, CustomerID) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$categoryId, $title, $description, $userId]);
        $topicId = $pdo->lastInsertId();
        
        $pdo->commit();
        return $topicId;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Função para criar post em tópico
function createForumPost($pdo, $topicId, $content, $userId) {
    if (!checkForumTables($pdo)) {
        return false;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Inserir o post
        $stmt = $pdo->prepare("
            INSERT INTO forum_posts (TopicID, CustomerID, PostContent) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$topicId, $userId, $content]);
        
        // Atualizar contadores do tópico
        $stmt = $pdo->prepare("
            UPDATE forum_topics 
            SET ReplyCount = ReplyCount + 1,
                UpdatedAt = NOW(),
                LastPostBy = ?,
                LastPostAt = NOW()
            WHERE TopicID = ?
        ");
        $stmt->execute([$userId, $topicId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Função para obter posts de um tópico
function getTopicPosts($pdo, $topicId) {
    if (!checkForumTables($pdo)) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT fp.*, 
                   u.CustomerName, 
                   u.CustomerHandle,
                   u.ProfilePhoto,
                   u.ProfileIcon,
                   u.CreatedAt as JoinDate
            FROM forum_posts fp
            LEFT JOIN usuarios u ON fp.CustomerID = u.CustomerID
            WHERE fp.TopicID = ?
            ORDER BY fp.CreatedAt ASC
        ");
        $stmt->execute([$topicId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Função para obter informações do tópico
function getTopicInfo($pdo, $topicId) {
    if (!checkForumTables($pdo)) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   fc.CategoryName,
                   u.CustomerName,
                   u.CustomerHandle,
                   u.ProfilePhoto
            FROM forum_topics ft
            LEFT JOIN forum_categories fc ON ft.CategoryID = fc.CategoryID
            LEFT JOIN usuarios u ON ft.CustomerID = u.CustomerID
            WHERE ft.TopicID = ?
        ");
        $stmt->execute([$topicId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// Função para incrementar visualizações do tópico
function incrementTopicViews($pdo, $topicId) {
    if (!checkForumTables($pdo)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE forum_topics SET ViewCount = ViewCount + 1 WHERE TopicID = ?");
        $stmt->execute([$topicId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para obter estatísticas do usuário (compatível com ambas as tabelas)
function getUserStats($pdo, $userId) {
    $stats = ['post_count' => 0, 'game_count' => 0];
    
    // Contar posts (tentar nova tabela primeiro)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_posts WHERE CustomerID = ?");
        $stmt->execute([$userId]);
        $stats['post_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (Exception $e) {
        // Se falhar, tentar tabela antiga
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM posts WHERE CustomerID = ?");
            $stmt->execute([$userId]);
            $stats['post_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e2) {
            $stats['post_count'] = 0;
        }
    }
    
    // Contar fangames
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM fangames WHERE DeveloperID = ?");
        $stmt->execute([$userId]);
        $stats['game_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (Exception $e) {
        $stats['game_count'] = 0;
    }
    
    return $stats;
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

// Criar diretórios de upload se não existirem
createUploadDirs();
?>
