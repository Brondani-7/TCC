<?php
include('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $name = $_POST['name'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO usuários (Email, Name, Password) VALUES (?, ?, ?)");
  $stmt->execute([$email, $name, $password]);

  header("Location: login.php");
}
?>

<form method="POST">
  <input type="text" name="name" placeholder="Nome" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Senha" required>
  <button type="submit">Cadastrar</button>
</form>
