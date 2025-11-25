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
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--light);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            border-radius: 50%;
            cursor: pointer;
            background-size: cover;
            background-position: center;
            border: 2px solid var(--gamejolt-green);
        }
        
        /* Form Container */
        .form-container {
            background: var(--secondary);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--light);
            font-size: 1.1rem;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 15px 20px;
            background: var(--dark);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: var(--light);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            border-color: var(--gamejolt-green);
            box-shadow: 0 0 0 3px rgba(107, 198, 121, 0.2);
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
            line-height: 1.5;
        }
        
        .form-help {
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .file-upload {
            border: 3px dashed rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
        }
        
        .file-upload:hover {
            border-color: var(--gamejolt-green);
            background: rgba(107, 198, 121, 0.05);
            transform: translateY(-2px);
        }
        
        .file-upload.dragover {
            border-color: var(--gamejolt-green);
            background: rgba(107, 198, 121, 0.1);
        }
        
        .file-upload i {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 15px;
        }
        
        .file-upload input {
            display: none;
        }
        
        .file-preview {
            margin-top: 20px;
            display: none;
            text-align: center;
        }
        
        .file-preview img {
            max-width: 250px;
            max-height: 180px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            min-width: 140px;
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--gamejolt-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5ab869;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(107, 198, 121, 0.3);
        }
        
        .btn-secondary {
            background: var(--gray);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #657c6a;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border: 2px solid var(--success);
            color: var(--success);
        }
        
        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .suggestion-tag {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .suggestion-tag:hover {
            background: var(--gamejolt-green);
            color: white;
            transform: translateY(-2px);
        }
        
        .download-options {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 25px;
            margin-top: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .download-option-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: var(--gamejolt-green);
            font-size: 1.1rem;
        }
        
        .server-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            color: var(--gray);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .required {
            color: var(--gamejolt-green);
        }
        
        .upload-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        /* Responsive */
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
                padding: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .file-upload {
                padding: 20px;
            }
            
            .download-options {
                padding: 20px;
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
                <a href="perfil.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Publicar Fangame</div>
                <div class="header-actions">
                    <a href="fangames.php" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <div class="user-avatar" style="background-image: url('<?php echo htmlspecialchars($user['ProfilePhoto'] ?? ''); ?>')" 
                         onclick="window.location.href='perfil.php'">
                        <?php if(empty($user['ProfilePhoto'])): ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Informações do servidor -->
            <div class="server-info">
                <strong><i class="fas fa-info-circle"></i> Informações do Servidor:</strong><br>
                Upload: <?php echo ini_get('upload_max_filesize'); ?> | 
                POST: <?php echo ini_get('post_max_size'); ?> | 
                Memória: <?php echo ini_get('memory_limit'); ?>
            </div>
            
            <!-- Form Container -->
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-lg"></i>
                        <div><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="add_fangame.php" enctype="multipart/form-data" id="fangameForm">
                    <!-- Informações Básicas -->
                    <div class="form-group">
                        <label class="form-label" for="game_title">
                            Título do Fangame <span class="required">*</span>
                        </label>
                        <input type="text" id="game_title" name="game_title" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['game_title'] ?? ''); ?>" 
                               placeholder="Ex: Super Mario Bros: The Lost Levels" required
                               maxlength="255">
                        <div class="form-help">Máximo 255 caracteres</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="game_description">
                            Descrição do Jogo <span class="required">*</span>
                        </label>
                        <textarea id="game_description" name="game_description" class="form-textarea" 
                                  placeholder="Descreva seu fangame em detalhes: história, jogabilidade, características especiais, etc..." 
                                  required minlength="50"><?php echo htmlspecialchars($_POST['game_description'] ?? ''); ?></textarea>
                        <div class="form-help">Mínimo 50 caracteres. <span id="charCount">0</span> caracteres digitados</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="franchise">
                            Franquia <span class="required">*</span>
                        </label>
                        <input type="text" id="franchise" name="franchise" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['franchise'] ?? ''); ?>" 
                               placeholder="Ex: Mario, Sonic, Zelda, Pokémon..." required
                               maxlength="100">
                        <?php if (!empty($existingFranchises)): ?>
                            <div class="form-help">Sugestões baseadas em fangames existentes:</div>
                            <div class="suggestions">
                                <?php foreach ($existingFranchises as $franchiseOption): ?>
                                    <div class="suggestion-tag" onclick="document.getElementById('franchise').value = '<?php echo htmlspecialchars($franchiseOption); ?>'">
                                        <?php echo htmlspecialchars($franchiseOption); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="genre">
                            Gênero <span class="required">*</span>
                        </label>
                        <input type="text" id="genre" name="genre" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>" 
                               placeholder="Ex: Plataforma, RPG, Aventura, Luta..." required
                               maxlength="100">
                        <?php if (!empty($existingGenres)): ?>
                            <div class="form-help">Sugestões baseadas em fangames existentes:</div>
                            <div class="suggestions">
                                <?php foreach ($existingGenres as $genreOption): ?>
                                    <div class="suggestion-tag" onclick="document.getElementById('genre').value = '<?php echo htmlspecialchars($genreOption); ?>'">
                                        <?php echo htmlspecialchars($genreOption); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="status">
                            Status do Desenvolvimento <span class="required">*</span>
                        </label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="Em Desenvolvimento" <?php echo ($_POST['status'] ?? '') === 'Em Desenvolvimento' ? 'selected' : ''; ?>>🚧 Em Desenvolvimento</option>
                            <option value="Lançado" <?php echo ($_POST['status'] ?? '') === 'Lançado' ? 'selected' : ''; ?>>🎉 Lançado</option>
                            <option value="Pausado" <?php echo ($_POST['status'] ?? '') === 'Pausado' ? 'selected' : ''; ?>>⏸️ Pausado</option>
                            <option value="Cancelado" <?php echo ($_POST['status'] ?? '') === 'Cancelado' ? 'selected' : ''; ?>>❌ Cancelado</option>
                        </select>
                    </div>
                    
                    <!-- Opções de Download -->
                    <div class="form-group">
                        <label class="form-label">Opções de Download <span class="required">*</span></label>
                        <div class="download-options">
                            <div class="download-option-title">
                                <i class="fas fa-download"></i> Escolha pelo menos uma opção de download:
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 25px;">
                                <label class="form-label" for="download_link">
                                    <i class="fas fa-link"></i> Link de Download Externo
                                </label>
                                <input type="url" id="download_link" name="download_link" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['download_link'] ?? ''); ?>" 
                                       placeholder="https://exemplo.com/download/meu-jogo">
                                <div class="form-help">Forneça um link externo (Google Drive, MediaFire, Mega, etc.)</div>
                            </div>
                            
                            <div style="text-align: center; margin: 20px 0; color: var(--gray); font-weight: bold;">
                                <i class="fas fa-arrows-alt-v"></i> OU
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">
                                    <i class="fas fa-upload"></i> Upload do Arquivo do Jogo
                                </label>
                                <div class="file-upload" id="gameFileUpload" onclick="document.getElementById('game_file').click()">
                                    <i class="fas fa-file-archive"></i>
                                    <p><strong>Clique para selecionar o arquivo do jogo</strong></p>
                                    <p class="form-help">Formatos permitidos: ZIP, RAR, 7Z, EXE</p>
                                    <p class="form-help">Sem limite de tamanho</p>
                                    <input type="file" id="game_file" name="game_file" accept=".zip,.rar,.7z,.exe" onchange="showGameFileName(this)">
                                </div>
                                <div id="game_file_name" class="upload-info"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Capa do Jogo -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i> Capa do Jogo (Opcional)
                        </label>
                        <div class="file-upload" id="coverUpload" onclick="document.getElementById('game_cover').click()">
                            <i class="fas fa-image"></i>
                            <p><strong>Clique para selecionar a capa do jogo</strong></p>
                            <p class="form-help">Formatos: JPG, PNG, GIF, WebP</p>
                            <p class="form-help">Recomendado: 400x300px ou proporção similar</p>
                            <input type="file" id="game_cover" name="game_cover" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <div id="cover_preview" class="file-preview"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Limpar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-rocket"></i> Publicar Fangame
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Contador de caracteres para descrição
        const descriptionTextarea = document.getElementById('game_description');
        const charCount = document.getElementById('charCount');
        
        descriptionTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            if (this.value.length < 50) {
                charCount.style.color = 'var(--danger)';
            } else {
                charCount.style.color = 'var(--gamejolt-green)';
            }
        });
        
        // Inicializar contador
        charCount.textContent = descriptionTextarea.value.length;
        if (descriptionTextarea.value.length < 50) {
            charCount.style.color = 'var(--danger)';
        }

        // Preview da imagem
        function previewImage(input) {
            const preview = document.getElementById('cover_preview');
            const uploadArea = document.getElementById('coverUpload');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview da Capa">';
                    preview.style.display = 'block';
                    uploadArea.style.borderColor = 'var(--gamejolt-green)';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                uploadArea.style.borderColor = '';
            }
        }
        
        // Mostrar nome do arquivo do jogo
        function showGameFileName(input) {
            const fileName = document.getElementById('game_file_name');
            const uploadArea = document.getElementById('gameFileUpload');
            
            if (input.files && input.files[0]) {
                const fileSize = (input.files[0].size / (1024 * 1024)).toFixed(2);
                fileName.innerHTML = `<strong>Arquivo selecionado:</strong> ${input.files[0].name} (${fileSize} MB)`;
                uploadArea.style.borderColor = 'var(--gamejolt-green)';
                
                // Limpar link de download se arquivo for selecionado
                document.getElementById('download_link').value = '';
            } else {
                fileName.innerHTML = '';
                uploadArea.style.borderColor = '';
            }
        }
        
        // Limpar link de download quando arquivo for selecionado e vice-versa
        document.getElementById('download_link').addEventListener('input', function() {
            if (this.value.trim() !== '') {
                document.getElementById('game_file').value = '';
                document.getElementById('game_file_name').innerHTML = '';
                document.getElementById('gameFileUpload').style.borderColor = '';
            }
        });
        
        // Drag and drop para uploads
        function setupDragAndDrop(uploadArea, inputElement) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                inputElement.files = e.dataTransfer.files;
                
                // Disparar evento change manualmente
                const event = new Event('change');
                inputElement.dispatchEvent(event);
            });
        }
        
        // Aplicar drag and drop
        setupDragAndDrop(document.getElementById('coverUpload'), document.getElementById('game_cover'));
        setupDragAndDrop(document.getElementById('gameFileUpload'), document.getElementById('game_file'));
        
        // Validação do formulário
        document.getElementById('fangameForm').addEventListener('submit', function(e) {
            const title = document.getElementById('game_title').value.trim();
            const description = document.getElementById('game_description').value.trim();
            const franchise = document.getElementById('franchise').value.trim();
            const genre = document.getElementById('genre').value.trim();
            const downloadLink = document.getElementById('download_link').value.trim();
            const gameFile = document.getElementById('game_file').files.length;
            const submitBtn = document.getElementById('submitBtn');
            
            let isValid = true;
            let errorMessage = '';
            
            // Validar campos obrigatórios
            if (!title) {
                isValid = false;
                errorMessage = 'O título do jogo é obrigatório.';
            } else if (!description || description.length < 50) {
                isValid = false;
                errorMessage = 'A descrição deve ter pelo menos 50 caracteres.';
            } else if (!franchise) {
                isValid = false;
                errorMessage = 'A franquia é obrigatória.';
            } else if (!genre) {
                isValid = false;
                errorMessage = 'O gênero é obrigatório.';
            } else if (!downloadLink && !gameFile) {
                isValid = false;
                errorMessage = 'Forneça pelo menos um link de download ou faça upload do arquivo do jogo.';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('❌ ' + errorMessage);
                return;
            }
            
            // Mostrar loading no botão
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando...';
            submitBtn.disabled = true;
        });
        
        // Reset do formulário
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            document.getElementById('cover_preview').style.display = 'none';
            document.getElementById('game_file_name').innerHTML = '';
            document.getElementById('charCount').textContent = '0';
            document.getElementById('charCount').style.color = 'var(--danger)';
            
            // Resetar estilos dos upload areas
            document.getElementById('coverUpload').style.borderColor = '';
            document.getElementById('gameFileUpload').style.borderColor = '';
            
            // Reativar botão de submit se estiver desabilitado
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-rocket"></i> Publicar Fangame';
            submitBtn.disabled = false;
        });
    </script>
</body>
</html>