<?php
session_start();
include 'db.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'])); //sanitize input
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        //check if username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            //hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            //insert user into DB
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success_message = "Account created successfully!";
            } else {
                $error_message = "An error occurred. Please try again later.";
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
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles/sign_log.css">
    <link rel="icon" href="images/icon.png">

</head>
<body>

    <main>
        <div class="wrapper">
            <form method="POST" action="signup.php">
                <h1>Sign Up</h1>
                <div class="input-box">
                    <input type="text" name="username" id="username" placeholder="Username" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                    <?php if (!empty($error_message)): ?>
                        <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <p class="success-message" style="color: green;"><?php echo $success_message; ?></p>
                    <?php endif; ?>
                <button type="submit" class="btn">Sign Up</button>
                <div class="register-link">
                    <p>Already have an account?</p>
                    <a href="login.php">Login</a><br><br>
                    <a href="index.php">Home</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
