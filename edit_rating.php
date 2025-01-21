<?php
session_start();
include 'db.php'; // Include the database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Get the logged-in user ID
    $anime_id = $_POST['anime_id'];
    $new_rating = $_POST['rating'];

    // Update the rating in the user_ratings table
    $stmt = $pdo->prepare("UPDATE user_ratings SET rating = ? WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$new_rating, $user_id, $anime_id]);

    // Redirect back to the user's anime list page
    header("Location: mylist.php");
    exit;
}
?>
