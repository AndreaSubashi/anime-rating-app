<?php
session_start();
include 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $anime_id = $_POST['anime_id'];
    $comment = $_POST['comment'];

    // Update the comment for the given anime
    $stmt = $pdo->prepare("UPDATE user_ratings SET anime_comment = ? WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$comment, $user_id, $anime_id]);

    header("Location: mylist.php");
    exit;
}
?>
