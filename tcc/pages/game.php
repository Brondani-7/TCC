<?php
$pageTitle = "Detalhes do Jogo";
require_once '../includes/header.php';

$gameId = $_GET['id'] ?? 1;
$game = $fangames[$gameId - 1] ?? $fangames[0];
?>

<div class="game-header">
    <div class="game-cover-large">
        <i class="fas fa-<?= $game['franchise'] == 'Sonic' ? 'bolt' : 'dragon' ?>"></i>
    </div>
    
    <div class="game-info-large">
        <h1><?= $game['title'] ?></h1>
        <p class="game-developer">por <?= $game['developer'] ?></p>
        
        <div class="game-stats-large">
            <div class="stat">
                <span class="stat-number"><?= number_format($game['downloads']) ?></span>
                <span class="stat-label">Downloads</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?= $game['rating'] ?></span>
                <span class="stat-label">Avaliação</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?= $game['genre'] ?></span>
                <span class="stat-label">Gênero</span>
            </div>
        </div>
        
        <div class="game-actions">
            <button class="btn btn-primary">
                <i class="fas fa-download"></i>
                Download
            </button>
            <button class="btn btn-secondary">
                <i class="fas fa-star"></i>
                Avaliar
            </button>
            <button class="btn btn-secondary">
                <i class="fas fa-heart"></i>
                Favoritar
            </button>
        </div>
    </div>
</div>

<div class="game-details">
    <div class="detail-section">
        <h3>Descrição</h3>
        <p><?= $game['description'] ?></p>
    </div>
    
    <div class="detail-section">
        <h3>Tags</h3>
        <div class="game-tags">
            <?php foreach($game['tags'] as $tag): ?>
            <span class="game-tag"><?= $tag ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="detail-section">
        <h3>Requisitos</h3>
        <ul>
            <li>Sistema: Windows 10</li>
            <li>Processador: 2.0 GHz</li>
            <li>Memória: 2GB RAM</li>
            <li>Armazenamento: 500MB</li>
        </ul>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>