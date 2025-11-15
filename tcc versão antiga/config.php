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

// Função para fazer logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Função para criar diretórios se não existirem
function createUploadDirs() {
    $dirs = ['uploads/games/covers', 'uploads/games/files'];
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
?>