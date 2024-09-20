<?php
if (!defined('USER_DATA_FILE')) {
    define('USER_DATA_FILE', 'data/users/users.json');
}

function getUserData($username) {
    $botUser = [
        'username' => 'Mr.Rock',
        'status' => 'Chat bot, at your service!',
        'description' => 'Hai, I\'m a bot! :3',
        'image' => 'data/users/img/default.png',
        'rank' => 'bot'
    ];

    if ($username === $botUser['username']) {
        return $botUser;
    }

    if (file_exists(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);
        $users = json_decode($json_data, true);

        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
    }
    return null;
}

$username = $_GET['username'] ?? null;
$userData = getUserData($username);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
        }

        .profile-container img.user-image {
            border-radius: 50%;
            width: 100px;
            height: 100px;
        }

        .profile-container .username-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
        }

        .profile-container .username-container h1 {
            margin: 0;
            font-size: 24px;
            margin-right: 10px;
        }

        .profile-container .rank-icon {
            width: 30px;
            height: 30px;
        }

        .profile-container p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <?php if ($userData): ?>
            <img src="<?php echo htmlspecialchars($userData['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="User Image" class="user-image">
            <div class="username-container">
                <h1><?php echo htmlspecialchars($userData['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <img src="data/users/rankimg/<?php echo htmlspecialchars($userData['rank'], ENT_QUOTES, 'UTF-8'); ?>.png" alt="Rank Icon" class="rank-icon">
            </div>
            <p><?php echo htmlspecialchars($userData['status'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Description: <?php echo htmlspecialchars($userData['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>