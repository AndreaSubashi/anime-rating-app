<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
]);
include 'db.php';

//make sure user is logged-in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
        exit;
    }

    $user_id = $_SESSION['user_id']; //get logged-in user ID
    $anime_id = $_POST['anime_id'];
    $new_rating = $_POST['rating'];

    //update  rating in  user_ratings table
    $stmt = $pdo->prepare("UPDATE user_ratings SET rating = ? WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$new_rating, $user_id, $anime_id]);

    echo json_encode(['status' => 'success', 'message' => 'Rating updated successfully.']);
    exit;
}
?>
