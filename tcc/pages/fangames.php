<?php
$pageTitle = "Fangames";
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Descubra Fangames Incríveis</h1>
    <p>Da comunidade, para a comunidade</p>
    
    <?php if(isLoggedIn()): ?>
    <a href="upload.php" class="btn btn-primary">
        <i class="fas fa-cloud-upload-alt"></i>
        Enviar Fangame
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="filter-bar">
    <select class="filter-select" id="genreFilter">
        <option value="">Todos os Gêneros</option>
        <option value="plataforma">Plataforma</option>
        <option value="rpg">RPG</option>
        <option value="aventura">Aventura</option>
    </select>
    
    <select class="filter-select" id="franchiseFilter">
        <option value="">Todas as Franquias</option>
        <option value="sonic">Sonic</option>
        <option value="pokemon">Pokémon</option>
        <option value="mario">Mario</option>
    </select>

    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar fangames...">
    </div>
</div>

<!-- Grid de Fangames -->
<div class="games-grid" id="gamesGrid">
    <?php foreach($fangames as $game): ?>
    <div class="game-card" data-genre="<?= strtolower($game['genre']) ?>" data-franchise="<?= strtolower($game['franchise']) ?>">
        <div class="game-cover">
            <i class="fas fa-<?= $game['franchise'] == 'Sonic' ? 'bolt' : 'dragon' ?>"></i>
            <div class="game-badge"><?= $game['status'] ?></div>
        </div>
        <div class="game-info">
            <h3 class="game-title"><?= $game['title'] ?></h3>
            <p class="game-description"><?= $game['description'] ?></p>
            
            <div class="game-meta">
                <div class="game-developer">
                    <i class="fas fa-user"></i>
                    <?= $game['developer'] ?>
                </div>
                <div class="game-stats">
                    <span class="game-stat">
                        <i class="fas fa-download"></i>
                        <?= number_format($game['downloads']) ?>
                    </span>
                    <span class="game-stat">
                        <i class="fas fa-star"></i>
                        <?= $game['rating'] ?>
                    </span>
                </div>
            </div>
            
            <div class="game-tags">
                <?php foreach($game['tags'] as $tag): ?>
                <span class="game-tag"><?= $tag ?></span>
                <?php endforeach; ?>
            </div>
            
            <a href="game.php?id=<?= $game['id'] ?>" class="btn btn-secondary">
                Ver Detalhes
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>