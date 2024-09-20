<?php

// Load user data
$users = [];
if (file_exists(USER_DATA_FILE)) {
    $json_data = file_get_contents(USER_DATA_FILE);
    $users = json_decode($json_data, true);

    if ($users === null) {
        $users = [];
    }
} 

// Count the number of users per rank and collect user details
$rankCounts = [];
$rankUsers = []; // Array to hold users by rank

foreach ($users as $user) {
    $rank = ucfirst(strtolower($user['rank'])); // Normalize rank format
    if (!isset($rankCounts[$rank])) {
        $rankCounts[$rank] = 0;
        $rankUsers[$rank] = [];
    }
    $rankCounts[$rank]++;
    $rankUsers[$rank][] = $user;
}

// Sample array of ranks; replace this with actual data retrieval logic
$ranks = [
    'Owner' => 'Owner',
    'Admin' => 'Admin',
    'Mod' => 'Mod',
    'Basic' => 'Basic',
    'Banned' => 'Banned', // This rank will be excluded from display
];

// Filter out 'Banned' rank
$filteredRanks = array_filter($ranks, function($rank) {
    return $rank !== 'Banned';
});

// Sort ranks in the desired order
$order = ['Owner', 'Admin', 'Mod', 'Basic'];
usort($filteredRanks, function($a, $b) use ($order) {
    $aIndex = array_search($a, $order);
    $bIndex = array_search($b, $order);
    return $aIndex - $bIndex;
});

// Calculate total member count
$totalMembers = array_sum($rankCounts);
?>

<div id="rightbar" class="rightbar">
    <div class="total-members">
        Members: <span><?php echo htmlspecialchars($totalMembers); ?></span>
    </div>
    <?php foreach ($filteredRanks as $rank): ?>
        <div class="rank-item">
            <?php echo htmlspecialchars($rank); ?> 
            <span class="rank-count">
                <?php echo isset($rankCounts[$rank]) ? $rankCounts[$rank] : 0; ?>
            </span>
            <?php if (isset($rankUsers[$rank]) && count($rankUsers[$rank]) > 0): ?>
                <div class="users-list">
                    <?php foreach ($rankUsers[$rank] as $user): ?>
                        <div class="user-item">
                            <a href="profiles.php?username=<?php echo urlencode($user['username']); ?>">
                                <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" class="user-image">
                                <span class="user-username"><?php echo htmlspecialchars($user['username']); ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="users-list">
                    <div class="no-users"></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
function refreshRightbar() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'rightbar.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('rightbar').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

// Refresh the right bar every 5 seconds
setInterval(refreshRightbar, 5000);
</script>

<style>
.rightbar {
    position: fixed;
    top: 0;
    right: 0;
    width: 230px;
    height: 100%;
    background-color: #333;
    border-radius: 20px 0px 0px 0px;
    color: #fff;
    padding: 0;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-x: hidden;
}

.total-members {
    margin: 10px 0;
    margin-right: 60%;
    font-size: 15px;
    color: #fff;
}

.rank-item {
    margin: 10px 0;
    margin-left: 10%;
    font-size: 18px;
    width: 100%;
}

.rank-count {
    font-size: 14px;
    color: #ccc;
    margin-left: 5px;
}

.users-list {
    margin-top: 10px;
}

.user-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.user-image {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
}

.user-username {
    font-size: 14px;
    color: #ccc;
    position: relative;
    top: -10px; /* Move the username up by 10px */
}

.no-users {
    color: #aaa;
    font-style: none;
}
</style>
