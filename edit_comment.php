<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
]);
include 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $anime_id = $_POST['anime_id'];
    $comment = $_POST['comment'];

    // Update the comment for the given anime
    $stmt = $pdo->prepare("UPDATE user_ratings SET anime_comment = ? WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$comment, $user_id, $anime_id]);

    echo json_encode(['status' => 'success', 'message' => 'Comment updated successfully.']);
    exit;
}
?>
