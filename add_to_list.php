<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $anime_id = $_POST['anime_id'];
        $anime_title = $_POST['anime_title'];
        $comment = isset($_POST['comment']) ? $_POST['comment'] : ''; //get comment if it exists

        //heck if anime is already in user's list
        $stmt = $pdo->prepare("SELECT * FROM user_ratings WHERE user_id = ? AND anime_id = ?");
        $stmt->execute([$user_id, $anime_id]);
        $existing_entry = $stmt->fetch();

        if (!$existing_entry) {
            //fetch() anime details from Jikan API to get genres
            $api_url = "https://api.jikan.moe/v4/anime/$anime_id";
            $response = file_get_contents($api_url);
            $anime_details = json_decode($response, true);

            $genres = [];
            if (!empty($anime_details['data']['genres'])) {
                foreach ($anime_details['data']['genres'] as $genre) {
                    $genres[] = $genre['name'];
                }
            }
            $genres_string = implode(', ', $genres); //convert genres to string

            //get anime image URL
            if (!empty($anime_details['data']['images']['jpg']['image_url'])) {
                $image_url = $anime_details['data']['images']['jpg']['image_url'];
            }

            //insert anime with genres, image URL, and comment into DB
            $stmt = $pdo->prepare("INSERT INTO user_ratings (user_id, anime_id, anime_title, rating, anime_comment, anime_genres, anime_image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $anime_id, $anime_title, 0, $comment, $genres_string, $image_url]);

            //success message as JSON
            echo json_encode(['status' => 'success', 'message' => 'Anime added to your list']);
        } else {
            //return message saying the anime is already in list
            echo json_encode(['status' => 'error', 'message' => 'This anime is already in your list.']);
        }
    } else {
        //return message asking the user to log in first
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
    }
}
?>
