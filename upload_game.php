<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['gameTitle'];
    $description = $_POST['gameDescription'];
    $genre = $_POST['gameGenre'];
    $franchise = $_POST['gameFranchise'];
    $status = $_POST['gameStatus'];
    
    $stmt = $pdo->prepare("
        INSERT INTO fangames (GameTitle, GameDescription, DeveloperID, Genre, Franchise, Status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$title, $description, $_SESSION['customer_id'], $genre, $franchise, $status])) {
        header("Location: fangames.php?success=1");
        exit;
    } else {
        header("Location: fangames.php?error=1");
        exit;
    }
}
?>