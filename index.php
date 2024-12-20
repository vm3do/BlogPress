<?php 

    require_once 'db_connection.php';

    
    $query = "SELECT article_id, title, content FROM articles";
    $result = $conn->query($query);

    function getBackgroundForCategory($category) {
        $backgrounds = [
            'Tech' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
            'Literature' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
            'Travel' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'Health' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?q=80&w=1932&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'Food' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
            'Education' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
            'Business' => 'https://images.unsplash.com/photo-1664575602276-acd073f104c1?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'Sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'Entertainment' => 'https://images.unsplash.com/photo-1486572788966-cfd3df1f5b42?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            'Science' => 'https://images.unsplash.com/photo-1617791160536-598cf32026fb?q=80&w=1964&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
        ];
        return $backgrounds[$category] ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080';
    }
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta type="theme-color" content="#fff">
    <title>BlogPress</title>
    <link rel="stylesheet" href="css/style.css">
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

    <main class="trends">
        <h1>Trending Articles</h1>
        
        <?php
        // Get top 3 articles by views
        $trendingStmt = $conn->prepare("SELECT articles.*, authors.author_name 
                                        FROM articles 
                                        JOIN authors ON articles.author_id = authors.author_id 
                                        ORDER BY articles.views DESC 
                                        LIMIT 3");
        $trendingStmt->execute();
        $trendingResult = $trendingStmt->get_result();

        while ($article = $trendingResult->fetch_assoc()) {
            $backgroundImage = getBackgroundForCategory($article['category']);
            echo '
            <div class="article">
                <div class="background">
                    <img src="' . $backgroundImage . '" alt="Category Image">
                </div>
                <div class="title">
                    <p>' . htmlspecialchars($article['title']) . '</p>
                </div>
                <div class="content">
                    <p>' . htmlspecialchars($article['content']) . '...</p>
                </div>
                <div class="read-more">
                    <a href="article.php?id=' . $article['article_id'] . '">Read More</a>
                </div>
            </div>';
        }
        ?>
    </main>

    <div class="articles">
        <h2>All Articles</h2>

        <?php

        $stmt = $conn->prepare("SELECT articles.*, authors.author_name FROM articles 
                                JOIN authors ON articles.author_id = authors.author_id 
                                ORDER BY articles.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($article = $result->fetch_assoc()) {
            $backgroundImage = getBackgroundForCategory($article['category']);
            echo '
            <div class="article">
                <div class="background">
                    <img src="' . $backgroundImage . '" alt="Category Image">
                </div>
                <div class="title">
                    <p>' . htmlspecialchars($article['title']) . '</p>
                </div>
                <div class="content">
                    <p>' . htmlspecialchars($article['content']) . '...</p>
                </div>
                <div class="read-more">
                    <a href="article.php?id=' . $article['article_id'] . '">Read More</a>
                </div>
            </div>';
        }
        ?>
        
    </div>

    </div>


</body>
</html>