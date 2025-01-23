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
    <link rel="stylesheet" href="styles.css?v=1.0">
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
                <div class="anime-item" id="anime-<?php echo $anime['anime_id']; ?>">
                    <img src="<?php echo $anime['anime_image_url']; ?>" alt="<?php echo $anime['anime_title']; ?>">
                    <h3><?php echo $anime['anime_title']; ?></h3>
                    <!-- Rating Update Form -->
                    <form class="update-rating-form" method="POST">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <label for="rating">Rating:</label>
                        <input type="number" name="rating" id="rating-<?php echo $anime['anime_id']; ?>" value="<?php echo $anime['rating']; ?>" min="1" max="10">
                        <button type="submit">Update Rating</button>
                    </form>

                    <!-- Comment Update Form -->
                    <form class="update-comment-form" method="POST">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <label for="comment">Comment:</label>
                        <textarea name="comment" id="comment-<?php echo $anime['anime_id']; ?>"><?php echo htmlspecialchars($anime['anime_comment']); ?></textarea>
                        <button type="submit">Update Comment</button>
                    </form>
                    
                    <!-- Delete Button -->
                    <form class="delete-anime-form" method="POST">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Handle the update of rating dynamically
        $(".update-rating-form").on("submit", function(event) {
            event.preventDefault();
            var form = $(this);
            var anime_id = form.find("input[name='anime_id']").val();
            var rating = form.find("input[name='rating']").val();

            $.ajax({
                url: "edit_rating.php",
                type: "POST",
                data: {
                    anime_id: anime_id,
                    rating: rating
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    alert(data.message);
                    if (data.status === "success") {
                        // Update the displayed rating
                        $("#rating-" + anime_id).val(rating);
                    }
                }
            });
        });

        // Handle the update of comment dynamically
        $(".update-comment-form").on("submit", function(event) {
            event.preventDefault();
            var form = $(this);
            var anime_id = form.find("input[name='anime_id']").val();
            var comment = form.find("textarea[name='comment']").val();

            $.ajax({
                url: "edit_comment.php",
                type: "POST",
                data: {
                    anime_id: anime_id,
                    comment: comment
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    alert(data.message);
                    if (data.status === "success") {
                        // Update the displayed comment
                        $("#comment-" + anime_id).text(comment);
                    }
                }
            });
        });

        // Handle the delete action dynamically
        $(".delete-anime-form").on("submit", function(event) {
            event.preventDefault();
            var form = $(this);
            var anime_id = form.find("input[name='anime_id']").val();

            $.ajax({
                url: "delete_anime.php",
                type: "POST",
                data: {
                    anime_id: anime_id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    alert(data.message);
                    if (data.status === "success") {
                        // Remove the anime item from the list
                        $("#anime-" + anime_id).remove();
                    }
                }
            });
        });
    </script>
</body>
</html>
