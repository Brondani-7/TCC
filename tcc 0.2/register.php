<?php
require_once 'config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $handle = trim($_POST['handle']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Validações
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Por favor, insira um email válido.");
        }

        if ($password !== $confirm_password) {
            throw new Exception("As senhas não coincidem.");
        }

        if (strlen($password) < 6) {
            throw new Exception("A senha deve ter pelo menos 6 caracteres.");
        }

        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT CustomerID FROM usuarios WHERE CustomerGmail = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Este email já está cadastrado.");
        }

        // Verificar se handle já existe (se fornecido)
        if (!empty($handle)) {
            $stmt = $pdo->prepare("SELECT CustomerID FROM usuarios WHERE CustomerHandle = ?");
            $stmt->execute([$handle]);
            if ($stmt->fetch()) {
                throw new Exception("Este nome de usuário já está em uso.");
            }
        }

        // Hash da senha
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios 
            (CustomerName, CustomerGmail, CustomerHandle, CustomerPassword, CreatedAt) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $name,
            $email,
            empty($handle) ? null : $handle,
            $hashed_password
        ]);

        // Login automático após cadastro
        $user_id = $pdo->lastInsertId();
        $_SESSION['customer_id'] = $user_id;
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_email'] = $email;
        
        $_SESSION['success_message'] = 'Cadastro realizado com sucesso! Bem-vindo ao Bonfire Games!';
        header('Location: fangames.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | BONFIRE GAMES</title>
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
        
        .register-container {
            width: 100%;
            max-width: 450px;
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
        
        .register-card {
            background: rgba(26, 43, 60, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .register-card h2 {
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
        
        .form-group label .required {
            color: var(--primary);
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
        
        .form-help {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .register-btn {
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
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 70, 85, 0.4);
        }
        
        .register-btn:active {
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
        
        .login-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
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
        
        .password-strength {
            margin-top: 5px;
            height: 4px;
            border-radius: 2px;
            background: var(--gray);
            overflow: hidden;
        }
        
        .password-strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h1>BONFIRE GAMES</h1>
            <p>Junte-se à comunidade</p>
        </div>
        
        <div class="register-card">
            <h2>Crie sua conta</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Nome completo <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Seu nome completo" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="handle">Nome de usuário</label>
                    <div class="input-with-icon">
                        <i class="fas fa-at"></i>
                        <input type="text" id="handle" name="handle" placeholder="seu-usuario (opcional)" value="<?= isset($_POST['handle']) ? htmlspecialchars($_POST['handle']) : '' ?>">
                    </div>
                    <div class="form-help">
                        Se não preencher, será usado seu nome.
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <div class="form-help">
                        A senha deve ter pelo menos 6 caracteres.
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-fill" id="passwordStrength"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar senha <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Digite a senha novamente" required>
                    </div>
                </div>
                
                <button type="submit" class="register-btn">
                    <i class="fas fa-user-plus"></i>
                    Criar conta
                </button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <div class="login-link">
                <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </div>
    </div>

    <script>
        // Validação de força da senha
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            
            passwordStrength.style.width = strength + '%';
            
            if (strength < 50) {
                passwordStrength.style.background = 'var(--danger)';
            } else if (strength < 75) {
                passwordStrength.style.background = 'var(--warning)';
            } else {
                passwordStrength.style.background = 'var(--success)';
            }
        });
        
        // Validação de confirmação de senha
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.style.borderColor = 'var(--danger)';
            } else {
                confirmPasswordInput.style.borderColor = 'var(--success)';
            }
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    </script>
</body>

</html>
