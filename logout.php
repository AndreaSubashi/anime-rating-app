<?php
session_start(); //start session
$_SESSION = [];
//destroy session to log user out
session_destroy();
//used to delte session cookie
setcookie(session_name(), '', time() - 3600, '/');
//redirect to login page
header("Location: index.php");
exit;
?>
