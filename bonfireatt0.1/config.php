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

// Função para fazer upload de arquivo
function uploadFile($file, $destination) {
    // Criar diretório se não existir
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }
    
    $target_file = $destination . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Verificar se o arquivo já existe
    if (file_exists($target_file)) {
        $name = pathinfo($file["name"], PATHINFO_FILENAME);
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $counter = 1;
        while (file_exists($destination . $name . '_' . $counter . '.' . $extension)) {
            $counter++;
        }
        $target_file = $destination . $name . '_' . $counter . '.' . $extension;
    }

    // Verificar tamanho do arquivo (máximo 100MB)
    if ($file["size"] > 100000000) {
        return "Erro: Arquivo muito grande. Tamanho máximo: 100MB.";
    }

    // Permitir apenas certos formatos
    if($fileType != "zip" && $fileType != "rar" && $fileType != "7z") {
        return "Erro: Apenas arquivos ZIP, RAR e 7Z são permitidos.";
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return "Erro: Falha no upload do arquivo.";
    }
}

// Função para fazer logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Processar logout se solicitado
if (isset($_GET['logout'])) {
    logout();
}
?>