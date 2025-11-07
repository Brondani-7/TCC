<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE CustomerGmail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['CustomerPassword'])) {
        $_SESSION['customer_id'] = $user['CustomerID'];
        $_SESSION['customer_name'] = $user['CustomerName'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BONFIRE GAMES</title>
    <style>
        /* (Manter CSS do login original) */
        <?php include 'styles/login.css'; ?>
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <i class="fas fa-fire"></i>
                <span>BONFIRE GAMES</span>
            </div>
            
            <h2>Entrar na Comunidade</h2>
            
            <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Entrar</button>
            </form>
            
            <div class="register-link">
                Não tem conta? <a href="register.php">Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>