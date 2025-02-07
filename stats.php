<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
]);
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

//fetch all rated anime for user
$stmt = $pdo->prepare("SELECT * FROM user_ratings WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_anime_list = $stmt->fetchAll();

//calculate average rating
$total_score = 0;
$total_shows = count($user_anime_list);
$all_genres = [];

foreach ($user_anime_list as $anime) {
    $total_score += $anime['rating'];
    
    //collect genres
    if (!empty($anime['anime_genres'])) {
        $genres = explode(',', $anime['anime_genres']);
        foreach ($genres as $genre) {
            $genre = trim($genre); //remove whitespace
            if (!empty($genre)) {
                $all_genres[] = $genre;
            }
        }
    }
}

$average_score = $total_shows > 0 ? round($total_score / $total_shows, 2) : 0;

//calculate top 3 genres
$genre_counts = array_count_values($all_genres);
arsort($genre_counts);
$top_genres = array_slice($genre_counts, 0, 3, true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats</title>
    <link rel="stylesheet" href="styles/stats.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/icon.png">

</head>
<body>
    <header class="header">
        <h1>Anime Rating Website</h1>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="mylist.php">My List</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="dropdown">
        <button onclick="myFunction()" class="dropbtn">More</button>
        <div id="myDropdown" class="dropdown-content">
            <a href="index.php">Home</a>
            <a href="mylist.php">My List</a>
            <a href="logout.php">Logout</a>        
        </div>
    </div>
    
    <main>
        <div class="stats">
            <h2 class="category searchform" >Statistics</h2>
            <div class="stats-section">
                <h3>Average Rating</h3>
                <p>Your average rating: <strong><?php echo $average_score; ?></strong></p>
            </div>

            <div class="stats-section">
                <h3>Top Genres</h3>
                <?php if (!empty($top_genres)): ?>
                    <ol>
                        <?php foreach ($top_genres as $genre => $count): ?>
                            <li><?php echo htmlspecialchars($genre); ?> (<?php echo $count; ?>)</li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p>No genres available to calculate stats.</p>
                <?php endif; ?>
            </div>

            <div class="stats-section">
                <h3>Total Rated Anime</h3>
                <p>You have rated <strong><?php echo $total_shows; ?></strong> anime.</p>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/header.js"></script>

</body>
</html>
