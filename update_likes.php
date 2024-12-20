<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);

    if (!isset($_SESSION['liked_articles'])) {
        $_SESSION['liked_articles'] = array();
    }

    $isLiked = in_array($article_id, $_SESSION['liked_articles']);

    $updateQuery = $conn->prepare("UPDATE articles SET likes = likes " . ($isLiked ? "-1" : "+1") . " WHERE article_id = ?");
    $updateQuery->bind_param("i", $article_id);
    
    if ($updateQuery->execute()) {
        
        if ($isLiked) {

            $_SESSION['liked_articles'] = array_diff($_SESSION['liked_articles'], [$article_id]);
        } else {

            $_SESSION['liked_articles'][] = $article_id;
        }
    }

    header("Location: article.php?id=" . $article_id);
    exit;
}

header("Location: index.php");
exit;
?>
