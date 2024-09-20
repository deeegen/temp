<?php
session_start(); // Start the session to access session variables

define('USER_DATA_FILE', 'data/users/users.json');

// Function to get the user ID from a cookie
function getUserIdFromCookie() {
    return isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null;
}

// Function to get user data by ID
function getUserById($userId) {
    if (file_exists(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);
        $users = json_decode($json_data, true);

        if ($users === null) {
            return null;
        }

        foreach ($users as $user) {
            if ($user['user_id'] === $userId) {
                return $user;
            }
        }
    }
    return null;
}

// Function to update user data
function updateUser($userId, $newData) {
    if (file_exists(USER_DATA_FILE)) {
        $json_data = file_get_contents(USER_DATA_FILE);
        $users = json_decode($json_data, true);

        if ($users === null) {
            return false;
        }

        foreach ($users as &$user) {
            if ($user['user_id'] === $userId) {
                $user = array_merge($user, $newData);
                $result = file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
                return $result !== false;
            }
        }
    }
    return false;
}

// Handle ban or unban action
if (isset($_POST['ban_user_id'])) {
    $banUserId = $_POST['ban_user_id'];
    $banReason = isset($_POST['ban_reason']) ? $_POST['ban_reason'] : '';

    $user = getUserById($banUserId);
    if ($user && $user['rank'] === 'banned') {
        // Unban the user
        if (updateUser($banUserId, ['rank' => 'basic', 'banreason' => ''])) {
            $successMessage = "User has been unbanned successfully.";
        } else {
            $errorMessage = "Failed to unban the user. Please check file permissions and JSON format.";
        }
    } else {
        // Ban the user
        if (updateUser($banUserId, ['banreason' => $banReason, 'rank' => 'banned'])) {
            $successMessage = "User has been banned successfully.";
        } else {
            $errorMessage = "Failed to ban the user. Please check file permissions and JSON format.";
        }
    }
}

// Check if the user is logged in
$userId = getUserIdFromCookie();
if (!$userId) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$user = getUserById($userId);
if (!$user || $user['rank'] !== 'admin') {
    header("Location: access_denied.php"); // Redirect to access denied page if not an admin
    exit;
}

// Fetch all users
$allUsers = [];
if (file_exists(USER_DATA_FILE)) {
    $json_data = file_get_contents(USER_DATA_FILE);
    $allUsers = json_decode($json_data, true);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Page</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .ban-form {
            display: none;
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translate(-50%, 0);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .ban-form input, .ban-form button {
            margin: 5px 0;
        }
    </style>
    <script>
        function showBanForm(userId) {
            document.getElementById('ban_user_id').value = userId;
            var rank = document.getElementById('rank_' + userId).innerText;
            var banForm = document.getElementById('ban_form');
            if (rank === 'banned') {
                document.getElementById('ban_form_title').innerText = 'Unban User';
                document.getElementById('ban_reason').style.display = 'none';
            } else {
                document.getElementById('ban_form_title').innerText = 'Ban User';
                document.getElementById('ban_reason').style.display = 'block';
            }
            banForm.style.display = 'block';
        }
        function closeBanForm() {
            document.getElementById('ban_form').style.display = 'none';
        }
    </script>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>This is a restricted page that only admins can view.</p>

    <?php if (isset($successMessage)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <h2>All Users</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>UserID</th>
                <th>Status</th>
                <th>Description</th>
                <th>Rank</th>
                <th>IP Address</th>
                <th>Ban Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allUsers as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                    <td><?php echo htmlspecialchars($user['description']); ?></td>
                    <td id="rank_<?php echo htmlspecialchars($user['user_id']); ?>"><?php echo htmlspecialchars($user['rank']); ?></td>
                    <td><?php echo htmlspecialchars($user['ip']); ?></td>
                    <td><?php echo htmlspecialchars($user['banreason']); ?></td>
                    <td>
                        <?php if ($user['rank'] === 'banned'): ?>
                            <button onclick="showBanForm('<?php echo htmlspecialchars($user['user_id']); ?>')">Unban</button>
                        <?php else: ?>
                            <button onclick="showBanForm('<?php echo htmlspecialchars($user['user_id']); ?>')">Ban</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="ban_form" class="ban-form">
        <h3 id="ban_form_title">Ban User</h3>
        <form method="post">
            <input type="hidden" id="ban_user_id" name="ban_user_id">
            <div id="ban_reason_container">
                <label for="ban_reason">Ban Reason:</label>
                <input type="text" id="ban_reason" name="ban_reason">
                <br>
            </div>
            <button type="submit">Confirm</button>
            <button type="button" onclick="closeBanForm()">Cancel</button>
        </form>
    </div>
</body>
</html>
