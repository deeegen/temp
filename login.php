<?php
define('USER_DATA_FILE', 'data/users/users.json');

$blacklist = ['r', 'root', 'bot'];

function getIPv6() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function containsBlacklistedSubstring($username, $blacklist) {
    foreach ($blacklist as $badWord) {
        if (strpos($username, $badWord) !== false) {
            return true;
        }
    }
    return false;
}

function isUserBanned($user) {
    return $user['rank'] === 'banned';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip = getIPv6();

    if (containsBlacklistedSubstring($username, $blacklist)) {
        $error = 'Username contains blacklisted text.';
    } else {
        $users = json_decode(file_get_contents(USER_DATA_FILE), true);

        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                if (isUserBanned($user)) {
                    $error = 'You have been banned.<br>' . htmlspecialchars($user['username']) . '<br>' . htmlspecialchars($user['banreason']);
                } else {
                    setcookie('user_id', $user['user_id'], time() + (86400 * 365), "/");

                    echo 'Login successful.';
                    exit;
                }
            }
        }

        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
