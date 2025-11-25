<?php
// edit_fangame.php
require_once 'config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser($pdo);

// Verificar se o ID do jogo foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fangames.php');
    exit;
}

$gameId = intval($_GET['id']);
$game = getFangame($pdo, $gameId);

// Verificar se o usuário é o desenvolvedor do jogo
if (!$game || !isGameDeveloper($pdo, $gameId, $user['CustomerID'])) {
    header('Location: game.php?id=' . $gameId);
    exit;
}

// Buscar screenshots existentes
$screenshots = getGameScreenshots($pdo, $gameId);

// Processar formulário de atualização
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_game'])) {
        // Atualizar informações do jogo
        $updateData = [
            'GameTitle' => $_POST['game_title'],
            'GameDescription' => $_POST['game_description'],
            'Franchise' => $_POST['franchise'],
            'Genre' => $_POST['genre'],
            'Status' => $_POST['status'],
            'Tags' => $_POST['tags'],
            'DownloadLink' => $_POST['download_link'],
            'FileSize' => $_POST['file_size'],
            'SystemRequirements' => $_POST['system_requirements'],
            'ReleaseDate' => $_POST['release_date']
        ];
        
        // Verificar se a função updateFangame existe
        if (function_exists('updateFangame')) {
            if (updateFangame($pdo, $gameId, $updateData)) {
                $message = 'Fangame atualizado com sucesso!';
                $messageType = 'success';
                // Atualizar dados locais
                $game = getFangame($pdo, $gameId);
            } else {
                $message = 'Erro ao atualizar fangame.';
                $messageType = 'error';
            }
        } else {
            // Fallback: atualização manual se a função não existir
            try {
                $stmt = $pdo->prepare("
                    UPDATE fangames SET 
                        GameTitle = ?, 
                        GameDescription = ?, 
                        Franchise = ?, 
                        Genre = ?, 
                        Status = ?, 
                        Tags = ?, 
                        DownloadLink = ?, 
                        FileSize = ?, 
                        SystemRequirements = ?, 
                        ReleaseDate = ?,
                        UpdatedAt = NOW()
                    WHERE GameID = ?
                ");
                
                $result = $stmt->execute([
                    $updateData['GameTitle'],
                    $updateData['GameDescription'],
                    $updateData['Franchise'],
                    $updateData['Genre'],
                    $updateData['Status'],
                    $updateData['Tags'],
                    $updateData['DownloadLink'],
                    $updateData['FileSize'],
                    $updateData['SystemRequirements'],
                    $updateData['ReleaseDate'],
                    $gameId
                ]);
                
                if ($result) {
                    $message = 'Fangame atualizado com sucesso!';
                    $messageType = 'success';
                    $game = getFangame($pdo, $gameId);
                } else {
                    $message = 'Erro ao atualizar fangame.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Erro no servidor: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    
    // Processar upload de screenshots
    if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
        $uploadedCount = 0;
        foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
                $result = handleFileUpload([
                    'name' => $_FILES['screenshots']['name'][$key],
                    'type' => $_FILES['screenshots']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['screenshots']['error'][$key],
                    'size' => $_FILES['screenshots']['size'][$key]
                ], 'uploads/games/screenshots', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                
                if ($result['success']) {
                    if (addGameScreenshot($pdo, $gameId, $result['path'])) {
                        $uploadedCount++;
                    }
                }
            }
        }
        
        if ($uploadedCount > 0) {
            $message .= ($message ? '<br>' : '') . $uploadedCount . ' screenshot(s) adicionada(s) com sucesso!';
            $messageType = $messageType ?: 'success';
            $screenshots = getGameScreenshots($pdo, $gameId);
        }
    }
    
    // Processar remoção de screenshots
    if (isset($_POST['delete_screenshot'])) {
        $screenshotId = intval($_POST['screenshot_id']);
        if (removeGameScreenshot($pdo, $screenshotId, $gameId)) {
            $message = 'Screenshot removida com sucesso!';
            $messageType = 'success';
            $screenshots = getGameScreenshots($pdo, $gameId);
        } else {
            $message = 'Erro ao remover screenshot.';
            $messageType = 'error';
        }
    }
    
    // Processar upload de capa
    if (isset($_FILES['game_cover']) && $_FILES['game_cover']['error'] === UPLOAD_ERR_OK) {
        $result = handleFileUpload($_FILES['game_cover'], 'uploads/games/covers', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        
        if ($result['success']) {
            // Atualizar no banco de dados
            $stmt = $pdo->prepare("UPDATE fangames SET GameCover = ? WHERE GameID = ?");
            if ($stmt->execute([$result['path'], $gameId])) {
                $message .= ($message ? '<br>' : '') . 'Capa atualizada com sucesso!';
                $messageType = $messageType ?: 'success';
                $game['GameCover'] = $result['path'];
            }
        } else {
            $message = 'Erro ao fazer upload da capa: ' . $result['error'];
            $messageType = 'error';
        }
    }
}

// Função auxiliar para buscar dados completos das screenshots
function getScreenshotData($pdo, $gameId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_screenshots WHERE GameID = ? ORDER BY ScreenshotOrder, CreatedAt ASC");
        $stmt->execute([$gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao buscar screenshots: " . $e->getMessage());
        return [];
    }
}

$screenshotData = getScreenshotData($pdo, $gameId);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar <?php echo htmlspecialchars($game['GameTitle']); ?> | BONFIRE GAMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (mantenha todo o CSS anterior) ... */
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
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--secondary);
            color: var(--light);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: var(--primary);
        }
        
        /* Message Styles */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .message.error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        /* Form Styles */
        .edit-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-section {
            background: var(--secondary);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--light);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--light);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.15);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-help {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        /* Screenshots Grid */
        .screenshots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .screenshot-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: var(--dark);
        }
        
        .screenshot-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .screenshot-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        /* Cover Upload */
        .cover-upload {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .cover-preview {
            width: 150px;
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            background: var(--dark);
            flex-shrink: 0;
        }
        
        .cover-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .upload-info {
            flex: 1;
        }
        
        /* File Input */
        .file-input {
            display: none;
        }
        
        .file-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-label:hover {
            background: var(--primary-dark);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .edit-form {
                grid-template-columns: 1fr;
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
        }
        
        @media (max-width: 768px) {
            .cover-upload {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .screenshots-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
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
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">Editar Fangame</div>
                <a href="game.php?id=<?php echo $gameId; ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Voltar para o Jogo
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="edit-form">
                <!-- Informações Básicas -->
                <div class="form-section">
                    <h3 class="section-title">Informações Básicas</h3>
                    
                    <div class="form-group">
                        <label class="form-label" for="game_title">Título do Jogo *</label>
                        <input type="text" id="game_title" name="game_title" class="form-control" 
                               value="<?php echo htmlspecialchars($game['GameTitle']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="game_description">Descrição</label>
                        <textarea id="game_description" name="game_description" class="form-control" 
                                  rows="6"><?php echo htmlspecialchars($game['GameDescription'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="franchise">Franquia</label>
                        <input type="text" id="franchise" name="franchise" class="form-control" 
                               value="<?php echo htmlspecialchars($game['Franchise'] ?? ''); ?>">
                        <div class="form-help">Ex: Mario, Sonic, Pokémon, etc.</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="genre">Gênero</label>
                        <input type="text" id="genre" name="genre" class="form-control" 
                               value="<?php echo htmlspecialchars($game['Genre'] ?? ''); ?>">
                        <div class="form-help">Ex: Plataforma, RPG, Ação, etc.</div>
                    </div>
                </div>
                
                <!-- Detalhes Técnicos -->
                <div class="form-section">
                    <h3 class="section-title">Detalhes Técnicos</h3>
                    
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="Em Desenvolvimento" <?php echo ($game['Status'] ?? '') == 'Em Desenvolvimento' ? 'selected' : ''; ?>>Em Desenvolvimento</option>
                            <option value="Lançado" <?php echo ($game['Status'] ?? '') == 'Lançado' ? 'selected' : ''; ?>>Lançado</option>
                            <option value="Pausado" <?php echo ($game['Status'] ?? '') == 'Pausado' ? 'selected' : ''; ?>>Pausado</option>
                            <option value="Cancelado" <?php echo ($game['Status'] ?? '') == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" class="form-control" 
                               value="<?php echo htmlspecialchars($game['Tags'] ?? ''); ?>">
                        <div class="form-help">Separe as tags por vírgula (ex: 2D, Plataforma, Aventura)</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="download_link">Link de Download</label>
                        <input type="url" id="download_link" name="download_link" class="form-control" 
                               value="<?php echo htmlspecialchars($game['DownloadLink'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="file_size">Tamanho do Arquivo</label>
                        <input type="text" id="file_size" name="file_size" class="form-control" 
                               value="<?php echo htmlspecialchars($game['FileSize'] ?? ''); ?>">
                        <div class="form-help">Ex: 150 MB, 2.5 GB</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="release_date">Data de Lançamento</label>
                        <input type="date" id="release_date" name="release_date" class="form-control" 
                               value="<?php echo htmlspecialchars($game['ReleaseDate'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- Capa do Jogo -->
                <div class="form-section">
                    <h3 class="section-title">Capa do Jogo</h3>
                    
                    <div class="cover-upload">
                        <div class="cover-preview">
                            <?php if (!empty($game['GameCover'])): ?>
                                <img src="<?php echo htmlspecialchars($game['GameCover']); ?>" 
                                     alt="Capa atual" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="game-cover-fallback" style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background: var(--dark); color: white;">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--dark); color: white;">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="upload-info">
                            <p style="margin-bottom: 15px; color: var(--gray);">
                                Formatos suportados: JPG, PNG, GIF, WEBP<br>
                                Tamanho máximo: 5MB
                            </p>
                            <input type="file" id="game_cover" name="game_cover" class="file-input" 
                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                            <label for="game_cover" class="file-label">
                                <i class="fas fa-upload"></i>
                                Alterar Capa
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Screenshots -->
                <div class="form-section">
                    <h3 class="section-title">Screenshots</h3>
                    
                    <?php if (!empty($screenshotData)): ?>
                        <div class="screenshots-grid">
                            <?php foreach ($screenshotData as $screenshot): ?>
                                <div class="screenshot-item">
                                    <img src="<?php echo htmlspecialchars($screenshot['ScreenshotPath']); ?>" 
                                         alt="Screenshot">
                                    <div class="screenshot-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="screenshot_id" value="<?php echo $screenshot['ScreenshotID']; ?>">
                                            <button type="submit" name="delete_screenshot" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Tem certeza que deseja remover esta screenshot?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--gray); margin-bottom: 20px;">Nenhuma screenshot adicionada ainda.</p>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">Adicionar Screenshots</label>
                        <input type="file" id="screenshots" name="screenshots[]" class="file-input" 
                               multiple accept=".jpg,.jpeg,.png,.gif,.webp">
                        <label for="screenshots" class="file-label">
                            <i class="fas fa-images"></i>
                            Selecionar Screenshots
                        </label>
                        <div class="form-help">Você pode selecionar múltiplas imagens. Formatos: JPG, PNG, GIF, WEBP</div>
                    </div>
                </div>
                
                <!-- Requisitos do Sistema -->
                <div class="form-section">
                    <h3 class="section-title">Requisitos do Sistema</h3>
                    
                    <div class="form-group">
                        <label class="form-label" for="system_requirements">Requisitos</label>
                        <textarea id="system_requirements" name="system_requirements" class="form-control" 
                                  rows="8" placeholder="Ex:
• Sistema Operacional: Windows 10
• Processador: Intel Core i5
• Memória: 8 GB RAM
• Placa de vídeo: NVIDIA GTX 1060
• Armazenamento: 2 GB de espaço disponível"><?php echo htmlspecialchars($game['SystemRequirements'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Botão de Atualização -->
                <div class="form-section" style="grid-column: 1 / -1; text-align: center;">
                    <button type="submit" name="update_game" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 40px;">
                        <i class="fas fa-save"></i>
                        Atualizar Fangame
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Preview de arquivos selecionados
        document.getElementById('game_cover').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.cover-preview img');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        // Esconder fallback se existir
                        const fallback = preview.nextElementSibling;
                        if (fallback) fallback.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Mostrar quantas screenshots foram selecionadas
        document.getElementById('screenshots').addEventListener('change', function(e) {
            const files = e.target.files;
            const label = document.querySelector('label[for="screenshots"]');
            if (files.length > 0) {
                label.innerHTML = `<i class="fas fa-images"></i> ${files.length} screenshot(s) selecionada(s)`;
            }
        });
        
        // Validação básica do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('game_title').value.trim();
            if (!title) {
                e.preventDefault();
                alert('Por favor, preencha o título do jogo.');
                document.getElementById('game_title').focus();
            }
        });
    </script>
</body>
</html>