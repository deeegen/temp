<?php
define('USER_DATA_FILE', 'data/users/users.json');

$blacklist = ['r', 'root', 'bot'];

function getIP() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $ipArray = explode(',', $ip);
    return trim(end($ipArray));
}

function containsBlacklistedSubstring($username, $blacklist) {
    foreach ($blacklist as $badWord) {
        if (strpos($username, $badWord) !== false) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip = getIP();

    if (containsBlacklistedSubstring($username, $blacklist)) {
        $error = 'Username contains blacklisted text.';
    } else {
        if (file_exists(USER_DATA_FILE)) {
            $json_data = file_get_contents(USER_DATA_FILE);
            $users = json_decode($json_data, true);

            if ($users === null) {
                $users = [];
            }
        } else {
            $users = [];
        }

        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $error = 'Username already exists.';
                break;
            }
        }

        if (!isset($error)) {
            $userId = uniqid();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $newUser = [
                'username' => $username,
                'password' => $hashedPassword,
                'user_id' => $userId,
                'status' => '',
                'description' => '',
                'image' => 'data/users/img/default.png',
                'rank' => 'basic',
                'ip' => $ip,
                'banreason' => ''
            ];

            $users[] = $newUser;
            file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));

            setcookie('user_id', $userId, time() + (86400 * 365), "/");

            header("Location: welcome.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
</head>
<body>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>