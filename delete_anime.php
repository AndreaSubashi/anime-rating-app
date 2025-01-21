<?php
session_start();
include 'db.php'; // Include database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Get the logged-in user ID
    $anime_id = $_POST['anime_id'];

    // Delete the anime from the user's list
    $stmt = $pdo->prepare("DELETE FROM user_ratings WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$user_id, $anime_id]);

    // Redirect back to the user's anime list page
    header("Location: mylist.php");
    exit;
}
?>
