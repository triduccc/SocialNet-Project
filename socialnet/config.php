<?php

// Start session early so pages can use session-based protections (CSRF tokens, auth)
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$servername = "localhost";
$username = "socialuser";
$password = "socialpass";
$dbname = "socialnet";

?>
