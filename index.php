<?php
session_start();
include 'db.php'; // Include database connection file

$anime_list = [];
$search_query = ''; // Initialize search query
$user_anime_ids = []; // Initialize array to store the anime IDs in the user's list
$excluded_genre = 'Hentai';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get the logged-in user ID

    // Fetch the anime IDs from the user's list
    $stmt = $pdo->prepare("SELECT anime_id FROM user_ratings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_anime_ids = $stmt->fetchAll(PDO::FETCH_COLUMN); // Get all anime_ids in the user's list
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page (default: 1)
$api_url = ""; // Initialize API URL

// Check if there’s a search query
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $api_url = "https://api.jikan.moe/v4/anime?q=" . urlencode($search_query) . "&page=" . $page; // API URL with search query and page
} else {
    $api_url = "https://api.jikan.moe/v4/top/anime?page=" . $page ; // Top anime API endpoint with page
}

// Fetch data from the Jikan API
$response = file_get_contents($api_url);
$anime_list = json_decode($response, true); // Decode the JSON response

// Filter out anime with the excluded genre
$anime_list['data'] = array_filter($anime_list['data'], function($anime) use ($excluded_genre) {
    // Check if any genre in the anime contains the excluded genre
    foreach ($anime['genres'] as $genre) {
        if (strtolower($genre['name']) == strtolower($excluded_genre)) {
            return false; // Exclude this anime if the genre matches
        }
    }
    return true; // Include the anime if no excluded genre is found
});

// Total pages from Jikan API (if available)
$total_pages = $anime_list['pagination']['last_visible_page'] ?? 1;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Rating Website</title>
    <link rel="stylesheet" href="styles.css?v=1.0"> <!-- Add a stylesheet -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery for AJAX -->
</head>
<body>
    <header>
        <h1>Anime Rating Website</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="mylist.php">My List</a>
                <a href="logout.php">Logout</a>
                <a href="stats.php">User Stats</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <main>
        <!-- Search Form -->
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search for anime..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <h2><?php echo $search_query ? 'Search Results' : 'Popular Anime'; ?></h2>
        <div class="anime-list">
            <?php foreach ($anime_list['data'] as $anime): ?>
                <div class="anime-item" id="anime-<?php echo $anime['mal_id']; ?>">
                    <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>" loading="lazy">
                    <h3><a href="anime_details.php?anime_id=<?php echo $anime['mal_id']; ?>" target="_blank"><?php echo $anime['title']; ?></a></h3>
                    <p>Episodes: <?php echo $anime['episodes'] ?? 'Unknown'; ?></p>
                    <p>Score: <?php echo $anime['score'] ?? 'N/A'; ?></p>

                    <!-- Check if anime is already added to the user's list -->
                    <?php if (in_array($anime['mal_id'], $user_anime_ids)): ?>
                        <p>Already added</p>
                    <?php else: ?>
                        <form class="add-to-list-form" method="POST" action="add_to_list.php">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['mal_id']; ?>">
                            <input type="hidden" name="anime_title" value="<?php echo $anime['title']; ?>">
                            <input type="hidden" name="anime_image_url" value="<?php echo $anime['images']['jpg']['image_url']; ?>">
                            <button type="submit">Add to My List</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

<!-- Pagination Controls -->
        <div class="pagination">
            <?php
            // Number of visible page links
            $visible_pages = 5;

            // Calculate the start and end of the pagination window
            $start_page = max(1, $page - floor($visible_pages / 2));
            $end_page = min($total_pages, $start_page + $visible_pages - 1);

            // Adjust start page if near the end
            $start_page = max(1, $end_page - $visible_pages + 1);

            // Display "Previous" button
            if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">Previous</a>
            <?php endif; ?>

            <!-- Display page links -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Display "Next" button -->
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">Next</a>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Handle form submission with AJAX
        $(".add-to-list-form").on("submit", function(event) {
            event.preventDefault();
            var form = $(this);
            var button = form.find("button");
            var animeId = form.find("input[name='anime_id']").val(); // Get the anime_id
            button.prop('disabled', true); // Disable the button to prevent multiple submissions
            
            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === "success") {
                        alert(data.message);
                        // Replace the button with "Already added" after successful addition
                        $("#anime-" + animeId + " .add-to-list-form").html('<p>Already added</p>');
                    } else {
                        alert(data.message);
                    }
                },
                error: function() {
                    alert("An error occurred. Please try again.");
                },
                complete: function() {
                    button.prop('disabled', false); // Re-enable the button after completion
                }
            });
        });
    </script>
</body>
</html>
