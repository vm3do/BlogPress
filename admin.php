<?php
require_once 'db_connection.php';

if (!isset($_SESSION['author_id'])) {
    header('Location: login.php');
    exit;
}

$author_id = $_SESSION['author_id'];

$stats_query = $conn->prepare("SELECT 
    SUM(views) as total_views,
    SUM(likes) as total_likes,
    (SELECT COUNT(*) FROM comments WHERE article_id IN 
        (SELECT article_id FROM articles WHERE author_id = ?)) as total_comments
    FROM articles 
    WHERE author_id = ?");
$stats_query->bind_param("ii", $author_id, $author_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

// Get monthly stats for the chart
$chart_query = $conn->prepare("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    SUM(views) as views,
    SUM(likes) as likes,
    COUNT(DISTINCT article_id) as articles,
    (SELECT COUNT(*) FROM comments WHERE article_id IN 
        (SELECT article_id FROM articles WHERE author_id = ?) 
        AND DATE_FORMAT(comments.created_at, '%Y-%m') = DATE_FORMAT(a.created_at, '%Y-%m')) as comments
    FROM articles a
    WHERE author_id = ?
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6");
$chart_query->bind_param("ii", $author_id, $author_id);
$chart_query->execute();
$chart_data = $chart_query->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_id = $_SESSION['author_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];

    if (empty($title) || empty($content) || empty($category)) {
        die('All fields are required.');
    }

    $stmt = $conn->prepare("INSERT INTO articles (author_id, title, category, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $author_id, $title, $category, $content);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error publishing article: " . $stmt->error);
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Dashboard - BlogPress</title>
    <link rel="stylesheet" href="css/author.css">

</head>
<body>

    <header>
        <a href="index.php" class="logo">BlogPress</a>
        <ul>
            <li><a href="logout.php" class="deconnect">Log out</a></li>
        </ul>
    </header>

    <div class="dashboard">

        <div class="stats-container">
            <div class="stats-card">
                <h3><?php echo $stats['total_views'] ?? 0; ?></h3>
                <p>Total Views</p>
            </div>
            <div class="stats-card">
                <h3><?php echo $stats['total_likes'] ?? 0; ?></h3>
                <p>Total Likes</p>
            </div>
            <div class="stats-card">
                <h3><?php echo $stats['total_comments'] ?? 0; ?></h3>
                <p>Total Comments</p>
            </div>
        </div>

        <div class="chart">
            <canvas id="viewsChart"></canvas>
        </div>

        <div class="create-article-container">
            <h2>Create New Article</h2>
            <form id="createArticleForm" method="POST" action="admin.php">
                <input type="text" name="title" placeholder="Article Title" required>
                <textarea name="content" placeholder="Article Content" required></textarea>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Tech">Tech</option>
                    <option value="Literature">Literature</option>
                    <option value="Travel">Travel</option>
                    <option value="Health">Health</option>
                    <option value="Food">Food</option>
                    <option value="Education">Education</option>
                    <option value="Business">Business</option>
                    <option value="Sports">Sports</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Science">Science</option>
                </select>
                <button type="submit">Publish Article</button>
            </form>

        </div>

        <div class="articles-container">
            <h2>Your Articles</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Views</th>
                        <th>Likes</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $author_id = $_SESSION['author_id'];
                    $stmt = $conn->prepare("SELECT articles.*, 
                                            (SELECT COUNT(*) FROM comments WHERE comments.article_id = articles.article_id) AS total_comments 
                                            FROM articles WHERE author_id = ?");
                    $stmt->bind_param("i", $author_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($article = $result->fetch_assoc()) {
                        echo "<tr>
                                <td data-label='Title'>{$article['title']}</td>
                                <td data-label='Views'>{$article['views']}</td>
                                <td data-label='Likes'>{$article['likes']}</td>
                                <td data-label='Comments'>{$article['total_comments']}</td>
                                <td data-label='Actions'>
                                    <a href='edit_article.php?id={$article['article_id']}'><button>Edit</button></a>
                                    <a href='delete_article.php?id={$article['article_id']}'><button class='delete'>Delete</button></a>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer>
        <p>&copy; 2024 Ayadi</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('viewsChart').getContext('2d');
        const chartData = <?php echo json_encode(array_reverse($chart_data)); ?>;
        
        const viewsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(item => {
                    const [year, month] = item.month.split('-');
                    return new Date(year, month - 1).toLocaleDateString('default', { month: 'short', year: '2-digit' });
                }),
                datasets: [{
                    label: 'Views',
                    data: chartData.map(item => parseInt(item.views) || 0),
                    backgroundColor: 'rgba(47, 39, 206, 0.7)',
                    borderColor: '#2f27ce',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }, {
                    label: 'Likes',
                    data: chartData.map(item => parseInt(item.likes) || 0),
                    backgroundColor: 'rgba(39, 206, 79, 0.7)',
                    borderColor: '#27ce4f',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }, {
                    label: 'Comments',
                    data: chartData.map(item => parseInt(item.comments) || 0),
                    backgroundColor: 'rgba(206, 39, 39, 0.7)',
                    borderColor: '#ce2727',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#000',
                        bodyColor: '#000',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>
</body>
</html>