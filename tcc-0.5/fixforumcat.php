<?php
require_once 'config.php';

echo "<h1>Correção do Fórum</h1>";

// 1. Mostrar situação atual
echo "<h2>Situação Atual:</h2>";

$stmt = $pdo->query("
    SELECT fc.CategoryID, fc.CategoryName, COUNT(ft.TopicID) as topic_count 
    FROM forum_categories fc 
    LEFT JOIN forum_topics ft ON fc.CategoryID = ft.CategoryID 
    GROUP BY fc.CategoryID
");
$situation = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1'>";
echo "<tr><th>CategoryID</th><th>CategoryName</th><th>Tópicos</th></tr>";
foreach ($situation as $row) {
    echo "<tr>";
    echo "<td>{$row['CategoryID']}</td>";
    echo "<td>{$row['CategoryName']}</td>";
    echo "<td>{$row['topic_count']}</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Corrigir os tópicos para usar categorias válidas
echo "<h2>Corrigindo tópicos...</h2>";

$corrections = [
    // Mover tópicos da categoria 2 (Desenvolvimento antigo) para 14 (Desenvolvimento novo)
    "UPDATE forum_topics SET CategoryID = 14 WHERE CategoryID = 2" => "Categoria 2 → 14",
    // Mover tópicos da categoria 17 (Recursos) - já está correto
    "UPDATE forum_topics SET CategoryID = 17 WHERE CategoryID = 17" => "Categoria 17 → 17 (mantém)",
];

foreach ($corrections as $sql => $desc) {
    try {
        $affected = $pdo->exec($sql);
        echo "<p style='color: green;'>✓ $desc: $affected tópicos afetados</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro em $desc: " . $e->getMessage() . "</p>";
    }
}

// 3. Remover categorias duplicadas antigas
echo "<h2>Limpando categorias duplicadas...</h2>";

try {
    $pdo->exec("DELETE FROM forum_categories WHERE CategoryID < 13");
    echo "<p style='color: green;'>✓ Categorias antigas removidas</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao remover categorias: " . $e->getMessage() . "</p>";
}

echo "<h2>Correção concluída!</h2>";
echo "<p><a href='forum.php'>Ir para o Fórum</a></p>";
?>