<?php
require_once 'config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Buscar usuário pelo email
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE CustomerGmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar senha (sem hash por enquanto - vamos corrigir depois)
            if ($password === $user['CustomerPassword'] || password_verify($password, $user['CustomerPassword'])) {
                // Login bem-sucedido
                $_SESSION['customer_id'] = $user['CustomerID'];
                $_SESSION['customer_name'] = $user['CustomerName'];
                $_SESSION['customer_email'] = $user['CustomerGmail'];
                
                $_SESSION['success_message'] = 'Login realizado com sucesso!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email ou senha incorretos.';
            }
        } else {
            $error = 'Email ou senha incorretos.';
        }
    } catch (Exception $e) {
        $error = 'Erro ao fazer login. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BONFIRE GAMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4655;
            --primary-dark: #e63e4c;
            --secondary: #0f1923;
            --dark: #1a2b3c;
            --light: #ece8e1;
            --gray: #768079;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--secondary), var(--dark));
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .logo h1 {
            font-size: 2.5rem;
            background: linear-gradient(90deg, var(--primary), #ff7e5f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .login-card {
            background: rgba(26, 43, 60, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-card h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8rem;
            color: var(--light);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--light);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--light);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .input-with-icon input:focus {
            border-color: var(--primary);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .input-with-icon input::placeholder {
            color: var(--gray);
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 70, 85, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: var(--gray);
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            background: var(--dark);
            padding: 0 15px;
            position: relative;
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: var(--primary);
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h1>BONFIRE GAMES</h1>
            <p>Comunidade de Fangames</p>
        </div>
        
        <div class="login-card">
            <h2>Entre na sua conta</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Sua senha" required>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>
            
            <div class="forgot-password">
                <a href="forgot-password.php">Esqueceu sua senha?</a>
            </div>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <div class="register-link">
                <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
</body>
</html>
