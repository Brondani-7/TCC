<?php
// Configurações básicas
session_start();

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

// Função simples para verificar login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

$pageTitle = "Firelink Shrine";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | BONFIRE GAMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-fire"></i>
            <span>BONFIRE GAMES</span>
        </div>
        
        <nav class="nav-top">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-home"></i> Início
            </a>
            <a href="pages/fangames.php" class="nav-link">
                <i class="fas fa-gamepad"></i> Fangames
            </a>
            <a href="pages/forum.php" class="nav-link">
                <i class="fas fa-users"></i> Comunidade
            </a>
            <a href="pages/search.php" class="nav-link">
                <i class="fas fa-search"></i> Pesquisar
            </a>
        </nav>

        <div class="header-actions">
            <?php if(isLoggedIn()): ?>
                <div class="user-menu">
                    <a href="pages/profile.php" class="nav-link">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <a href="pages/logout.php" class="btn btn-secondary">Sair</a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="pages/login.php" class="btn btn-secondary">Entrar</a>
                    <a href="pages/register.php" class="btn btn-primary">Cadastrar</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="hero-section">
            <div class="hero-content">
                <h1>BONFIRE GAMES</h1>
                <p class="hero-subtitle">Uma jornada começa com um único passo... ou um único jogo</p>
                
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($fangames); ?></div>
                        <div class="stat-label">Fangames</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">42</div>
                        <div class="stat-label">Criadores</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">1.2K</div>
                        <div class="stat-label">Downloads</div>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="pages/fangames.php" class="btn btn-primary btn-large">
                        <i class="fas fa-gamepad"></i>
                        Explorar Fangames
                    </a>
                    <?php if(!isLoggedIn()): ?>
                    <a href="pages/register.php" class="btn btn-secondary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Juntar-se à Fogueira
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Fangames em Destaque -->
        <section class="featured-section">
            <h2 class="section-title">
                <i class="fas fa-crown"></i>
                Fangames em Destaque
            </h2>
            
            <div class="games-grid">
                <?php
                $featuredGames = array_slice($fangames, 0, 3);
                foreach($featuredGames as $game): 
                ?>
                <div class="game-card featured">
                    <div class="game-cover">
                        <i class="fas fa-<?php echo $game['franchise'] == 'sonic' ? 'bolt' : 'dragon'; ?>"></i>
                        <div class="game-badge">Destaque</div>
                    </div>
                    <div class="game-info">
                        <h3 class="game-title"><?php echo $game['title']; ?></h3>
                        <p class="game-description"><?php echo $game['description']; ?></p>
                        
                        <div class="game-meta">
                            <div class="game-developer">
                                <i class="fas fa-user"></i>
                                <?php echo $game['developer']; ?>
                            </div>
                            <div class="game-stats">
                                <span class="game-stat">
                                    <i class="fas fa-download"></i>
                                    <?php echo number_format($game['downloads']); ?>
                                </span>
                                <span class="game-stat">
                                    <i class="fas fa-star"></i>
                                    <?php echo $game['rating']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="game-tags">
                            <?php foreach(array_slice($game['tags'], 0, 2) as $tag): ?>
                            <span class="game-tag"><?php echo $tag; ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="pages/game.php?id=<?php echo $game['id']; ?>" class="btn btn-secondary">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-brand">
                <div class="footer-logo">
                    <i class="fas fa-fire"></i>
                    <span>BONFIRE GAMES</span>
                </div>
                <p class="footer-desc">
                    Plataforma para fangames criados pela comunidade. 
                    Descubra, compartilhe, jogue.
                </p>
            </div>

            <div class="footer-links">
                <div class="link-group">
                    <h4>Navegação</h4>
                    <a href="index.php">Início</a>
                    <a href="pages/fangames.php">Fangames</a>
                    <a href="pages/forum.php">Comunidade</a>
                </div>

                <div class="link-group">
                    <h4>Ajuda</h4>
                    <a href="#">Suporte</a>
                    <a href="#">FAQ</a>
                    <a href="#">Contato</a>
                </div>

                <div class="link-group">
                    <h4>Legal</h4>
                    <a href="#">Termos</a>
                    <a href="#">Privacidade</a>
                </div>
            </div>

            <div class="footer-social">
                <h4>Conecte-se</h4>
                <div class="social-icons">
                    <a href="#" class="social-btn" title="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="#" class="social-btn" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-btn" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; 2024 BONFIRE GAMES. Desenvolvido com ♥ pela comunidade.
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>