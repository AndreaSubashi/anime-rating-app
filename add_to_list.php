<?php
session_start();
include 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get the logged-in user ID
        $anime_id = $_POST['anime_id'];
        $anime_title = $_POST['anime_title'];
        $comment = isset($_POST['comment']) ? $_POST['comment'] : ''; // Retrieve the comment if it exists

        // Check if the anime is already in the user's list
        $stmt = $pdo->prepare("SELECT * FROM user_ratings WHERE user_id = ? AND anime_id = ?");
        $stmt->execute([$user_id, $anime_id]);
        $existing_entry = $stmt->fetch();

        if (!$existing_entry) {
            // Fetch anime details from the Jikan API to get genres
            $api_url = "https://api.jikan.moe/v4/anime/$anime_id";
            $response = file_get_contents($api_url);
            $anime_details = json_decode($response, true);

            $genres = [];
            if (!empty($anime_details['data']['genres'])) {
                foreach ($anime_details['data']['genres'] as $genre) {
                    $genres[] = $genre['name'];
                }
            }
            $genres_string = implode(', ', $genres); // Convert genres to a string

            // Get the anime image URL
            if (!empty($anime_details['data']['images']['jpg']['image_url'])) {
                $image_url = $anime_details['data']['images']['jpg']['image_url'];
            }

            // Insert the anime with genres, image URL, and comment into the database
            $stmt = $pdo->prepare("INSERT INTO user_ratings (user_id, anime_id, anime_title, rating, anime_comment, anime_genres, anime_image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $anime_id, $anime_title, 0, $comment, $genres_string, $image_url]);

            // Return a success message as a JSON response
            echo json_encode(['status' => 'success', 'message' => 'Anime added to your list']);
        } else {
            // Return a message saying the anime is already in the list
            echo json_encode(['status' => 'error', 'message' => 'This anime is already in your list.']);
        }
    } else {
        // Return a message asking the user to log in first
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
    }
}
?>
