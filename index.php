<?php
session_start();
include 'db.php';

$anime_list = [];
$search_query = ''; //initialize search query
$user_anime_ids = []; //initialize array to store anime IDs in user's list
$excluded_genre = 'Hentai'; //remove 18+ shows

//check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; //get logged-in user ID

    //fetch anime IDs from user's list
    $stmt = $pdo->prepare("SELECT anime_id FROM user_ratings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_anime_ids = $stmt->fetchAll(PDO::FETCH_COLUMN); //get all anime_ids in user's list
}

//pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; //current page (default: 1)
$category = isset($_GET['category']) ? $_GET['category'] : 'top'; //default to 'top' if no category is set
$api_url = ""; //initialize API URL

//check if there’s a search query or category
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $api_url = "https://api.jikan.moe/v4/anime?q=" . urlencode($search_query) . "&page=" . $page; // Search query API URL
} else {
    //set API URL based on category
    switch ($category) {
        case 'seasonal':
            //Seasonal anime
            $api_url = "https://api.jikan.moe/v4/seasons/now?page=" . $page;
            break;
        case 'popular':
            //Popular anime
            $api_url = "https://api.jikan.moe/v4/top/anime?page=" . $page;
            break;
        case 'top':
        default:
            //Top anime (default)
            $api_url = "https://api.jikan.moe/v4/top/anime?page=" . $page;
            break;
        
    }
}

//fetch data from Jikan API
$response = file_get_contents($api_url);
$anime_list = json_decode($response, true); //decode JSON response

if ($category === 'popular') {
    usort($anime_list['data'], function($a, $b) {
        return $b['members'] - $a['members']; //compare members count in descending order
    });
}

//filter out anime with the excluded genre
$anime_list['data'] = array_filter($anime_list['data'], function($anime) use ($excluded_genre) {
    foreach ($anime['genres'] as $genre) {
        if (strtolower($genre['name']) == strtolower($excluded_genre)) {
            return false; //exclude if the genre matches
        }
    }
    return true; //include anime if no excluded genre found
});

//total pages from Jikan API
$total_pages = $anime_list['pagination']['last_visible_page'] ?? 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Rating Website</title>
    <link rel="stylesheet" href="styles/main.css"> 
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/icon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="header">
        <h1>Anime Rating Website</h1>
        <nav class="navbar">
            <a href="index.php">Home (Top shows)</a>
            <a href="index.php?category=popular">Popular Shows</a>
            <a href="index.php?category=seasonal">Currently airing</a>
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
        <button onclick="myFunction()" class="dropbtn">More</button>
        <div id="myDropdown" class="dropdown-content">
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
                    <!-- Making the layout for each anime "card" -->
                    <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>" loading="lazy">
                    <h3><a href="anime_details.php?anime_id=<?php echo $anime['mal_id']; ?>" target="_blank"><?php echo $anime['title']; ?></a></h3>
                    <p>Episodes: <?php echo $anime['episodes'] ?? 'Unknown'; ?></p>
                    <?php 
                    if ($category === 'popular') {
                        echo "<p>Memebers:" . (number_format(round($anime['members'], -3), 0 , '', ',') ?? 'N/A') . "</p>"; 
                    }
                    else{
                        echo "<p>Score:" . ($anime['score'] ?? 'N/A') . "</p>";
                    }
                    ?>

                    <!--check if anime is already added to user's list -->
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

        <!--pagination Controls -->
        <div class="pagination">
            <?php
            //number of visible page links
            $visible_pages = 5;

            //calculate the start and end of the pagination window
            $start_page = max(1, $page - floor($visible_pages / 2));
            $end_page = min($total_pages, $start_page + $visible_pages - 1);

            //adjust start page if near end
            $start_page = max(1, $end_page - $visible_pages + 1);

            //display "Previous" button
            if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>">Previous</a>
            <?php endif; ?>

            <!--display page links -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!--display "Next" button -->
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>&category=<?php echo $category; ?>">Next</a>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/dynamic_add.js"></script>
    <script src="js/header.js"></script>
    <script src="js/dropdown.js"></script>
</body>
</html>
