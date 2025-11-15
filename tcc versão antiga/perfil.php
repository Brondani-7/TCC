<?php
// profile.php
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);
$message = '';

// Buscar estatísticas do usuário
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM posts WHERE CustomerID = ?) as post_count,
        (SELECT COUNT(*) FROM fangames WHERE DeveloperID = ?) as game_count
");
$stmt->execute([$user['CustomerID'], $user['CustomerID']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Processar upload de foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_profile'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['profile_photo']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user['CustomerID'] . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if ($_FILES['profile_photo']['size'] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET ProfilePhoto = ? WHERE CustomerID = ?");
                    if ($stmt->execute([$target_file, $user['CustomerID']])) {
                        $message = "Foto de perfil atualizada com sucesso!";
                        $user['ProfilePhoto'] = $target_file;
                    } else {
                        $message = "Erro ao atualizar no banco de dados.";
                    }
                } else {
                    $message = "Erro ao fazer upload do arquivo.";
                }
            } else {
                $message = "Arquivo muito grande. Tamanho máximo: 5MB.";
            }
        } else {
            $message = "Tipo de arquivo não permitido. Use JPEG, PNG, GIF ou WebP.";
        }
    } else {
        $message = "Erro no upload do arquivo.";
    }
}

// Processar upload de banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_banner'])) {
    if (isset($_FILES['profile_banner']) && $_FILES['profile_banner']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['profile_banner']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/banners/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_banner']['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . $user['CustomerID'] . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if ($_FILES['profile_banner']['size'] <= 10 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['profile_banner']['tmp_name'], $target_file)) {
                    // Atualizar banner no banco (se a coluna existir)
                    try {
                        $stmt = $pdo->prepare("UPDATE usuarios SET ProfileBanner = ? WHERE CustomerID = ?");
                        $stmt->execute([$target_file, $user['CustomerID']]);
                        $user['ProfileBanner'] = $target_file;
                    } catch (Exception $e) {
                        // Se a coluna não existir, usar session
                        $_SESSION['profile_banner'] = $target_file;
                    }
                    $message = "Banner atualizado com sucesso!";
                } else {
                    $message = "Erro ao fazer upload do banner.";
                }
            } else {
                $message = "Arquivo muito grande. Tamanho máximo: 10MB.";
            }
        } else {
            $message = "Tipo de arquivo não permitido para banner.";
        }
    }
}

// Atualizar informações do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_bio = trim($_POST['customer_bio']);
    $customer_handle = trim($_POST['customer_handle']);
    
    if (!empty($customer_name)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET CustomerName = ?, CustomerBio = ?, CustomerHandle = ? WHERE CustomerID = ?");
        if ($stmt->execute([$customer_name, $customer_bio, $customer_handle, $user['CustomerID']])) {
            $message = "Perfil atualizado com sucesso!";
            $user = getCurrentUser($pdo);
        } else {
            $message = "Erro ao atualizar perfil.";
        }
    } else {
        $message = "O nome não pode estar vazio.";
    }
}

