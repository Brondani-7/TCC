<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $handle = $_POST['handle'];
    $password = $_POST['password'];
    
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT CustomerID FROM usuarios WHERE CustomerGmail = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $error = "Email já cadastrado!";
    } else {
        // Inserir novo usuário
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (CustomerName, CustomerGmail, CustomerHandle, CustomerPassword) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $email, $handle, $hashedPassword])) {
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['customer_name'] = $name;
            header("Location: index.php");
            exit;
        } else {
            $error = "Erro ao criar conta!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | BONFIRE GAMES</title>
    <style>
        /* (Manter CSS do registro original) */
        <?php include 'styles/register.css'; ?>
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-form">
            <div class="logo">
                <i class="fas fa-fire"></i>
                <span>BONFIRE GAMES</span>
            </div>
            
            <h2>Junte-se à Comunidade</h2>
            
            <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nome</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="handle">Nome de Usuário</label>
                    <input type="text" id="handle" name="handle" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="register-btn">Criar Conta</button>
            </form>
            
            <div class="login-link">
                Já tem conta? <a href="login.php">Faça login</a>
            </div>
        </div>
    </div>
</body>
</html>