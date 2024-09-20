<!-- sidebar.php -->
<?php

// Check if the user has signed in by checking the cookie
if (!isset($_COOKIE['user_id'])) {
    header("Location: signup.php");
    exit;
}

// Fetch user data
$user_id = $_COOKIE['user_id'];

if (file_exists(USER_DATA_FILE)) {
    $json_data = file_get_contents(USER_DATA_FILE);
    $users = json_decode($json_data, true);

    foreach ($users as $user) {
        if ($user['user_id'] === $user_id) {
            $username = $user['username'];
            $userImage = $user['image'];
            break;
        }
    }
}

// If the username or image isn't found, redirect to signup
if (!isset($username) || !isset($userImage)) {
    header("Location: signup.php");
    exit;
}
?>
<div class="sidebar">
    <!-- Existing content -->
    <div class="header">
        <img src="https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png" alt="Sidebar Image">
        <div class="text-overlay">Cerebral Incubation</div>
    </div>
    <ul>
        <li class="dropdown">
            <button>Important</button>
            <div class="dropdown-content">
                <a href="anno.php"># announcements</a>
                <a href="#">Sub-link 2</a>
                <a href="#">Sub-link 3</a>
            </div>
        </li>
        <li class="dropdown">
            <button>Main</button>
            <div class="dropdown-content">
                <a href="gen.php"># general</a>
                <a href="#">Sub-link B</a>
                <a href="#">Sub-link C</a>
            </div>
        </li>
        <li><a href="#">Link 3</a></li>
    </ul>

    <!-- User info container -->
    <div class="user-info" id="userInfo">
        <img src="<?php echo htmlspecialchars($userImage); ?>" alt="User Image" class="sidebar-user-image">
        <div class="user-details">
            <span class="username"><?php echo htmlspecialchars($username) ?? 'Not found'; ?></span>
        </div>
    </div>
</div>

<script>
// Add event listener to redirect on click
document.getElementById('userInfo').addEventListener('click', function() {
    window.location.href = 'settings.php';
});
</script>

<style>
/* Sidebar specific styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 230px;
    height: 100%;
    border-radius: 0px 30px 0px 0px;
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
    height: 150px;
    overflow: hidden;
}

.sidebar .header img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0px 30px 0px 0px;
    display: block;
}

.sidebar .header .text-overlay {
    position: absolute;
    top: 10px;
    left: 10px;
    color: #fff;
    font-size: 15px;
    font-weight: bold;
    background-color: rgba(0, 0, 0, 0.5);
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
    width: calc(100% - 20px);
    background-color: transparent;
    color: #fff;
    border: none;
    padding: 10px;
    font-size: 17px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    outline: none;
}

.dropdown button:hover {
    background-color: #444;
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

.user-info {
    position: absolute;
    bottom: 10px;
    height: 10%;
    width: 90%;
    border-radius: 10px;
    background-color: #444;
    color: #fff;
    display: flex;
    align-items: center;
    padding: 10px;
    border-top: 1px solid #555;
    box-sizing: border-box;
    cursor: pointer;
}

.user-info .sidebar-user-image {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin-right: 10px;
    margin-bottom: 5px;
}

.user-info .user-details {
    flex-grow: 1;
}

.user-info .username {
}
</style>