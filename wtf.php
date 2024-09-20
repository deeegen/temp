<?php
include 'ban_check.php';
checkBan();

if (!defined('CHAT_LOG_FILE')) {
    define('CHAT_LOG_FILE', 'data/chat/chatlogs_anno.json');
}
if (!defined('USER_DATA_FILE')) {
    define('USER_DATA_FILE', 'data/users/users.json');
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', 'data/chat/uploads/');
}
if (!defined('CHANNELS_LAYOUT_FILE')) {
    define('CHANNELS_LAYOUT_FILE', 'data/chat/channels_layout.json');
}

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
        'message' => 'Cleared by ' . htmlspecialchars($user['username'] ?? 'Unknown'),
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
    if (in_array($userData['rank'], ['owner', 'admin', 'mod']) && stripos($message, '!clear') === 0) {
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
        /* Existing styles remain unchanged */
        body {
            font-family: Arial, sans-serif;
            background-color: #4a2c2c;
            margin: 0;
            padding-bottom: 80px;
            display: flex;
            flex-direction: row; /* Allow content to flow horizontally */
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            height: 100%;
            background-color: #333;
            color: #fff;
            padding: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar .header {
            position: relative;
            width: 100%;
            height: 150px; /* Adjust height according to your image size */
            overflow: hidden;
        }

        .sidebar .header img {
            width: 100%; /* Make image fit the width of the sidebar */
            height: 100%; /* Ensure the image covers the entire header area */
            object-fit: cover; /* Ensure the image covers the area without distortion */
            display: block;
        }

        .sidebar .header .text-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #fff;
            font-size: 15px; /* Adjust font size as needed */
            font-weight: bold;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            padding: 5px;
            border-radius: 5px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .sidebar ul li {
            margin: 10px 0;
            text-align: center;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
        }

        .sidebar ul li a:hover {
            background-color: #444;
        }

        .dropdown {
            position: relative;
            width: 100%;
        }

        .dropdown button {
            width: 100%;
            background-color: #444;
            color: #fff;
            border: none;
            padding: 10px;
            text-align: left;
            cursor: pointer;
            border-radius: 5px;
            outline: none;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #555;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 100%;
            border-radius: 5px;
            animation: slideDown 0.3s ease-out;
        }

        .dropdown-content a {
            color: #fff;
            padding: 10px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #666;
        }

        .dropdown.open .dropdown-content {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
            cursor: pointer; /* Add cursor pointer */
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
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Hide the actual file input */
        form input[type="file"] {
            display: none;
        }

        /* Style the upload image */
        .upload-button {
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 10px 5px 5px 10px; 
        }

        form input[type="text"] {
            width: 100%;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 12px;
        }

        /* Wrapper for the content (header and container) */
.content-wrapper {
    margin-left: 210px; /* Align with the container */
    width: calc(100% - 220px);
    display: flex;
    flex-direction: column; /* Stack the header above the container */
    margin-top: 10px; /* Adjust as needed to move content below top elements */
}

/* Styling for the chat header */
.chat-header {
    width: 73%;
    background-color: #333;
    color: #fff;
    margin-top: -11px;
    padding: 15px 30px;
    border-radius: 0 0 50px 0; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: left; /* Center-align the header text */
}

.chat-header p {
    margin: 0;
    font-size: 24px;
}

/* Ensure the container stays below the header */
.container {
    background-color: transparent;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid #7a4f4f;
    width: 77.3%;
    min-height: 68vh;
    max-height: 68vh;
    overflow-y: auto;
    overflow-x: hidden;
}

    </style>
    <script>
        const botUser = <?php echo json_encode($botUser); ?>;

        function handleImageClick(username) {
            window.location.href = `profiles.php?username=${encodeURIComponent(username)}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.user-image').forEach(image => {
                image.addEventListener('click', function () {
                    const username = this.dataset.username;
                    handleImageClick(username);
                });
            });

            // Add event listeners for dropdown buttons
            document.querySelectorAll('.dropdown button').forEach(button => {
                button.addEventListener('click', function () {
                    const dropdown = this.parentElement;
                    dropdown.classList.toggle('open');
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Trigger the file input when the image is clicked
            const uploadImage = document.getElementById('upload-image');
            const fileInput = document.querySelector('input[type="file"]');

            uploadImage.addEventListener('click', function() {
                fileInput.click();
            });
        });
    </script>
</head>
<body>
    <?php include 'elements/sidebar.php'; ?>

    <!-- Ensure the header stays above the container -->
    <div class="content-wrapper">
        <header class="chat-header">
            <p>main</p>
        </header>

        <div class="container">
    <?php
    if (file_exists(CHAT_LOG_FILE)) {
        $json_data = file_get_contents(CHAT_LOG_FILE);
        $logs = json_decode($json_data, true);

        foreach ($logs as $log) {
            $mentionedUser = htmlspecialchars($log['mentionedUser'] ?? '');
            $messageClass = getMessageClass($log['username'], $userData);
            // Update the rank icon selection to include 'owner'
            $rankIcon = $log['rank'] === 'owner' ? 'owner.png' : 
                        ($log['rank'] === 'admin' ? 'admin.png' : 
                        ($log['rank'] === 'bot' ? 'bot.png' : 'default.png'));
            $userDetails = json_encode([
                'username' => $log['username'],
                'status' => $log['status'] ?? 'No status available',
                'description' => $log['description'] ?? 'No description available',
                'image' => $log['image'],
                'rank' => $log['rank']
            ]);

            echo "<div class='chat-message $messageClass' data-mentioned-user='$mentionedUser'>";
            echo "<div class='chat-message-content'>";
            echo "<div class='message-header'>";
            echo "<img src='{$log['image']}' alt='User Image' class='user-image' width='30' height='30' data-username='" . htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') . "'>";
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
        <img src="data/chat/file/upload.png" alt="Upload" id="upload-image" class="upload-button">
        <input type="file" name="file">
        <input type="text" name="message" placeholder="Type your message here..." required>
    </form>

    <?php include 'elements/rightbar.php'; ?>
</body>

</html>