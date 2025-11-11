<?php
$pageTitle = "Bonfires - Comunidade";
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Bonfires</h1>
    <p>Junte-se à fogueira e compartilhe suas experiências</p>
</div>

<div class="forum-layout">
    <!-- Criar Post -->
    <?php if(isLoggedIn()): ?>
    <div class="create-post-card">
        <div class="post-input">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <textarea placeholder="O que está acontecendo no mundo dos jogos?" id="postContent"></textarea>
        </div>
        <div class="post-actions">
            <button class="btn btn-primary" id="publishPost">Publicar</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Posts do Fórum -->
    <div class="posts-feed">
        <!-- Post 1 -->
        <div class="forum-post">
            <div class="post-header">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="post-user-info">
                    <div class="username">SonicFanBR <span class="user-badge">MOD</span></div>
                    <div class="post-meta">2 horas atrás</div>
                </div>
            </div>
            
            <div class="post-content">
                Alguém mais está tendo problemas com a nova atualização? Meu FPS caiu drasticamente!
            </div>
            
            <div class="post-tags">
                <span class="post-tag">#Performance</span>
                <span class="post-tag">#Ajuda</span>
            </div>
            
            <div class="post-stats">
                <button class="stat-btn"><i class="far fa-comment"></i> 42</button>
                <button class="stat-btn"><i class="far fa-heart"></i> 156</button>
            </div>
        </div>

        <!-- Post 2 -->
        <div class="forum-post">
            <div class="post-header">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="post-user-info">
                    <div class="username">GameMasterBR <span class="user-badge dev">DEV</span></div>
                    <div class="post-meta">5 horas atrás</div>
                </div>
            </div>
            
            <div class="post-content">
                Evento de verão começando na próxima semana! Preparem-se para desafios especiais! 🎉
            </div>
            
            <div class="post-tags">
                <span class="post-tag">#Evento</span>
                <span class="post-tag">#Anúncio</span>
            </div>
            
            <div class="post-stats">
                <button class="stat-btn"><i class="far fa-comment"></i> 127</button>
                <button class="stat-btn"><i class="far fa-heart"></i> 342</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>