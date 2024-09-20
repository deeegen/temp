<?php
include 'ban_check.php';
checkBan();

// Check if constants are already defined
if (!defined('CHAT_LOG_FILE')) {
    define('CHAT_LOG_FILE', 'data/chat/chatlogs_anno.json');
}
if (!defined('USER_DATA_FILE')) {
    define('USER_DATA_FILE', 'data/users/users.json');
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', 'data/chat/uploads/');
}

// Define bot user
$botUser = [
    'user_id' => '0',
    'username' => 'Mr.Rock',
    'status' => 'Chat bot, at your service!',
    'description' => 'Hai, I\'m a bot! :3',
    'image' => 'data/users/img/default.png',
    'rank' => 'bot',
    'ip' => '69.69.69.69'
];

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

function clearChatLogs($user, $botUser) {
    $log = [
        'username' => $botUser['username'],
        'image' => $botUser['image'],
        'rank' => $botUser['rank'],
        'time' => date('H:i'),
        'message' => 'Cleared by ' . htmlspecialchars($user['username'] ?? 'Unknown')
    ];
    file_put_contents(CHAT_LOG_FILE, json_encode([$log], JSON_PRETTY_PRINT));
}

function handleFileUpload() {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destination = UPLOAD_DIR . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                return $newFileName;
            }
        }
    }
    return null;
}

function getMessageClass($username, $userData) {
    return $username === $userData['username'] ? 'right' : 'left';
}

// Start the session before any output
session_start();

$userId = $_COOKIE['user_id'] ?? null;
$userData = getUserData($userId);

