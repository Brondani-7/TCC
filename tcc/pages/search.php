<?php
$pageTitle = "Pesquisar";
require_once '../includes/header.php';

$query = $_GET['q'] ?? '';
?>

<div class="page-header">
    <h1>Pesquisar Fangames</h1>
</div>

<div class="search-container">
    <div class="search-box-large">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Digite o nome do jogo, desenvolvedor ou tags..." value="<?= htmlspecialchars($query) ?>">
        <button class="btn btn-primary" onclick="performSearch()">Buscar</button>
    </div>
    
    <div class="search-filters">
        <select class="filter-select">
            <option>Ordenar por: Relevância</option>
            <option>Mais Baixados</option>
            <option>Melhor Avaliados</option>
            <option>Mais Recentes</option>
        </select>
    </div>
    
    <div id="searchResults" class="search-results">
        <?php if($query): ?>
            <p>Resultados para: "<?= htmlspecialchars($query) ?>"</p>
            <div class="games-grid">
                <?php 
                // Filtra jogos baseado na query
                $filteredGames = array_filter($fangames, function($game) use ($query) {
                    return stripos($game['title'], $query) !== false || 
                           stripos($game['developer'], $query) !== false;
                });
                
                foreach($filteredGames as $game): 
                ?>
                <div class="game-card">
                    <div class="game-cover">
                        <i class="fas fa-<?= $game['franchise'] == 'Sonic' ? 'bolt' : 'dragon' ?>"></i>
                    </div>
                    <div class="game-info">
                        <h3><?= $game['title'] ?></h3>
                        <p><?= $game['description'] ?></p>
                        <a href="game.php?id=<?= $game['id'] ?>" class="btn btn-secondary">Ver Detalhes</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Digite algo para buscar fangames.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function performSearch() {
    const query = document.getElementById('searchInput').value;
    if(query.trim()) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
    }
}

// Busca ao pressionar Enter
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        performSearch();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>