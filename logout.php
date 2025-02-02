<?php
session_start(); // Start the session
$_SESSION = [];
// Destroy the session to log the user out
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
// Redirect to the login page
header("Location: index.php");
exit;
?>
