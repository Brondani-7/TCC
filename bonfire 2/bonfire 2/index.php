<?php
require_once 'includes/db.php';
include('includes/header.php');

$categoria = $_GET['categoria'] ?? null;
?>

<div class="layout">
   

  <main class="content">
    <h2>Fóruns <?= $categoria ? ucfirst($categoria) : "Recentes" ?></h2>

    <?php
    $query = "SELECT * FROM fóruns";
    $params = [];

    if ($categoria) {
      $query .= " WHERE ForumName LIKE ?";
      $params[] = "%$categoria%";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($forum = $stmt->fetch()) {
      echo "<div class='forum-card'>";
      echo "<h3>" . htmlspecialchars($forum['ForumName']) . "</h3>";
      echo "<a href='forum.php?id=" . $forum['ForumID'] . "'>Ver Posts</a>";
      echo "</div>";
    }
    ?>
  </main>
</div>

<?php include('includes/footer.php'); ?>

  
