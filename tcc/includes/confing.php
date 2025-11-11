<?php
// Configurações de segurança
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'database_tcc');
define('DB_USER', 'root');
define('DB_PASS', '');

// Função para conexão com banco
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        error_log("Erro DB: " . $e->getMessage());
        return null;
    }
}

// Funções de segurança
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Funções de autenticação
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['CustomerID'];
    $_SESSION['user'] = [
        'username' => $user['Name'],
        'email' => $user['Email'],
        'joined' => $user['created_at'] ?? '2024'
    ];
}

function logoutUser() {
    session_destroy();
    header('Location: /index.php');
    exit;
}

// Dados mock para desenvolvimento
$fangames = [
    [
        'id' => 1,
        'title' => 'Sonic Adventure DX',
        'description' => 'Remake do clássico jogo do Sonic em Unity',
        'developer' => 'SonicFanBR',
        'downloads' => 1250,
        'rating' => 4.5,
        'genre' => 'plataforma',
        'franchise' => 'sonic',
        'status' => 'active',
        'tags' => ['2D', 'Velocidade', 'Clássico']
    ],
    [
        'id' => 2,
        'title' => 'Pokémon Dark Version',
        'description' => 'Fangame Pokémon com história sombria',
        'developer' => 'PokeMaster',
        'downloads' => 890,
        'rating' => 4.2,
        'genre' => 'rpg',
        'franchise' => 'pokemon',
        'status' => 'active',
        'tags' => ['RPG', 'Turnos', 'Aventura']
    ]
];
?>