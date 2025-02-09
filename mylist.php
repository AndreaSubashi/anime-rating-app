<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); //redirect to login if user is not logged in
    exit;
}

$user_id = $_SESSION['user_id']; //get logged-in user ID
$stmt = $pdo->prepare("SELECT * FROM user_ratings WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_anime_list = $stmt->fetchAll();

$anime_list = [];
$search_query = ''; //initialize search query

//check if there’s a search query
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $api_url = "https://api.jikan.moe/v4/anime?q=" . urlencode($search_query); //API URL with search query
    $response = file_get_contents($api_url);
    $anime_list = json_decode($response, true); //decode the JSON response for search results
} else {
    //if no search query, don't modify anime list (just display user’s anime list)
    $anime_list = ['data' => []];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Anime List</title>
    <link rel="stylesheet" href="styles/mylist.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/icon.png">

</head>
<body>
    <header class="header">
    <h1>Anime Rating Website</h1>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="stats.php">User Stats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="dropdown">
        <button onclick="myFunction()" class="dropbtn">More</button>
        <div id="myDropdown" class="dropdown-content">
            <a href="index.php">Home</a>
            <a href="stats.php">User Stats</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <main>
        <div class="searchbar">
            <form method="GET" action="mylist.php" class="searchform">
                <input type="text" name="search" placeholder="Search for anime..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2 class="category"><?php echo $search_query ? 'Search Results' : 'Your Rated Anime'; ?></h2>

        <!--search Results (from API) -->
        <?php if ($search_query && !empty($anime_list['data'])): ?>
            <div class="anime-list">
                <?php foreach ($anime_list['data'] as $anime): ?>
                    <div class="anime-item">
                        <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>">
                        <div class="anime-item-content">
                            <h3><?php echo $anime['title']; ?></h3>
                            <p>Episodes: <?php echo $anime['episodes'] ?? 'Unknown'; ?></p>
                            <p>Score: <?php echo $anime['score'] ?? 'N/A'; ?></p>
                            <form method="POST" action="add_to_list.php">
                                <input type="hidden" name="anime_id" value="<?php echo $anime['mal_id']; ?>">
                                <input type="hidden" name="anime_title" value="<?php echo $anime['title']; ?>">
                                <button type="submit">Add to My List</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($search_query): ?>
            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>". Try searching with a different term.</p>
        <?php endif; ?>

        <!--user's Anime List -->
        <div class="anime-list">
            <?php foreach ($user_anime_list as $anime): ?>
                <div class="anime-item" id="anime-<?php echo $anime['anime_id']; ?>">
                    <img src="<?php echo $anime['anime_image_url']; ?>" alt="<?php echo $anime['anime_title']; ?>">
                    <div class="anime-item-content">
                        <h3><a href="anime_details.php?anime_id=<?php echo $anime['anime_id']; ?>" target="_blank" class="link"><?php echo $anime['anime_title']; ?></a></h3>
                        <!--rating Update Form -->
                        <form class="update-rating-form interactive" method="POST">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                            <label for="rating" class="textlabel">Rating:</label>
                            <input type="number" name="rating" id="rating-<?php echo $anime['anime_id']; ?>" value="<?php echo $anime['rating']; ?>" min="1" max="10" class="ratingnum">
                            <button type="submit">Update Rating</button>
                        </form>

                        <!--comment Update Form -->
                        <form class="update-comment-form" method="POST">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                            <label for="comment" class="textlabel">Comment:</label>
                            <textarea name="comment" id="comment-<?php echo $anime['anime_id']; ?>"maxlength="200"><?php echo htmlspecialchars($anime['anime_comment']); ?></textarea>
                            <button type="submit">Update Comment</button>
                        </form>
                        
                        <!--delete Button -->
                        <form class="delete-anime-form interactive" method="POST">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/dynamic_interaction.js"></script>
    <script src="js/header.js"></script>
    <script src="js/dropdown.js"></script>
</body>
</html>
