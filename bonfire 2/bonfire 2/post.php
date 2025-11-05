<?php
include('includes/db.php');
session_start();

$postID = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
  $text = $_POST['text'];
  $userID = $_SESSION['user_id'];
  $date = date('Y-m-d');

  $stmt = $pdo->prepare("INSERT INTO comentarios (UserID, text, date) VALUES (?, ?, ?)");
  $stmt->execute([$userID, $text, $date]);
}

$stmt = $pdo->prepare("SELECT * FROM comentarios WHERE commentID IN (SELECT commentID FROM posts WHERE PostID = ?)");
$stmt->execute([$postID]);

echo "<h2>Comentários</h2>";
while ($comment = $stmt->fetch()) {
  echo "<div class='forum-card'>";
  echo "<p>" . htmlspecialchars($comment['text']) . "</p>";
  echo "<small>" . $comment['date'] . "</small>";
  echo "</div>";
}
?>

<?php if (isset($_SESSION['user_id'])): ?>
<form method="POST">
  <textarea name="text" placeholder="Deixe seu comentário" required></textarea>
  <button type="submit">Comentar</button>
</form>
<?php else: ?>
<p>Faça login para comentar.</p>
<?php endif; ?>