if (!$userData) {
    header("Location: signup.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = htmlspecialchars($_POST['message'] ?? '');
    $currentTime = date('H:i');
    
    // Handle file upload
    $uploadedFile = handleFileUpload();

    // Check for admin or mod rank and handle the !clear command
    if (in_array($userData['rank'], ['admin', 'mod']) && stripos($message, '!clear') === 0) {
        clearChatLogs($userData, $GLOBALS['botUser']);
    } else {
        // Check for @username mentions
        $mentionedUser = null;
        if (preg_match('/@(\w+)/', $message, $matches)) {
            $mentionedUsername = $matches[1];
            foreach (json_decode(file_get_contents(USER_DATA_FILE), true) as $user) {
                if ($user['username'] === $mentionedUsername) {
                    $mentionedUser = $user;
                    break;
                }
            }
        }

        $log = [
            'username' => $userData['username'],
            'image' => $userData['image'],
            'rank' => $userData['rank'],
            'time' => $currentTime,
            'message' => $message,
            'file' => $uploadedFile ? UPLOAD_DIR . $uploadedFile : null,
            'mentionedUser' => $mentionedUser ? $mentionedUser['username'] : null
        ];
        
        saveChatLog($log);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #4a2c2c;
            margin: 0;
            padding-bottom: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        .container {
            width: 60%;
            max-width: 800px;
            margin: 0 auto;
            background-color: transparent;
            padding: 20px;
            border-radius: 10px;
            min-height: 56.3vh;
            max-height: 56.3vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .header {
            width: 60%;
            max-width: 800px;
            background-color: #b06363;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1px;
            text-align: left;
            color: #bfbfbf;
        }

        .subheader {
            width: 60%;
            max-width: 800px;
            background-color: #b06363;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2px;
            text-align: left;
            color: #bfbfbf;
        }

        h1, h2, h3 {
            margin: 10px 0;
            color: #bfbfbf;
        }

        .chat-message {
            background-color: #b07575;
            border: 1px solid #7a4f4f;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 3px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            width: 80%;
            text-align: left;
            /* Flex direction for layout adjustment */
            flex-direction: column;
        }

        .chat-message.left {
            justify-content: flex-start;
            float: left;
            margin-right: 20%; /* Adjust spacing */
        }

        .chat-message.right {
            justify-content: flex-end;
            float: right;
            margin-left: 20%; /* Adjust spacing */
        }

        .chat-message:hover {
            background-color: #c38585;
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .user-image {
            border-radius: 50%;
            margin-right: 12px;
            margin-top: 5px; /* Adjust this value to move the image down */
        }

        .rank-icon {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-left: -10px; /* Move the rank icon to the left */
        }

        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px; /* Space between header and message */
            position: relative;
        }

        .message-text {
            margin-top: 5px; /* Space between username and message */
            margin-left: 10px;
        }

        .username {
            padding-right: 10px;
            margin-left: -5px; /* Move the username to the left */
        }

        .timestamp {
            position: absolute;
            right: 0; /* Align to the right side of the container */
            margin-left: -20px; /* Move the timestamp slightly to the left */
            color: #bfbfbf;
            font-size: 0.9em;
        }

        form {
            background-color: #b06363;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        form input[type="text"] {
            width: calc(100% - 90px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 12px;
        }

        form input[type="file"] {
            background-color: #5c3737;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        form input:hover[type="file"] {
            background-color: #4a2d2d;
        }

        strong {
            color: #bfbfbf;
        }

        span {
            color: #bfbfbf;
            font-size: 0.9em;
        }
        .modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 40%;
    text-align: center;
    border-radius: 10px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.user-image {
    border-radius: 50%;
    margin-bottom: 10px;
}
    </style>
    <script>
    const loggedInUser = "<?php echo htmlspecialchars($userData['username']); ?>";

    function playNotificationSound() {
        const audio = new Audio('data/chat/file/ping.mp3');
        audio.play();
    }

    function highlightMentions() {
        document.querySelectorAll('.chat-message').forEach(message => {
            const mentionedUser = message.getAttribute('data-mentioned-user');
            if (mentionedUser && mentionedUser === loggedInUser) {
                message.style.backgroundColor = 'yellow';
                playNotificationSound();
            }
        });
    }

    function fetchChatLogs() {
        fetch('fetch_chat_logs.php')
            .then(response => response.json())
            .then(data => {
                const container = document.querySelector('.container');
                container.innerHTML = '';

                data.forEach(log => {
                    const mentionedUser = log.mentionedUser || '';
                    const messageClass = log.username === loggedInUser ? 'right' : 'left';
                    const rankIcon = log.rank === 'admin' ? 'admin.png' : (log.rank === 'bot' ? 'bot.png' : 'default.png');
                    const userDetails = JSON.stringify({
                        username: log.username,
                        status: log.status || 'No status available',
                        description: log.description || 'No description available',
                        image: log.image,
                        rank: log.rank
                    });

                    const messageHTML = `
                        <div class='chat-message ${messageClass}' data-mentioned-user='${mentionedUser}'>
                            <div class='chat-message-content'>
                                <div class='message-header'>
                                    <img src='${log.image}' alt='User Image' class='user-image' width='30' height='30' data-user='${userDetails}'>
                                    <strong class='username'>${log.username}</strong>
                                    <img src='data/users/rankimg/${rankIcon}' alt='Rank Icon' class='rank-icon'>
                                    <span class='timestamp'>${log.time}</span>
                                </div>
                                <div class='message-text'>${log.message}</div>
                            </div>
                        </div>
                    `;
                    
                    container.innerHTML += messageHTML;
                });

                highlightMentions();
                scrollToBottom();
            })
            .catch(error => console.error('Error fetching chat logs:', error));
    }

    function scrollToBottom() {
        const container = document.querySelector('.container');
        container.scrollTop = container.scrollHeight;
    }

    function handleScroll() {
        const container = document.querySelector('.container');
        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 1;

        if (isScrolledToBottom) {
            scrollToBottom();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('.container');
        scrollToBottom();
        container.addEventListener('scroll', handleScroll);

        setInterval(fetchChatLogs, 5000); // Fetch new chat logs every 5 seconds
    });

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('userProfileModal');
        const modalUsername = document.getElementById('modalUsername');
        const modalStatus = document.getElementById('modalStatus');
        const modalDescription = document.getElementById('modalDescription');
        const modalUserImage = document.getElementById('modalUserImage');
        const modalRankIcon = document.getElementById('modalRankIcon');
        const closeBtn = document.querySelector('.modal .close');

        document.querySelectorAll('.user-image').forEach(image => {
            image.addEventListener('click', function () {
                const userData = this.dataset.user;

                const user = JSON.parse(userData); // Parse user data stored in the dataset

                modalUsername.textContent = user.username;
                modalStatus.textContent = user.status;
                modalDescription.textContent = user.description;
                modalUserImage.src = user.image;
                modalRankIcon.src = `data/users/rankimg/${user.rank === 'admin' ? 'admin.png' : user.rank === 'bot' ? 'bot.png' : 'default.png'}`;

                modal.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($userData['username']); ?>!</h1>
    </div>
    <div class="subheader">
        <h3>#Chat</h3>
    </div>

    <div class="container">
    <?php
    if (file_exists(CHAT_LOG_FILE)) {
        $json_data = file_get_contents(CHAT_LOG_FILE);
        $logs = json_decode($json_data, true);

        foreach ($logs as $log) {
            // Set the $mentionedUser variable
            $mentionedUser = htmlspecialchars($log['mentionedUser'] ?? '');
        
            // Determine the message class based on user data
            $messageClass = getMessageClass($log['username'], $userData);
        
            // Set the $rankIcon variable based on the user's rank
            $rankIcon = $log['rank'] === 'admin' ? 'admin.png' : ($log['rank'] === 'bot' ? 'bot.png' : 'default.png');
        
            // Create a JSON string with user details for the data-user attribute
            $userDetails = json_encode([
                'username' => $log['username'],
                'status' => $log['status'] ?? 'No status available',
                'description' => $log['description'] ?? 'No description available',
                'image' => $log['image'],
                'rank' => $log['rank']
            ]);
        
            // Output the chat message
            echo "<div class='chat-message $messageClass' data-mentioned-user='$mentionedUser'>";
            echo "<div class='chat-message-content'>";
            echo "<div class='message-header'>";
            echo "<img src='{$log['image']}' alt='User Image' class='user-image' width='30' height='30' data-user='" . htmlspecialchars($userDetails, ENT_QUOTES, 'UTF-8') . "'>";
            echo "<strong class='username'>" . htmlspecialchars($log['username']) . "</strong>";
            echo "<img src='data/users/rankimg/{$rankIcon}' alt='Rank Icon' class='rank-icon'>";
            echo "<span class='timestamp'>{$log['time']}</span>";
            echo "</div>";
            echo "<div class='message-text'>" . htmlspecialchars($log['message']) . "</div>";
            echo "</div>";
            echo "</div>";
        }
        
    }
    ?>
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="text" name="message" placeholder="Type your message here..." required>
    </form>

    <div id="userProfileModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="modalUserImage" src="" alt="User Image" class="user-image" width="50" height="50">
        <img id="modalRankIcon" src="" alt="Rank Icon" class="rank-icon" width="20" height="20">
        <h3 id="modalUsername"></h3>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Description:</strong> <span id="modalDescription"></span></p>
    </div>
</div>

</body>
</html>