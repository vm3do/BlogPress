<?php
require_once 'db_connection.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT author_id, author_name, password FROM authors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($author_id, $author_name, $hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['author_id'] = $author_id;
        $_SESSION['author_name'] = $author_name;
        header('Location: admin.php');
        exit;
    } else {
        $error_message = 'Invalid email or password. Please try again.';
    }
    $stmt->close();
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
        <h1>Welcome !</h1>
        <p>Glad to see you again !</p>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="inputs">
                <input type="email" id="email" name="email" placeholder="your email" required>
            </div>

            <div class="inputs">
                <input type="password" id="password" name="password" placeholder="strong password" required>
            </div>
            <button type="submit">Log In</button>
        </form>
    </div>

    

</body>
</html>