<?php

require_once 'auth.php';

$dbpath = '/home/killerb/databases/users.db';

// Update last activity for logged-in users
function updateLastActivity($db, $userId) {
    try {
        $stmt = $db->prepare("UPDATE users SET last_activity = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error updating last activity: " . $e->getMessage());
    }
}

// Function to validate session token against the database
function isSessionValid($db, $userId, $sessionToken) {
    try {
        $stmt = $db->prepare("SELECT session_token FROM users WHERE id = :id");
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && $user['session_token'] === $sessionToken) {
            return true;
        }
    } catch (Exception $e) {
        error_log("Error validating session: " . $e->getMessage());
    }
    return false;
}

// Check if the session is valid
$isLoggedIn = false;
if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
    $db = connectDatabase($dbpath);
    $isLoggedIn = isSessionValid($db, $_SESSION['user_id'], $_SESSION['session_token']);

    // Update last activity for logged-in non-admin users
    if ($isLoggedIn && $_SESSION['role'] !== 'donkey') {
        updateLastActivity($db, $_SESSION['user_id']);
    }

    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <noscript><link rel="stylesheet" href="/assets/css/noscript.css"></noscript>
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/browser.min.js" defer></script>
    <script src="assets/js/breakpoints.min.js" defer></script>
    <script src="assets/js/util.js" defer></script>
    <script src="assets/js/main.js" defer></script>
</head>
<body class="is-preload">

<!-- Wrapper -->
<div id="wrapper">

    <!-- Header -->
    <header id="header">
        <div class="logo">
            <?php
            // Determine the avatar path
            if (isset($_SESSION['username'])) {
                $avatarDir = "/assets/avatars/" . htmlspecialchars($_SESSION['username']);
                $avatarPath = '';
                foreach (['png', 'jpeg', 'jpg'] as $ext) {
                    if (file_exists("/usr/share/nginx/HoneyPot08/assets/avatars/" . $_SESSION['username'] . "/avatar.$ext")) {
                        $avatarPath = "$avatarDir/avatar.$ext";
                        break;
                    }
                }
            } else {
                $avatarPath = '/assets/avatars/default/default.jpg'; // Default avatar for guests
            }
            ?>
            <!-- Show avatar (user's or default) -->
            <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar">
        </div>
        <div class="content">
            <div class="inner">
                <h1>Welcome to Our Website</h1>
                <p>Explore exciting challenges and customize your profile!</p>
            </div>
        </div>
        <nav>
            <ul>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                    <li><a href="edit_profile.php">Edit Profile</a></li>
                    <li><a href="challenge1.php">Challenge No. 1</a></li>
                    <li><a href="challenge2.php">Challenge No. 2</a></li>
                    <li><a href="challenge3.php">Challenge No. 3</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div id="main">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="success-message">
                    <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
                </div>
            <?php endif; ?>
    </header>
    <!-- Username/Guest at the Top Left -->
    <div class="username">
        <?php if (isset($_SESSION['username'])): ?>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        <?php else: ?>
            Guest
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer id="footer">
        <p class="copyright">
            &copy; HoneyPot. Design: <a href="https://www.youtube.com/watch?v=25fsQofab9c">HoneyPot08</a>.
        </p>
    </footer>

</div>

<!-- Background -->
<div id="bg"></div>
</body>
</html>