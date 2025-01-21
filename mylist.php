<?php
session_start();
include 'db.php'; // Include database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID
$stmt = $pdo->prepare("SELECT * FROM user_ratings WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_anime_list = $stmt->fetchAll();

$anime_list = [];
$search_query = ''; // Initialize search query

// Check if there’s a search query
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $api_url = "https://api.jikan.moe/v4/anime?q=" . urlencode($search_query); // API URL with search query
    $response = file_get_contents($api_url);
    $anime_list = json_decode($response, true); // Decode the JSON response for search results
} else {
    // If no search query, don't modify the anime list (just display user’s anime list)
    $anime_list = ['data' => []];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Anime List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>My Anime List</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
            <a href="stats.php">User Stats</a>
        </nav>
    </header>
    
    <main>
        <!-- Search Form -->
        <form method="GET" action="mylist.php">
            <input type="text" name="search" placeholder="Search for anime..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <h2><?php echo $search_query ? 'Search Results' : 'Your Rated Anime'; ?></h2>

        <!-- Search Results (from Jikan API) -->
        <?php if ($search_query && !empty($anime_list['data'])): ?>
            <div class="anime-list">
                <?php foreach ($anime_list['data'] as $anime): ?>
                    <div class="anime-item">
                        <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>">
                        <h3><?php echo $anime['title']; ?></h3>
                        <p>Episodes: <?php echo $anime['episodes'] ?? 'Unknown'; ?></p>
                        <p>Score: <?php echo $anime['score'] ?? 'N/A'; ?></p>
                        <form method="POST" action="add_to_list.php">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['mal_id']; ?>">
                            <input type="hidden" name="anime_title" value="<?php echo $anime['title']; ?>">
                            <button type="submit">Add to My List</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($search_query): ?>
            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>". Try searching with a different term.</p>
        <?php endif; ?>

        <!-- User's Anime List -->
        <div class="anime-list">
            <?php foreach ($user_anime_list as $anime): ?>
                <div class="anime-item">
                    <h3><?php echo $anime['anime_title']; ?></h3>

                    <!-- Rating Update Form -->
                    <form method="POST" action="edit_rating.php">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <label for="rating">Rating:</label>
                        <input type="number" name="rating" id="rating" value="<?php echo $anime['rating']; ?>" min="1" max="10">
                        <button type="submit">Update Rating</button>
                    </form>

                    <!-- Comment Update Form -->
                    <form method="POST" action="edit_comment.php">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <label for="comment">Comment:</label>
                        <textarea name="comment" id="comment"><?php echo htmlspecialchars($anime['anime_comment']); ?></textarea>
                        <button type="submit">Update Comment</button>
                    </form>
                    
                    <!-- Delete Button -->
                    <form method="POST" action="delete_anime.php">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
