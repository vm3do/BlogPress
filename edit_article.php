<?php
require_once 'db_connection.php';

if (!isset($_SESSION['author_id'])) {
    header('Location: login.php');
    exit;
}

$article_id = isset($_GET['id']) ? $_GET['id'] : null;
$author_id = $_SESSION['author_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];

    if (empty($title) || empty($content) || empty($category)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, category = ? WHERE article_id = ? AND author_id = ?");
        $stmt->bind_param("sssii", $title, $content, $category, $article_id, $author_id);
        
        if ($stmt->execute()) {
            header("Location: admin.php?msg=updated");
            exit;
        } else {
            $error = "Error updating article: " . $stmt->error;
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM articles WHERE article_id = ? AND author_id = ?");
$stmt->bind_param("ii", $article_id, $author_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - BlogPress</title>
    <link rel="stylesheet" href="css/author.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">BlogPress</a>
        <ul>
            <li><a href="admin.php" class="admin">Dashboard</a></li>
            <li><a href="logout.php" class="deconnect">Log out</a></li>
        </ul>
    </header>

    <div class="dashboard">
        <div class="create-article-container">
            <h2>Edit Article</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            
            <form method="POST">
                <input type="text" name="title" placeholder="Article Title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                <textarea name="content" placeholder="Article Content" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                <select name="category" required>
                    <option value="" disabled>Select Category</option>
                    <?php
                    $categories = ['Tech', 'Literature', 'Travel', 'Health', 'Food', 'Education', 'Business', 'Sports', 'Entertainment', 'Science'];
                    foreach ($categories as $cat) {
                        $selected = ($cat === $article['category']) ? 'selected' : '';
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
                <button type="submit">Update Article</button>
            </form>
        </div>
    </div>
    
</body>
</html> 