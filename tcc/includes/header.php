<?php
session_start();

// Configurações básicas
$pageTitle = $pageTitle ?? 'BONFIRE GAMES';
$currentPage = basename($_SERVER['PHP_SELF']);

// Função simples para verificar login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função simples para obter usuário
function getUser() {
    return $_SESSION['user'] ?? ['username' => 'Visitante', 'joined' => '2024'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-fire"></i>
            <span>BONFIRE GAMES</span>
        </div>
        
        <nav class="nav-top">
            <a href="/index.php" class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Início
            </a>
            <a href="/pages/fangames.php" class="nav-link <?php echo $currentPage == 'fangames.php' ? 'active' : ''; ?>">
                <i class="fas fa-gamepad"></i> Fangames
            </a>
            <a href="/pages/forum.php" class="nav-link <?php echo $currentPage == 'forum.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Comunidade
            </a>
            <a href="/pages/search.php" class="nav-link <?php echo $currentPage == 'search.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Pesquisar
            </a>
        </nav>

        <div class="header-actions">
            <?php if(isLoggedIn()): ?>
                <div class="user-menu">
                    <a href="/pages/profile.php" class="nav-link">
                        <i class="fas fa-user"></i> <?php echo getUser()['username']; ?>
                    </a>
                    <a href="/pages/logout.php" class="btn btn-secondary">Sair</a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="/pages/login.php" class="btn btn-secondary">Entrar</a>
                    <a href="/pages/register.php" class="btn btn-primary">Cadastrar</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">