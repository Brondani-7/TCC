<?php
// add_fangame.php - VERSÃO CORRIGIDA
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);
$message = '';
$error = '';

// Criar diretórios de upload
createUploadDirs();

// PROCESSAR O FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Coletar dados do formulário
    $gameTitle = trim($_POST['game_title'] ?? '');
    $gameDescription = trim($_POST['game_description'] ?? '');
    $franchise = trim($_POST['franchise'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $status = $_POST['status'] ?? 'Em Desenvolvimento';
    $tags = trim($_POST['tags'] ?? '');
    $systemRequirements = trim($_POST['system_requirements'] ?? '');
    $releaseDate = $_POST['release_date'] ?? null;
    $downloadLink = trim($_POST['download_link'] ?? '');
    
    // Validações básicas
    if (empty($gameTitle) || empty($gameDescription) || empty($franchise) || empty($genre)) {
        $error = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            // Processar upload da capa
            $gameCover = null;
            if (isset($_FILES['game_cover']) && $_FILES['game_cover']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['game_cover']['name']);
                $extension = strtolower($fileInfo['extension']);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $allowedExtensions) && $_FILES['game_cover']['size'] <= 5 * 1024 * 1024) {
                    $filename = 'cover_' . $user['CustomerID'] . '_' . time() . '.' . $extension;
                    $targetPath = 'uploads/games/covers/' . $filename;
                    
                    if (move_uploaded_file($_FILES['game_cover']['tmp_name'], $targetPath)) {
                        $gameCover = $targetPath;
                    }
                }
            }
            
            // Processar upload do arquivo do jogo
            $gameFile = null;
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['game_file']['name']);
                $extension = strtolower($fileInfo['extension']);
                $allowedExtensions = ['zip', 'rar', '7z'];
                
                if (in_array($extension, $allowedExtensions) && $_FILES['game_file']['size'] <= 100 * 1024 * 1024) {
                    $filename = 'game_' . $user['CustomerID'] . '_' . time() . '.' . $extension;
                    $targetPath = 'uploads/games/files/' . $filename;
                    
                    if (move_uploaded_file($_FILES['game_file']['tmp_name'], $targetPath)) {
                        $gameFile = $targetPath;
                    }
                }
            }
            
            // VERIFICAR QUAIS COLUNAS EXISTEM NA TABELA
            $columns = [
                'GameTitle', 'GameDescription', 'DeveloperID', 'Franchise', 'Genre', 'Status'
            ];
            $values = [
                $gameTitle, $gameDescription, $user['CustomerID'], $franchise, $genre, $status
            ];
            
            // Verificar colunas opcionais
            $optionalColumns = ['Tags', 'SystemRequirements', 'ReleaseDate', 'DownloadLink', 'GameCover', 'GameFile'];
            $optionalValues = [$tags, $systemRequirements, $releaseDate ?: null, $downloadLink ?: null, $gameCover, $gameFile];
            
            for ($i = 0; $i < count($optionalColumns); $i++) {
                try {
                    // Testar se a coluna existe
                    $test = $pdo->query("SELECT " . $optionalColumns[$i] . " FROM fangames LIMIT 1");
                    $columns[] = $optionalColumns[$i];
                    $values[] = $optionalValues[$i];
                } catch (Exception $e) {
                    // Coluna não existe, ignorar
                    continue;
                }
            }
            
            // Construir a query dinamicamente
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            $columnList = implode(', ', $columns);
            
            $sql = "INSERT INTO fangames ($columnList) VALUES ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($values);
            
            if ($success) {
                $message = "Fangame cadastrado com sucesso!";
                // Redirecionar após 2 segundos
                echo "<script>setTimeout(() => window.location.href = 'profile.php', 2000);</script>";
            } else {
                $error = "Erro ao cadastrar fangame no banco de dados.";
            }
            
        } catch (Exception $e) {
            $error = "Erro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Fangame | BONFIRE GAMES</title>
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
        
        .form-container {
            background: var(--secondary);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 800px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert.success {
            background-color: rgba(107, 198, 121, 0.2);
            border: 1px solid var(--gamejolt-green);
            color: var(--gamejolt-green);
        }
        
        .alert.error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
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
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: var(--light);
            outline: none;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .file-upload {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover {
            border-color: var(--gamejolt-green);
        }
        
        .file-upload input {
            display: none;
        }
        
        .file-upload i {
            font-size: 2rem;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-cancel {
            background: transparent;
            color: var(--gray);
            border: 1px solid var(--gray);
        }
        
        .btn-submit {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .btn-submit:hover {
            background: #5ab869;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .sidebar span {
                display: none;
            }
            .main-content {
                margin-left: 80px;
            }
            .form-row {
                grid-template-columns: 1fr;
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
                <div class="page-title">Adicionar Fangame</div>
                <div class="user-avatar" style="background-image: url('<?php echo $user['ProfilePhoto'] ?? ''; ?>')">
                    <?php if(empty($user['ProfilePhoto'])): ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mensagens -->
            <?php if($message): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulário -->
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <!-- Informações Básicas -->
                    <div class="form-group">
                        <label for="game_title">Título do Fangame *</label>
                        <input type="text" id="game_title" name="game_title" 
                               value="<?php echo htmlspecialchars($_POST['game_title'] ?? ''); ?>" 
                               required placeholder="Digite o título do fangame">
                    </div>
                    
                    <div class="form-group">
                        <label for="game_description">Descrição *</label>
                        <textarea id="game_description" name="game_description" required
                                  placeholder="Descreva seu fangame..."><?php echo htmlspecialchars($_POST['game_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="franchise">Franquia *</label>
                            <input type="text" id="franchise" name="franchise" 
                                   value="<?php echo htmlspecialchars($_POST['franchise'] ?? ''); ?>" 
                                   required placeholder="Ex: Pokémon, Mario, Sonic">
                        </div>
                        
                        <div class="form-group">
                            <label for="genre">Gênero *</label>
                            <input type="text" id="genre" name="genre" 
                                   value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>" 
                                   required placeholder="Ex: RPG, Aventura, Plataforma">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Em Desenvolvimento">Em Desenvolvimento</option>
                                <option value="Lançado">Lançado</option>
                                <option value="Pausado">Pausado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="release_date">Data de Lançamento</label>
                            <input type="date" id="release_date" name="release_date" 
                                   value="<?php echo $_POST['release_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tags">Tags (opcional)</label>
                        <input type="text" id="tags" name="tags" 
                               value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>" 
                               placeholder="rpg-maker, fangame, hack-rom">
                    </div>
                    
                    <!-- Uploads -->
                    <div class="form-group">
                        <label>Capa do Jogo (opcional)</label>
                        <div class="file-upload" onclick="document.getElementById('game_cover').click()">
                            <i class="fas fa-image"></i>
                            <div>Clique para selecionar a capa</div>
                            <small>PNG, JPG, GIF, WebP (max 5MB)</small>
                            <input type="file" id="game_cover" name="game_cover" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Arquivo do Jogo (opcional)</label>
                        <div class="file-upload" onclick="document.getElementById('game_file').click()">
                            <i class="fas fa-file-archive"></i>
                            <div>Clique para selecionar o arquivo</div>
                            <small>ZIP, RAR, 7Z (max 100MB)</small>
                            <input type="file" id="game_file" name="game_file" accept=".zip,.rar,.7z">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="download_link">Link de Download Alternativo (opcional)</label>
                        <input type="url" id="download_link" name="download_link" 
                               value="<?php echo htmlspecialchars($_POST['download_link'] ?? ''); ?>" 
                               placeholder="https://...">
                    </div>
                    
                    <div class="form-group">
                        <label for="system_requirements">Requisitos do Sistema (opcional)</label>
                        <textarea id="system_requirements" name="system_requirements" 
                                  placeholder="Requisitos mínimos..."><?php echo htmlspecialchars($_POST['system_requirements'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Ações -->
                    <div class="form-actions">
                        <a href="profile.php" class="btn btn-cancel">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-plus"></i> Adicionar Fangame
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Feedback visual para uploads
        document.getElementById('game_cover').addEventListener('change', function(e) {
            if (this.files[0]) {
                const container = this.parentElement;
                container.style.borderColor = '#6bc679';
                container.innerHTML = '<i class="fas fa-check" style="color: #6bc679;"></i><div>Arquivo selecionado: ' + this.files[0].name + '</div>';
            }
        });
        
        document.getElementById('game_file').addEventListener('change', function(e) {
            if (this.files[0]) {
                const container = this.parentElement;
                container.style.borderColor = '#6bc679';
                container.innerHTML = '<i class="fas fa-check" style="color: #6bc679;"></i><div>Arquivo selecionado: ' + this.files[0].name + '</div>';
            }
        });
    </script>
</body>
</html>