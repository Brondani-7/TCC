<?php
$pageTitle = "Perfil";
require_once '../includes/header.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUser();
?>

<div class="profile-header">
    <div class="cover-photo"></div>
    <div class="profile-info">
        <div class="avatar-large">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-details">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p class="profile-handle">@<?= strtolower($user['username']) ?></p>
            <p class="profile-bio">Membro desde <?= $user['joined'] ?></p>
            
            <div class="profile-stats">
                <div class="profile-stat">
                    <span class="stat-number">12</span>
                    <span class="stat-label">Fangames</span>
                </div>
                <div class="profile-stat">
                    <span class="stat-number">347</span>
                    <span class="stat-label">Seguidores</span>
                </div>
                <div class="profile-stat">
                    <span class="stat-number">128</span>
                    <span class="stat-label">Seguindo</span>
                </div>
            </div>
        </div>
        <button class="btn btn-secondary" id="editProfileBtn">Editar Perfil</button>
    </div>
</div>

<div class="profile-tabs">
    <button class="profile-tab active" data-tab="games">Meus Fangames</button>
    <button class="profile-tab" data-tab="favorites">Favoritos</button>
    <button class="profile-tab" data-tab="activity">Atividade</button>
</div>

<div class="profile-content">
    <div class="tab-panel active" id="games">
        <h3>Meus Fangames</h3>
        <div class="user-games-grid">
            <div class="user-game-card">
                <div class="game-cover-small">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="game-info-small">
                    <h4>Sonic Adventure DX</h4>
                    <p>Remake do clássico em Unity</p>
                    <div class="game-stats-small">
                        <span>1.2K downloads</span>
                        <span>4.5★</span>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Adicionar Novo Fangame
        </a>
    </div>
    
    <div class="tab-panel" id="favorites">
        <h3>Fangames Favoritos</h3>
        <p>Seus jogos favoritos aparecerão aqui.</p>
    </div>
    
    <div class="tab-panel" id="activity">
        <h3>Atividade Recente</h3>
        <div class="activity-feed">
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="activity-content">
                    <p>Você baixou <strong>Sonic Utopia</strong></p>
                    <span class="activity-time">2 horas atrás</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>