<?php
include 'ban_check.php';
checkBan();

// Define constants for file paths
if (!defined('USER_DATA_FILE')) {
    define('USER_DATA_FILE', 'data/users/users.json');
}
if (!defined('USER_IMG_DIR')) {
    define('USER_IMG_DIR', 'data/users/img/');
}

// Add the logout functionality
if (isset($_POST['logout'])) {
    setcookie('user_id', '', time() - 3600, '/');
    header("Location: login.php");
    exit;
}

// Function to get user data from the JSON file
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

// Function to save updated user data to the JSON file
function saveUserData($users) {
    file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// Function to handle file uploads
function handleFileUpload() {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destination = USER_IMG_DIR . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destination)) {
                return $newFileName;
            }
        }
    }
    return null;
}

// Start the session
session_start();
$userId = $_COOKIE['user_id'] ?? null;
$userData = getUserData($userId);

if (!$userData) {
    header("Location: signup.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updatedData = $userData;

    // Handle profile image upload
    $uploadedImage = handleFileUpload();
    if ($uploadedImage) {
        $updatedData['image'] = USER_IMG_DIR . $uploadedImage;
    }

    // Handle username update
    if (isset($_POST['username']) && $_POST['username'] !== $userData['username']) {
        $lastUpdated = strtotime($userData['last_username_update'] ?? '1970-01-01');
        if (time() - $lastUpdated >= 864000) { // 10 days in seconds
            $updatedData['username'] = htmlspecialchars($_POST['username']);
            $updatedData['last_username_update'] = date('Y-m-d');
        } else {
            echo "<p>You can only change your username once every 10 days.</p>";
        }
    }

    // Handle status and description updates
    if (isset($_POST['status'])) {
        $updatedData['status'] = htmlspecialchars($_POST['status']);
    }
    if (isset($_POST['description'])) {
        $updatedData['description'] = htmlspecialchars($_POST['description']);
    }

    // Update user data in the JSON file
    $json_data = file_get_contents(USER_DATA_FILE);
    $users = json_decode($json_data, true);
    foreach ($users as $key => $user) {
        if ($user['user_id'] == $userId) {
            $users[$key] = $updatedData;
            break;
        }
    }
    saveUserData($users);

    // Refresh user data after update
    $userData = getUserData($userId);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 50%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            padding: 8px;
        }

        .form-group button {
            background-color: #5c3737;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #4a2d2d;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Settings</h1>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <img src="<?php echo htmlspecialchars($userData['image']); ?>" alt="Profile Image" class="profile-image">
                <label for="image">Change Profile Image:</label>
                <input type="file" name="image" id="image">
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>">
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <input type="text" name="status" id="status" value="<?php echo htmlspecialchars($userData['status'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($userData['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Save Changes</button>
            </div>
            <div class="form-group">
        <form method="post">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
        </form>
    </div>
</body>
</html>