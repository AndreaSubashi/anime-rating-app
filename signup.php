<?php
session_start();
include 'db.php'; // Include the database connection file

$error_message = ''; // Initialize error message
$success_message = ''; // Initialize success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'])); // Sanitize the input
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if the username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success_message = "Account created successfully! <a href='login.php'>Login here</a>.";
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
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
</head>
<body>
    <header>
        <h1>Sign Up</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <main>
        <h2>Create an Account</h2>
        <form method="POST" action="signup.php">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required><br>

            <?php if (!empty($error_message)): ?>
                <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <p class="success-message" style="color: green;"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <button type="submit">Sign Up</button>
        </form>
    </main>
</body>
</html>
