function searchGames(query) {
    console.log("Buscando:", query);
    // Implementar busca AJAX
}

document.getElementById('searchInput')?.addEventListener('input', (e) => {
    searchGames(e.target.value);
});// Sistema de busca em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => performSearch(query), 300);
        });
    }

    function performSearch(query) {
        fetch(`/api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Erro na busca:', error);
            });
    }

    function displayResults(results) {
        if (!resultsContainer) return;
        
        if (results.length === 0) {
            resultsContainer.innerHTML = '<p>Nenhum resultado encontrado.</p>';
            return;
        }

        resultsContainer.innerHTML = results.map(game => `
            <div class="search-result">
                <h4>${game.title}</h4>
                <p>${game.description}</p>
                <a href="/pages/game.php?id=${game.id}">Ver detalhes</a>
            </div>
        `).join('');
    }
});

// Busca avançada
function advancedSearch(filters) {
    const params = new URLSearchParams();
    
    Object.keys(filters).forEach(key => {
        if (filters[key]) params.append(key, filters[key]);
    });

    fetch(`/api/search.php?${params}`)
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        });
}

// Buscar por tags
function searchByTag(tag) {
    document.getElementById('searchInput').value = tag;
    document.getElementById('searchInput').dispatchEvent(new Event('input'));
}