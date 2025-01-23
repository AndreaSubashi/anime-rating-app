<?php
session_start();
include 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
        exit;
    }

    $user_id = $_SESSION['user_id']; // Get the logged-in user ID
    $anime_id = $_POST['anime_id'];
    $new_rating = $_POST['rating'];

    // Update the rating in the user_ratings table
    $stmt = $pdo->prepare("UPDATE user_ratings SET rating = ? WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$new_rating, $user_id, $anime_id]);

    echo json_encode(['status' => 'success', 'message' => 'Rating updated successfully.']);
    exit;
}
?>
