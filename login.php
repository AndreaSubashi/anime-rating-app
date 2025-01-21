<?php
session_start();

include 'db.php'; // Include the database connection file
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Check if the username exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // If the password is correct, start a session for the user
        session_regenerate_id(true); // Regenerate session ID for security
        $_SESSION['user_id'] = $user['id']; // Store the user ID in the session
        header("Location: index.php"); // Redirect to the main page
        exit;
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <?php if (!empty($error_message)): ?>
            <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
</body>
</html>
