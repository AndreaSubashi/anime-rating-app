<?php
session_start();
include 'db.php'; // Include database connection file

$anime_list = [];
$search_query = ''; // Initialize search query
$user_anime_ids = []; // Initialize array to store the anime IDs in the user's list
$excluded_genre = 'Hentai'; // Remove 18+ shows

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
$category = isset($_GET['category']) ? $_GET['category'] : 'top'; // Default to 'top' if no category is set
$api_url = ""; // Initialize API URL

// Check if there’s a search query or category
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $api_url = "https://api.jikan.moe/v4/anime?q=" . urlencode($search_query) . "&page=" . $page; // Search query API URL
} else {
    // Set API URL based on category
    switch ($category) {
        case 'seasonal':
            // Seasonal anime
            $api_url = "https://api.jikan.moe/v4/seasons/now?page=" . $page;
            break;
        case 'popular':
            // Popular anime
            $api_url = "https://api.jikan.moe/v4/top/anime?page=" . $page;
            break;
        case 'top':
        default:
            // Top anime (default)
            $api_url = "https://api.jikan.moe/v4/top/anime?page=" . $page;
            break;
    }
}

// Fetch data from the Jikan API
$response = file_get_contents($api_url);
$anime_list = json_decode($response, true); // Decode the JSON response

if ($category === 'popular') {
    usort($anime_list['data'], function($a, $b) {
        return $b['members'] - $a['members']; // Compare members count in descending order
    });
}

// Filter out anime with the excluded genre
$anime_list['data'] = array_filter($anime_list['data'], function($anime) use ($excluded_genre) {
    foreach ($anime['genres'] as $genre) {
        if (strtolower($genre['name']) == strtolower($excluded_genre)) {
            return false; // Exclude if the genre matches
        }
    }
    return true; // Include anime if no excluded genre found
});

// Total pages from Jikan API (if available)
$total_pages = $anime_list['pagination']['last_visible_page'] ?? 1;

?>

<!-- HTML code remains the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Rating Website</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add a stylesheet -->
    <link rel="stylesheet" href="header.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery for AJAX -->
</head>
<body>
    <header class="header">
        <h1>Anime Rating Website</h1>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="index.php?category=top">Top Anime</a>
            <a href="index.php?category=popular">Popular Shows</a>
            <a href="index.php?category=seasonal">Seasonal Shows</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="mylist.php">My List</a>
                <a href="stats.php">User Stats</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="signup.php">Sign Up</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="dropdown">
        <button class="dropbtn">Dropdown</button>
        <div class="dropdown-content">
            <a href="index.php">Home</a>
            <a href="index.php?category=top">Top Anime</a>
            <a href="index.php?category=popular">Popular Shows</a>
            <a href="index.php?category=seasonal">Seasonal Shows</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="mylist.php">My List</a>
                <a href="stats.php">User Stats</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="signup.php">Sign Up</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    
    <main>
        <!-- Search Form -->
         <div class="searchbar">
            <form method="GET" action="index.php" class="searchform">
                <input type="text" name="search" placeholder="Search for anime..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2 class="category"><?php echo $search_query ? 'Search Results' : ucfirst($category) . ' Anime'; ?></h2>
        <div class="anime-list">
            <?php foreach ($anime_list['data'] as $anime): ?>
                <div class="anime-item" id="anime-<?php echo $anime['mal_id']; ?>">
                    <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>" loading="lazy" class="animeimg">
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
                            <button type="submit" class="addtolistbtn">Add to My List</button>
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
                <a href="?page=<?php echo $page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>">Previous</a>
            <?php endif; ?>

            <!-- Display page links -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Display "Next" button -->
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>">Next</a>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Handle form submission with AJAX
        $(".add-to-list-form").on("submit", function(event) {
            event.preventDefault();
            var form = $(this);
            var button = form.find("button");
            var originalText = button.text();
            button.prop('disabled', true).text('Adding...'); // Show loading state

            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === "success") {
                        alert(data.message);
                        form.html('<p>Already added</p>'); // Replace form with message
                    } else {
                        alert(data.message);
                        button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert("An error occurred. Please try again.");
                    button.prop('disabled', false).text(originalText);
                }
            });
        });

        const header = document.querySelector('.header');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) { // Adjust threshold as needed
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
