<?php
function getUserByCookie() {
    define('USER_DATA_FILE', 'data/users/users.json');

    if (file_exists(USER_DATA_FILE) && is_readable(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);

        $users = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Error reading user data.';
            exit;
        }

        if (isset($_COOKIE['user_id'])) {
            foreach ($users as $user) {
                if ($user['user_id'] === $_COOKIE['user_id']) {
                    return $user;
                }
            }
        }
    } else {
        echo 'User data file not found or not readable.';
        exit;
    }

    return null;
}

function checkBan() {
    $user = getUserByCookie();

    if ($user && $user['rank'] === 'banned') {
        echo '<!DOCTYPE html>
              <html>
              <head><title>Banned</title></head>
              <body>
              <h1>You have been banned</h1>
              <p>Username: ' . htmlspecialchars($user['username']) . '</p>
              <p>Ban Reason: ' . htmlspecialchars($user['banreason']) . '</p>
              </body>
              </html>';
        exit;
    }
}
?>