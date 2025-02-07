<?php
session_start();
include 'db.php';

//get anime ID from URL
if (isset($_GET['anime_id'])) {
    $anime_id = $_GET['anime_id'];

    //fetch() anime details from Jikan API
    $api_url = "https://api.jikan.moe/v4/anime/" . $anime_id;
    $response = file_get_contents($api_url);
    $anime_details = json_decode($response, true); //decode JSON response

    $anime = $anime_details['data'];
} else {
    die('Anime ID is required.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" maximum-scale=1.0>
    <title><?php echo $anime['title']; ?> - Anime Details</title>
    <link rel="stylesheet" href="styles/details.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/icon.png">

</head>
<body>
    <header class="header">
        <h1>Anime Details: <?php echo $anime['title']; ?></h1>
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
        <!-- extensive details -->
        <div class="anime-details">
            <img src="<?php echo $anime['images']['jpg']['image_url']; ?>" alt="<?php echo $anime['title']; ?>">
            <p><strong>Synopsis:</strong> <?php echo $anime['synopsis']; ?></p>
            <p><strong>Episodes:</strong> <?php echo $anime['episodes'] ?? 'Unknown'; ?></p>
            <p><strong>Score:</strong> <?php echo $anime['score'] ?? 'N/A'; ?></p>
            <p><strong>Rank:</strong> <?php echo $anime['rank'] ?? 'N/A'; ?></p>
            <p><strong>Popularity:</strong> <?php echo $anime['popularity'] ?? 'N/A'; ?></p>
            <p><strong>Members:</strong> <?php echo $anime['members'] ?? 'N/A'; ?></p>
            <p><strong>Status:</strong> <?php echo $anime['status']; ?></p>
            <p><strong>Source:</strong> <?php echo $anime['source']; ?></p>
            <p><strong>Duration:</strong> <?php echo $anime['duration'] ?? 'Unknown'; ?></p>
            <p><strong>Broadcast:</strong> <?php echo $anime['broadcast']['string'] ?? 'Unknown'; ?></p>
            <p><strong>Studio(s):</strong> <?php echo implode(', ', array_column($anime['studios'], 'name')); ?></p>
            <p><strong>Genres:</strong> <?php echo implode(', ', array_column($anime['genres'], 'name')); ?></p>
            <p><strong>Demographics:</strong> <?php echo implode(', ', array_column($anime['demographics'], 'name')); ?></p>
            <!-- trailer -->
            <?php if (isset($anime['trailer']['embed_url'])): ?>
                <p><strong>Trailer:</strong></p><br>
                <iframe width="560" height="315" src="<?php echo $anime['trailer']['embed_url']; ?>" 
                    frameborder="0" allowfullscreen></iframe>
            <?php endif; ?>
            </ul>
        </div>
    </main>
    <script src="js/dropdown.js"></script>
    <script src="js/header.js"></script>
</body>
</html>
