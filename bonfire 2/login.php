<?php
session_start();
include('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM usuários WHERE Email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['Password'])) {
    $_SESSION['user_id'] = $user['CustomerID'];
    $_SESSION['user_name'] = $user['Name'];
    header("Location: index.php");
    exit;
  } else {
    $erro = "Credenciais inválidas.";
  }
}
?>

<form method="POST">
  <input type="email" name="email" placeholder="email" required>
  <input type="password" name="password" placeholder="senha" required>
  <button type="submit">Entrar</button>
</form>

<?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
