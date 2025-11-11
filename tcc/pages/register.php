<?php
$pageTitle = "Cadastrar";
require_once '../includes/header.php';

if(isLoggedIn()) {
    header('Location: profile.php');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Juntar-se à Fogueira</h2>
        
        <form action="../includes/register-process.php" method="POST">
            <div class="form-group">
                <label>Nome de Usuário</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
        
        <p class="auth-link">
            Já tem conta? <a href="login.php">Entre aqui</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>