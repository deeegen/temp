<?php
// Clear the user_id cookie
setcookie('user_id', '', time() - 3600, "/");

// Redirect to the login page or home page
header("Location: login.php"); // Adjust this URL to your login page or home page
exit;
?>