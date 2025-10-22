<?php
require_once 'includes/db.php';
include('includes/header.php');

$categoria = $_GET['categoria'] ?? null;
?>

<div class="layout">
  <aside class="sidebar">
    <div class="menu-icon">☰</div>
    <nav>
      <ul>
        <li><a href="index.php?categoria=rpg">RPG</a></li>
        <li><a href="index.php?categoria=estratégia">Estratégia</a></li>
        <li><a href="index.php?categoria=corrida">Corrida</a></li>
        <li><a href="index.php?categoria=ação">Ação</a></li>
        <li><a href="index.php?categoria=indie">Indie</a></li>
      </ul>
    </nav>
    
  </aside>

  <main class="content">
    <div class="profile-icon">👤</div>
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
