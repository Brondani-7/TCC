<?php
// upload_game.php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'Você precisa estar logado para adicionar um fangame.';
    header('Location: fangames.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Validar campos obrigatórios
        $required_fields = ['gameTitle', 'gameDescription', 'gameGenre', 'gameFranchise', 'gameStatus'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("O campo " . str_replace('game', '', $field) . " é obrigatório.");
            }
        }

        // Obter dados do formulário
        $gameTitle = trim($_POST['gameTitle']);
        $gameDescription = trim($_POST['gameDescription']);
        $gameGenre = $_POST['gameGenre'];
        $gameFranchise = trim($_POST['gameFranchise']);
        $gameStatus = $_POST['gameStatus'];
        $developerId = $_SESSION['user_id'];

        // Validar arquivo
        if (!isset($_FILES['gameFile']) || $_FILES['gameFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo. Por favor, tente novamente.");
        }

        // Criar diretório de uploads se não existir
        $uploadDir = 'uploads/games/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Processar upload do arquivo
        $uploadResult = uploadFile($_FILES['gameFile'], $uploadDir);
        
        if (is_string($uploadResult) && strpos($uploadResult, 'Erro:') === 0) {
            throw new Exception($uploadResult);
        }

        $filePath = $uploadResult;
        $fileSize = filesize($filePath);

        // Inserir no banco de dados
        $stmt = $pdo->prepare("
            INSERT INTO fangames 
            (GameTitle, GameDescription, DeveloperID, Genre, Franchise, Status, FileSize, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $gameTitle,
            $gameDescription,
            $developerId,
            $gameGenre,
            $gameFranchise,
            $gameStatus,
            formatFileSize($fileSize)
        ]);

        $pdo->commit();

        // Redirecionar com mensagem de sucesso
        $_SESSION['success_message'] = 'Fangame adicionado com sucesso!';
        header('Location: fangames.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        
        // Deletar arquivo se foi feito upload mas houve erro no banco
        if (isset($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
        
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: fangames.php');
        exit;
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>