// Sistema de posts no fórum
document.addEventListener('DOMContentLoaded', function() {
    const publishBtn = document.getElementById('publishPost');
    const postContent = document.getElementById('postContent');

    if (publishBtn && postContent) {
        publishBtn.addEventListener('click', function() {
            const content = postContent.value.trim();
            
            if (content.length < 5) {
                alert('Post muito curto. Mínimo 5 caracteres.');
                return;
            }

            fetch('/api/create-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    postContent.value = '';
                    addNewPost(data.post);
                } else {
                    alert('Erro ao publicar post.');
                }
            });
        });
    }

    // Sistema de likes
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const icon = this.querySelector('i');
            
            fetch('/api/like-post.php', {
                method: 'POST',
                body: JSON.stringify({ postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = this.querySelector('.like-count');
                    if (icon.classList.contains('fas')) {
                        icon.classList.replace('fas', 'far');
                        count.textContent = parseInt(count.textContent) - 1;
                    } else {
                        icon.classList.replace('far', 'fas');
                        count.textContent = parseInt(count.textContent) + 1;
                    }
                }
            });
        });
    });
});

function addNewPost(post) {
    const postsFeed = document.querySelector('.posts-feed');
    const newPost = document.createElement('div');
    newPost.className = 'forum-post';
    newPost.innerHTML = `
        <div class="post-header">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <div class="post-user-info">
                <div class="username">${post.username}</div>
                <div class="post-meta">Agora</div>
            </div>
        </div>
        <div class="post-content">${post.content}</div>
        <div class="post-stats">
            <button class="stat-btn"><i class="far fa-comment"></i> 0</button>
            <button class="stat-btn"><i class="far fa-heart"></i> 0</button>
        </div>
    `;
    postsFeed.insertBefore(newPost, postsFeed.firstChild);
}

// Carregar mais posts
function loadMorePosts() {
    const lastPost = document.querySelector('.forum-post:last-child');
    const lastId = lastPost?.getAttribute('data-post-id') || 0;

    fetch(`/api/load-posts.php?last_id=${lastId}`)
        .then(response => response.json())
        .then(posts => {
            posts.forEach(post => addNewPost(post));
        });
}