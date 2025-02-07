<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
]);
include 'db.php';


//make sure the user is logged-in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; //get logged-in user ID
    $anime_id = $_POST['anime_id'];

    //delete anime from the user's list
    $stmt = $pdo->prepare("DELETE FROM user_ratings WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$user_id, $anime_id]);

    echo json_encode(['status' => 'success', 'message' => 'Anime deleted successfully.']);
    exit;
}
?>
