<?php
define('USER_DATA_FILE', 'data/users/users.json');

// Check if the user has signed in by checking the cookie
if (!isset($_COOKIE['user_id'])) {
    header("Location: signup.php");
    exit;
}

// Fetch user data
$user_id = $_COOKIE['user_id'];

if (file_exists(USER_DATA_FILE)) {
    $json_data = file_get_contents(USER_DATA_FILE);
    $users = json_decode($json_data, true);

    foreach ($users as $user) {
        if ($user['user_id'] === $user_id) {
            $username = $user['username'];
            break;
        }
    }
}

// If the username isn't found, redirect to signup
if (!isset($username)) {
    header("Location: signup.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Thank you for signing up.</p>
</body>
</html>
