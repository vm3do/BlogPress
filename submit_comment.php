<?php
    require_once 'db_connection.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['article_id'])) {
        $article_id = intval($_POST['article_id']);
        $comment = $conn->real_escape_string($_POST['comment']);
        $conn->query("INSERT INTO comments (article_id, content, created_at) VALUES ($article_id, '$comment', NOW())");
        header("Location: article.php?id=$article_id");
        exit;
    }
    
?>
