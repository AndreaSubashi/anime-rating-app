<?php
session_start([
    'cookie_lifetime' => 86400, //1 day
    'cookie_secure' => true, //use only with HTTPS
    'cookie_httponly' => true, //prevents JavaScript access to cookies
    'use_strict_mode' => true, //prevents session fixation
]);

include 'db.php';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    //check if username exists in DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        //if password is correct, start a session for user
        session_regenerate_id(true); //regenerate session ID for security
        $_SESSION['user_id'] = $user['id']; //store user ID in session
        header("Location: index.php"); //redirect to main page
        exit;
    } else {
        $error_message = "Invalid username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/sign_log.css">
    <link rel="icon" href="images/icon.png">

</head>
<body>
    <div class="wrapper">
        <form method="POST" action="login.php">
            <h1>Login</h1>
            <div class="input-box">
                <input type="text" name="username" id="username"  placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>  
                <?php if (!empty($error_message)): ?>
                    <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="signup.php">Register</a><br><br>
                <a href="index.php">Home</a>
            </div>
        </form>
    </div>
</body>
</html>
