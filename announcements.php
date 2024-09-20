<?php
define('CHAT_LOG_FILE', 'data/chat/chatlogs_anno.json');
define('USER_DATA_FILE', 'data/users/users.json');

function getUserData($userId) {
    if (file_exists(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);
        $users = json_decode($json_data, true);
        
        foreach ($users as $user) {
            if ($user['user_id'] == $userId) {
                return $user;
            }
        }
    }
    return null;
}

function saveChatLog($log) {
    if (file_exists(CHAT_LOG_FILE)) {
        $json_data = file_get_contents(CHAT_LOG_FILE);
        $logs = json_decode($json_data, true);
    } else {
        $logs = [];
    }
    
    $logs[] = $log;
    file_put_contents(CHAT_LOG_FILE, json_encode($logs, JSON_PRETTY_PRINT));
}

session_start();
$userId = $_COOKIE['user_id'] ?? null;
$userData = getUserData($userId);

if (!$userData) {
    header("Location: signup.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);
    $currentTime = date('H:i');
    $log = [
        'time' => $currentTime,
        'username' => $userData['username'],
        'image' => $userData['image'],
        'rank' => $userData['rank'],
        'message' => $message
    ];
    saveChatLog($log);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <style>
        .rank-icon {
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }
        .chat-message {
            margin-bottom: 10px;
        }
        .chat-message img {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($userData['username']); ?>!</h1>
    <form method="post">
        <input type="text" name="message" placeholder="Type your message here..." required>
        <button type="submit">Send</button>
    </form>

    <h2>Chat Log:</h2>
    <?php
    if (file_exists(CHAT_LOG_FILE)) {
        $json_data = file_get_contents(CHAT_LOG_FILE);
        $logs = json_decode($json_data, true);
        
        foreach ($logs as $log) {
            $rankIcon = $log['rank'] === 'admin' ? 'image2.png' : 'image1.png';
            echo "<div class='chat-message'>";
            echo "<img src='data/users/img/{$log['image']}' alt='User Image' width='30' height='30'>";
            echo "<img src='data/users/img/{$rankIcon}' alt='Rank Icon' class='rank-icon'>";
            echo "<strong>" . htmlspecialchars($log['username']) . "</strong> <span>({$log['time']})</span>: ";
            echo htmlspecialchars($log['message']);
            echo "</div>";
        }
    }
    ?>
</body>
</html>