<?php
//credentials
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'anime_rating_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

//PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage(), 3, 'errors/db_error_log.txt'); //log errors to errors/db_error_log.txt
    die("A database error occurred. Please try again later.");
}

?>
