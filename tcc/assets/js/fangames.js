// Filtros e busca na página de fangames
document.addEventListener('DOMContentLoaded', function() {
    const genreFilter = document.getElementById('genreFilter');
    const franchiseFilter = document.getElementById('franchiseFilter');
    const searchInput = document.getElementById('searchInput');
    const gameCards = document.querySelectorAll('.game-card');

    function filterGames() {
        const genreValue = genreFilter.value.toLowerCase();
        const franchiseValue = franchiseFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();

        gameCards.forEach(card => {
            const genre = card.getAttribute('data-genre');
            const franchise = card.getAttribute('data-franchise');
            const title = card.querySelector('.game-title').textContent.toLowerCase();
            const description = card.querySelector('.game-description').textContent.toLowerCase();

            const genreMatch = !genreValue || genre === genreValue;
            const franchiseMatch = !franchiseValue || franchise === franchiseValue;
            const searchMatch = !searchValue || title.includes(searchValue) || description.includes(searchValue);

            card.style.display = (genreMatch && franchiseMatch && searchMatch) ? 'block' : 'none';
        });
    }

    if (genreFilter) genreFilter.addEventListener('change', filterGames);
    if (franchiseFilter) franchiseFilter.addEventListener('change', filterGames);
    if (searchInput) searchInput.addEventListener('input', filterGames);
});

// Sistema de avaliação
function rateGame(gameId, rating) {
    if (!confirm(`Deseja avaliar com ${rating} estrelas?`)) return;
    
    fetch('/api/rate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ gameId, rating })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Avaliação salva!');
            location.reload();
        } else {
            alert('Erro ao avaliar.');
        }
    })
    .catch(error => console.error('Erro:', error));
}