<?php
require_once 'db_connection.php';

// Check if admin is logged in :)
if (!isset($_SESSION['author_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $article_id = $_GET['id'];
    $author_id = $_SESSION['author_id'];
    
    $stmt = $conn->prepare("DELETE FROM articles WHERE article_id = ? AND author_id = ?");
    $stmt->bind_param("ii", $article_id, $author_id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?msg=deleted");
    } else {
        header("Location: admin.php?error=delete_failed");
    }
} else {
    header("Location: admin.php");
}
exit; 