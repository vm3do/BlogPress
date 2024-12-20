<?php 

    require_once 'db_connection.php';
    
    $error_message = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $author_name = $_POST['author-name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        }
        else if (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long.';
        }
        else {
            $stmt = $conn->prepare("SELECT author_id FROM authors WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $error_message = 'This email is already registered. Please use a different email.';
            } else {
                $stmt->close();
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("INSERT INTO authors (author_name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $author_name, $email, $hashed_password);
    
                if ($stmt->execute()) {
                    $_SESSION['author_id'] = $stmt->insert_id;
                    $_SESSION['author_name'] = $author_name;
                    header('Location: admin.php');
                    exit;
                } else {
                    $error_message = 'An error occurred. Please try again later.';
                }
            }
        }
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
        <ul>
            <li><a href="signup.php">Sign up</a></li>
            <li><a href="login.php">Log in</a></li>
        </ul>
    </header>

    <div class="register">
        <h1>Be The Author</h1>
        <p>Create articles which inspire people</p>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="signup.php" method="POST">
            <div class="inputs">
                <input type="text" id="name" name="author-name" placeholder="your name" required>
            </div>
            <div class="inputs">
                <input type="email" id="email" name="email" placeholder="your email" required>
            </div>

            <div class="inputs">
                <input type="password" id="password" name="password" placeholder="strong password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
    </div>

    

</body>
</html>