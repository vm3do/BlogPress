<?php
require_once 'db_connection.php';


    if (isset($_GET['id'])) {
        $article_id = intval($_GET['id']);
        
        $conn->query("UPDATE articles SET views = views + 1 WHERE article_id = $article_id");
    } else {

        header("Location: index.php");
        exit;
    }

    $article_query = $conn->query("SELECT articles.*, authors.author_name 
                                    FROM articles 
                                    JOIN authors ON articles.author_id = authors.author_id 
                                    WHERE article_id = $article_id");

        if ($article_query->num_rows > 0) {
            $article = $article_query->fetch_assoc();
        } else {
            echo "Article not found.";
            exit;
        }

    $comments_query = $conn->query("SELECT * FROM comments WHERE article_id = $article_id ORDER BY created_at DESC");



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Page</title>
    <link rel="stylesheet" href="css/article.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">BlogPress</a>
        <?php 

            if (isset($_SESSION['author_id'])) {
                echo '<ul><li><a href="admin.php" class="admin">Dashboard</a></li><li><a href="logout.php" class="logout" >Log out</a></li></ul>';
            } else {
                echo '<ul><li><a href="signup.php">Sign up</a></li><li><a href="login.php">Log in</a></li></ul>';
            }
        
        ?>
    </header>

    <div class="wrapper">
        <div class="container">

            <div class="article-header">
                <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                <p class="article-meta">
                    By <?php echo htmlspecialchars($article['author_name']); ?> | 
                    Published at <?php echo htmlspecialchars($article['created_at']); ?>
                </p>
            </div>

            <div class="article-content">
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
            </div>

            <div class="like-section">
                <?php

                if (!isset($_SESSION['liked_articles'])) {
                    $_SESSION['liked_articles'] = array();
                }
                
                $isLiked = in_array($article_id, $_SESSION['liked_articles']);
                $likeButtonClass = $isLiked ? 'liked' : '';
                ?>
                <form action="update_likes.php" method="POST">
                    <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                    <button type="submit" name="like_button" class="<?php echo $likeButtonClass; ?>">
                        <?php echo $isLiked ? 'Unlike' : 'Like'; ?>
                    </button>
                    <span><?php echo htmlspecialchars($article['likes']); ?> Likes</span>
                </form>
            </div>

            <div class="comments-section">
                <h3>Comments</h3>
                <div id="comments">
                    <?php while ($comment = $comments_query->fetch_assoc()): ?>
                        <div class="comment">
                            <p><strong>Anonymous:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
                    
                <div class="comment-form">
                    <form action="submit_comment.php" method="POST">
                        <textarea name="comment" rows="4" placeholder="Add a comment..." required></textarea>
                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                        <button type="submit">Post Comment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Ayadi</p>
    </footer>
</body>
</html>