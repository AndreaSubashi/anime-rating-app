<?php
// Database credentials
$host = 'localhost';
$dbname = 'anime_rating_db'; // Replace with your database name
$username = 'root'; // Default XAMPP MySQL username
$password = ''; // Default XAMPP MySQL password (empty by default)

// Set up the PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}
?>
