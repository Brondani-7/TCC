<?php
// add_fangame.php
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);
$message = '';

// Processar o formulário de adição de fangame
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameTitle = trim($_POST['game_title']);
    $gameDescription = trim($_POST['game_description']);
    $franchise = trim($_POST['franchise']);
    $genre = trim($_POST['genre']);
    $status = $_POST['status'];
    $tags = trim($_POST['tags']);
    $systemRequirements = trim($_POST['system_requirements']);
    $releaseDate = $_POST['release_date'] ?: null;
    
    try {
        // Validações básicas
        if (empty($gameTitle)) {
            throw new Exception("O título do jogo é obrigatório.");
        }

        // Processar upload da capa
        $gameCover = null;
        if (isset($_FILES['game_cover']) && $_FILES['game_cover']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['game_cover']['type'];
            
            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = 'uploads/games/covers/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES['game_cover']['name'], PATHINFO_EXTENSION);
                $filename = 'cover_' . $user['CustomerID'] . '_' . time() . '.' . $fileExtension;
                $targetFile = $uploadDir . $filename;
                
                if ($_FILES['game_cover']['size'] <= 5 * 1024 * 1024) { // 5MB
                    if (move_uploaded_file($_FILES['game_cover']['tmp_name'], $targetFile)) {
                        $gameCover = $targetFile;
                    }
                }
            }
        }

        // Processar upload do arquivo do jogo
        $gameFile = null;
        if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/games/files/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['game_file']['name'], PATHINFO_EXTENSION);
            $filename = 'game_' . $user['CustomerID'] . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDir . $filename;
            
            if ($_FILES['game_file']['size'] <= 100 * 1024 * 1024) { // 100MB
                if (move_uploaded_file($_FILES['game_file']['tmp_name'], $targetFile)) {
                    $gameFile = $targetFile;
                }
            }
        }

        // Inserir no banco de dados
        $stmt = $pdo->prepare("
            INSERT INTO fangames 
            (GameTitle, GameDescription, DeveloperID, Franchise, Genre, Status, Tags, 
             GameFile, GameCover, SystemRequirements, ReleaseDate, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $gameTitle,
            $gameDescription,
            $user['CustomerID'],
            $franchise,
            $genre,
            $status,
            $tags,
            $gameFile,
            $gameCover,
            $systemRequirements,
            $releaseDate
        ]);

        $message = "Fangame publicado com sucesso!";
        $_SESSION['success_message'] = $message;
        header('Location: fangames.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Fangame | BONFIRE GAMES</title>
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
        
        /* Sidebar - Manter igual ao fangames.php */
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
        
        /* Form Styles */
        .form-container {
            background: var(--secondary);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--light);
            font-size: 1rem;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 15px;
            background: var(--dark);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--light);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            border-color: var(--gamejolt-green);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            display: block;
            padding: 12px 15px;
            background: var(--dark);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover {
            border-color: var(--gamejolt-green);
            background: rgba(107, 198, 121, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5ab869;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .form-help {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 5px;
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
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
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
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Publicar Fangame</div>
                <a href="fangames.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="add_fangame.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="game_title" class="form-label">Título do Fangame *</label>
                        <input type="text" id="game_title" name="game_title" class="form-input" 
                               placeholder="Digite o título do seu fangame" required
                               value="<?= isset($_POST['game_title']) ? htmlspecialchars($_POST['game_title']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="game_description" class="form-label">Descrição *</label>
                        <textarea id="game_description" name="game_description" class="form-textarea" 
                                  placeholder="Descreva seu fangame, história, características..." required><?= isset($_POST['game_description']) ? htmlspecialchars($_POST['game_description']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="franchise" class="form-label">Franquia Base *</label>
                        <input type="text" id="franchise" name="franchise" class="form-input" 
                               placeholder="Ex: Pokémon, Mario, Zelda, etc." required
                               value="<?= isset($_POST['franchise']) ? htmlspecialchars($_POST['franchise']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="genre" class="form-label">Gênero *</label>
                        <select id="genre" name="genre" class="form-select" required>
                            <option value="">Selecione um gênero</option>
                            <option value="RPG" <?= (isset($_POST['genre']) && $_POST['genre'] == 'RPG') ? 'selected' : '' ?>>RPG</option>
                            <option value="Ação" <?= (isset($_POST['genre']) && $_POST['genre'] == 'Ação') ? 'selected' : '' ?>>Ação</option>
                            <option value="Aventura" <?= (isset($_POST['genre']) && $_POST['genre'] == 'Aventura') ? 'selected' : '' ?>>Aventura</option>
                            <option value="Estratégia" <?= (isset($_POST['genre']) && $_POST['genre'] == 'Estratégia') ? 'selected' : '' ?>>Estratégia</option>
                            <option value="Esporte" <?= (isset($_POST['genre']) && $_POST['genre'] == 'Esporte') ? 'selected' : '' ?>>Esporte</option>
                            <option value="Outro" <?= (isset($_POST['genre']) && $_POST['genre'] == 'Outro') ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status do Desenvolvimento *</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="">Selecione o status</option>
                            <option value="Em Desenvolvimento" <?= (isset($_POST['status']) && $_POST['status'] == 'Em Desenvolvimento') ? 'selected' : '' ?>>Em Desenvolvimento</option>
                            <option value="Lançado" <?= (isset($_POST['status']) && $_POST['status'] == 'Lançado') ? 'selected' : '' ?>>Lançado</option>
                            <option value="Pausado" <?= (isset($_POST['status']) && $_POST['status'] == 'Pausado') ? 'selected' : '' ?>>Pausado</option>
                            <option value="Cancelado" <?= (isset($_POST['status']) && $_POST['status'] == 'Cancelado') ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" id="tags" name="tags" class="form-input" 
                               placeholder="Ex: fangame, rpg, 2d, pixelart (separados por vírgula)"
                               value="<?= isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : '' ?>">
                        <div class="form-help">Palavras-chave para ajudar na busca do seu jogo</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="game_cover" class="form-label">Capa do Jogo</label>
                        <div class="file-input-container">
                            <input type="file" id="game_cover" name="game_cover" class="file-input" accept="image/*">
                            <label for="game_cover" class="file-input-label">
                                <i class="fas fa-image"></i>
                                <span id="cover-label">Selecionar imagem de capa (max. 5MB)</span>
                            </label>
                        </div>
                        <div class="form-help">Formatos: JPG, PNG, GIF, WebP</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="game_file" class="form-label">Arquivo do Jogo (ZIP/RAR)</label>
                        <div class="file-input-container">
                            <input type="file" id="game_file" name="game_file" class="file-input" accept=".zip,.rar,.7z">
                            <label for="game_file" class="file-input-label">
                                <i class="fas fa-file-archive"></i>
                                <span id="file-label">Selecionar arquivo do jogo (max. 100MB)</span>
                            </label>
                        </div>
                        <div class="form-help">Compacte seu jogo em ZIP, RAR ou 7Z</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="system_requirements" class="form-label">Requisitos do Sistema</label>
                        <textarea id="system_requirements" name="system_requirements" class="form-textarea" 
                                  placeholder="Especifique os requisitos mínimos para rodar o jogo..."><?= isset($_POST['system_requirements']) ? htmlspecialchars($_POST['system_requirements']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="release_date" class="form-label">Data de Lançamento (Opcional)</label>
                        <input type="date" id="release_date" name="release_date" class="form-input"
                               value="<?= isset($_POST['release_date']) ? htmlspecialchars($_POST['release_date']) : '' ?>">
                    </div>
                    
                    <div class="form-actions">
                        <a href="fangames.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Publicar Fangame
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Atualizar labels dos arquivos
        document.getElementById('game_cover').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Selecionar imagem de capa (max. 5MB)';
            document.getElementById('cover-label').textContent = fileName;
        });
        
        document.getElementById('game_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Selecionar arquivo do jogo (max. 100MB)';
            document.getElementById('file-label').textContent = fileName;
        });
        
        // Validação básica do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('game_title').value.trim();
            const description = document.getElementById('game_description').value.trim();
            const franchise = document.getElementById('franchise').value.trim();
            const genre = document.getElementById('genre').value;
            const status = document.getElementById('status').value;
            
            if (!title || !description || !franchise || !genre || !status) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
        });
    </script>
</body>
</html>