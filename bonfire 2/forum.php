<?php include('includes/db.php'); ?>
<?php include('includes/header.php'); ?>

<?php
$forumID = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE PostID = ?");
$stmt->execute([$forumID]);

echo "<h2>Posts do Fórum</h2>";
while ($post = $stmt->fetch()) {
  echo "<div class='forum-card'>";
  echo "<p>" . htmlspecialchars($post['content']) . "</p>";
  echo "<small>Tags: " . htmlspecialchars($post['tags']) . "</small>";
  echo "</div>";
}
?>

<?php include('includes/footer.php'); ?>
