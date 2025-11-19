<?php
require_once 'config.php';

echo "<h1>Diagnóstico do Fórum</h1>";

// Verificar tabelas
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'forum_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tabelas encontradas:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
        
        // Mostrar estrutura da tabela
        $stmt2 = $pdo->query("DESCRIBE $table");
        $structure = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>";
        foreach ($structure as $column) {
            echo "<li>{$column['Field']} ({$column['Type']})</li>";
        }
        echo "</ul>";
    }
    echo "</ul>";

    // Verificar dados nas tabelas
    if (in_array('forum_categories', $tables)) {
        $stmt = $pdo->query("SELECT * FROM forum_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Categorias:</h2>";
        echo "<pre>" . print_r($categories, true) . "</pre>";
    }

    if (in_array('forum_topics', $tables)) {
        $stmt = $pdo->query("SELECT * FROM forum_topics");
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Tópicos:</h2>";
        echo "<pre>" . print_r($topics, true) . "</pre>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>