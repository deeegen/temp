<?php
define('USER_DATA_FILE', 'data/users/users.json');

function getUserByUsername($username) {
    $users = json_decode(file_get_contents(USER_DATA_FILE), true);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

function updateUser($username, $updates) {
    $users = json_decode(file_get_contents(USER_DATA_FILE), true);
    foreach ($users as &$user) {
        if ($user['username'] === $username) {
            $user = array_merge($user, $updates);
            file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
            return;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $banreason = 'test';

    $user = getUserByUsername($username);
    if ($user) {
        updateUser($username, [
            'rank' => 'banned',
            'banreason' => $banreason
        ]);
        echo 'User has been banned.';
    } else {
        echo 'User not found.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ban User</title>
</head>
<body>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <button type="submit">Ban User</button>
    </form>
</body>
</html>