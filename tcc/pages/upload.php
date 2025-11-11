<?php
$pageTitle = "Enviar Fangame";
require_once '../includes/header.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>

<div class="page-header">
    <h1>Enviar Fangame</h1>
    <p>Compartilhe sua criação com a comunidade</p>
</div>

<div class="upload-container">
    <form action="../includes/upload-process.php" method="POST" enctype="multipart/form-data" class="upload-form">
        <div class="form-section">
            <h3>Informações Básicas</h3>
            
            <div class="form-group">
                <label>Título do Fangame *</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Descrição *</label>
                <textarea name="description" rows="4" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Gênero</label>
                    <select name="genre">
                        <option value="plataforma">Plataforma</option>
                        <option value="rpg">RPG</option>
                        <option value="aventura">Aventura</option>
                        <option value="acao">Ação</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Franquia</label>
                    <select name="franchise">
                        <option value="sonic">Sonic</option>
                        <option value="pokemon">Pokémon</option>
                        <option value="mario">Mario</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Arquivos</h3>
            
            <div class="form-group">
                <label>Arquivo do Jogo (ZIP/RAR) *</label>
                <input type="file" name="game_file" accept=".zip,.rar" required>
            </div>
            
            <div class="form-group">
                <label>Imagem de Capa</label>
                <input type="file" name="cover_image" accept="image/*">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i>
                Enviar Fangame
            </button>
            <a href="fangames.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>