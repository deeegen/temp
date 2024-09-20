<?php
define('USER_DATA_FILE', 'data/users/users.json');

// Load users data
function loadUsersData() {
    if (file_exists(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);
        return json_decode($json_data, true);
    }
    return [];
}

// Update user's traffic status
function updateUserTraffic($userId, $status) {
    $users = loadUsersData();
    foreach ($users as &$user) {
        if ($user['user_id'] === $userId) {
            $user['traffic'] = $status;
            file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
            return;
        }
    }
}

// Handle status update via AJAX
if (isset($_POST['user_id']) && isset($_POST['status'])) {
    updateUserTraffic($_POST['user_id'], $_POST['status']);
    exit;
}

// Load all users
$users = loadUsersData();
$onlineUsers = array_filter($users, function($user) {
    return $user['traffic'] === 'online';
});
$offlineUsers = array_filter($users, function($user) {
    return $user['traffic'] === 'offline';
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .user-list {
            margin: 20px 0;
        }

        .user-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .user-image {
            border-radius: 50%;
            margin-right: 10px;
        }

        .status-online {
            color: green;
        }

        .status-offline {
            color: red;
        }

        .header {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">User List</div>

    <div class="user-list">
        <h2>Online Users</h2>
        <?php foreach ($onlineUsers as $user): ?>
            <div class="user-item">
                <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="User Image" class="user-image" width="50" height="50">
                <div>
                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    <p class="status-online">Online</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="user-list">
        <h2>Offline Users</h2>
        <?php foreach ($offlineUsers as $user): ?>
            <div class="user-item">
                <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="User Image" class="user-image" width="50" height="50">
                <div>
                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    <p class="status-offline">Offline</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Update the user status to 'online' when the user is on the page
        function updateUserStatus(status) {
            const userId = "<?php echo $_COOKIE['user_id']; ?>";
            if (userId) {
                fetch('user_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'user_id': userId,
                        'status': status
                    })
                });
            }
        }

        // Mark user as online when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            updateUserStatus('online');

            // Mark user as offline when the page unloads
            window.addEventListener('beforeunload', () => {
                updateUserStatus('offline');
            });
        });
    </script>
</body>
</html>