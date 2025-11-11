<?php
$pageTitle = "Entrar";
require_once '../includes/header.php';

if(isLoggedIn()) {
    header('Location: profile.php');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Entrar na Fogueira</h2>
        
        <form action="../includes/login-process.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
        
        <p class="auth-link">
            Não tem conta? <a href="register.php">Cadastre-se</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>