// Buscar fangames do usuário
$stmt = $pdo->prepare("
    SELECT * FROM fangames 
    WHERE DeveloperID = ? 
    ORDER BY CreatedAt DESC 
    LIMIT 6
");
$stmt->execute([$user['CustomerID']]);
$userGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | BONFIRE GAMES</title>
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
            background-size: cover;
            background-position: center;
        }
        
        /* Profile Header */
        .profile-header {
            position: relative;
            margin-bottom: 30px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .profile-banner {
            height: 200px;
            background: linear-gradient(135deg, var(--gamejolt-green), var(--gamejolt-purple));
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            padding: 20px;
        }
        
        .banner-upload-btn {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .banner-upload-btn:hover {
            background: rgba(0, 0, 0, 0.9);
        }
        
        .profile-info-section {
            background: var(--secondary);
            padding: 30px;
            padding-top: 80px;
            position: relative;
        }
        
        .profile-avatar {
            position: absolute;
            top: -60px;
            left: 30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid var(--secondary);
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            background-size: cover;
            background-position: center;
        }
        
        .avatar-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .profile-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .profile-text {
            flex: 1;
        }
        
        .profile-name {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--light);
        }
        
        .profile-handle {
            color: var(--gamejolt-green);
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .profile-bio {
            color: var(--gray);
            line-height: 1.6;
            max-width: 600px;
        }
        
        .profile-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--gamejolt-green);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
        }
        
        .profile-actions {
            display: flex;
            gap: 10px;
        }
        
        .edit-profile-btn {
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
        }
        
        .edit-profile-btn:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        /* Content Sections */
        .content-section {
            background: var(--secondary);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            font-size: 1.3rem;
            color: var(--light);
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--gamejolt-green);
        }
        
        .section-actions {
            display: flex;
            gap: 10px;
        }
        
        .publish-game-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .publish-game-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            background: linear-gradient(135deg, var(--gamejolt-green), var(--gamejolt-purple));
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
            background-color: var(--gamejolt-green);
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
        
        .game-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.85rem;
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
            background-color: var(--secondary);
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
            background-color: var(--gamejolt-green);
            color: white;
        }
        
        .save-btn:hover {
            background-color: #5ab869;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(107, 198, 121, 0.2);
            border: 1px solid var(--gamejolt-green);
            color: var(--gamejolt-green);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        /* Upload Forms */
        .upload-form {
            background: var(--dark);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
        }
        
        .file-input {
            margin-bottom: 10px;
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
            
            .profile-details {
                flex-direction: column;
                gap: 20px;
            }
            
            .profile-actions {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .profile-stats {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .games-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                top: -50px;
            }
            
            .profile-info-section {
                padding-top: 70px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .section-actions {
                width: 100%;
                justify-content: center;
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
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Fórum</span>
                </a>
                <a href="profile.php" class="nav-link active">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Meu Perfil</div>
                <div class="header-actions">
                    <div class="header-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="user-avatar" style="background-image: url('<?php echo !empty($user['ProfilePhoto']) ? $user['ProfilePhoto'] : ''; ?>')">
                        <?php echo empty($user['ProfilePhoto']) ? '<i class="fas fa-user"></i>' : ''; ?>
                    </div>
                </div>
            </div>
            
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo strpos($message, 'Erro') !== false ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-banner" style="background-image: url('<?php echo !empty($user['ProfileBanner']) ? $user['ProfileBanner'] : (isset($_SESSION['profile_banner']) ? $_SESSION['profile_banner'] : ''); ?>')">
                    <div class="banner-overlay">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="profile_banner" accept="image/*" style="display: none;" id="bannerInput">
                            <button type="button" class="banner-upload-btn" onclick="document.getElementById('bannerInput').click()">
                                <i class="fas fa-camera"></i>
                                Alterar Banner
                            </button>
                            <input type="hidden" name="upload_banner" value="1">
                            <button type="submit" style="display: none;" id="bannerSubmit"></button>
                        </form>
                    </div>
                </div>
                
                <div class="profile-info-section">
                    <div class="profile-avatar" style="background-image: url('<?php echo !empty($user['ProfilePhoto']) ? $user['ProfilePhoto'] : ''; ?>'); font-size: <?php echo empty($user['ProfilePhoto']) ? '48px' : '0'; ?>">
                        <?php echo empty($user['ProfilePhoto']) ? ($user['ProfileIcon'] ?? '🔥') : ''; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="profile_photo" accept="image/*" style="display: none;" id="avatarInput">
                            <button type="button" class="avatar-upload-btn" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="hidden" name="upload_profile" value="1">
                            <button type="submit" style="display: none;" id="avatarSubmit"></button>
                        </form>
                    </div>
                    
                    <div class="profile-details">
                        <div class="profile-text">
                            <h1 class="profile-name"><?php echo htmlspecialchars($user['CustomerName']); ?></h1>
                            <div class="profile-handle">@<?php echo htmlspecialchars($user['CustomerHandle'] ?? 'usuário'); ?></div>
                            <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user['CustomerBio'] ?? 'Nenhuma biografia definida.')); ?></p>
                            
                            <div class="profile-stats">
                                <div class="stat">
                                    <div class="stat-number"><?php echo $stats['post_count'] ?? 0; ?></div>
                                    <div class="stat-label">Posts</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number"><?php echo $stats['game_count'] ?? 0; ?></div>
                                    <div class="stat-label">Fangames</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number">0</div>
                                    <div class="stat-label">Seguidores</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button class="edit-profile-btn" onclick="openEditModal()">
                                <i class="fas fa-edit"></i>
                                Editar Perfil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Games Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-gamepad"></i>
                        Meus Fangames
                    </h2>
                    <div class="section-actions">
                        <button class="publish-game-btn" onclick="window.location.href='add_fangame.php'">
                            <i class="fas fa-plus"></i>
                            Publicar Fangame
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($userGames)): ?>
                    <div class="games-grid">
                        <?php foreach ($userGames as $game): ?>
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
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--gray);">
                        <i class="fas fa-gamepad" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Você ainda não publicou nenhum fangame.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activity Section -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Atividade Recente
                </h2>
                <div style="text-align: center; padding: 30px; color: var(--gray);">
                    <i class="fas fa-history" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Nenhuma atividade recente para exibir.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Perfil -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Editar Perfil</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="customer_name">Nome *</label>
                    <input type="text" id="customer_name" name="customer_name" 
                           value="<?php echo htmlspecialchars($user['CustomerName']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_handle">Handle (@username)</label>
                    <input type="text" id="customer_handle" name="customer_handle" 
                           value="<?php echo htmlspecialchars($user['CustomerHandle'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_bio">Biografia</label>
                    <textarea id="customer_bio" name="customer_bio" placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($user['CustomerBio'] ?? ''); ?></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" name="update_profile" class="save-btn">
                        <i class="fas fa-save"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openGame(gameId) {
            window.location.href = 'game.php?id=' + gameId;
        }

        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Auto-submit forms quando uma imagem é selecionada
        document.getElementById('avatarInput').addEventListener('change', function() {
            document.getElementById('avatarSubmit').click();
        });

        document.getElementById('bannerInput').addEventListener('change', function() {
            document.getElementById('bannerSubmit').click();
        });

        // Preview de imagem antes do upload
        function previewImage(input, previewElement) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.style.backgroundImage = `url(${e.target.result})`;
                    if (previewElement.classList.contains('profile-avatar')) {
                        previewElement.innerHTML = '<button type="button" class="avatar-upload-btn" onclick="document.getElementById(\'avatarInput\').click()"><i class="fas fa-camera"></i></button>';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.getElementById('avatarInput').addEventListener('change', function() {
            previewImage(this, document.querySelector('.profile-avatar'));
        });

        document.getElementById('bannerInput').addEventListener('change', function() {
            previewImage(this, document.querySelector('.profile-banner'));
        });

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>