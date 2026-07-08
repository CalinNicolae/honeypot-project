<?php

require_once 'auth.php';

$dbpath = '/home/killerb/databases/users.db';

// Authenticate user credentials
function authenticateUser($db, $username, $password) {
    try {
        $stmt = $db->prepare("SELECT id, password, role, is_enabled FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && $user['is_enabled'] && password_verify($password, $user['password'])) {
            return $user;
        }
    } catch (Exception $e) {
        error_log("Error authenticating user: " . $e->getMessage());
    }
    return false;
}

// Update session details in the database
function updateSessionDetails($db, $userId, $sessionToken) {
    try {
        $lastLogin = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE users SET is_active = 1, session_token = :session_token, last_login = :last_login WHERE id = :id");
        $stmt->bindValue(':session_token', $sessionToken, SQLITE3_TEXT);
        $stmt->bindValue(':last_login', $lastLogin, SQLITE3_TEXT);
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error updating session details: " . $e->getMessage());
    }
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectDatabase($dbpath);

    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    $user = authenticateUser($db, $username, $password);

    if (is_array($user)) {
        session_regenerate_id(true);
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        $_SESSION['session_token'] = $sessionToken;

        updateSessionDetails($db, $user['id'], $sessionToken);

        $db->close();
        header("Location: " . ($user['role'] === 'donkey' ? "donkeymaster.php" : "index.php"));
        exit();
    } else {
        $error = "Invalid credentials.";
    }

    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/browser.min.js" defer></script>
    <script src="assets/js/breakpoints.min.js" defer></script>
    <script src="assets/js/util.js" defer></script>
    <script src="assets/js/main.js" defer></script>
</head>
<body class="is-preload">

<!-- Wrapper -->
<div id="wrapper">

    <!-- Main Content -->
    <div id="main">
        <article id="login">
            <h2 class="major">Login</h2>
            <?php if (!empty($error)): ?>
            <p class="error" style="color: red; text-align: center;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="fields">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <ul class="actions">
                    <li><button type="submit" class="button primary">Login</button></li>
                </ul>
            </form>
            <p><a href="register.php">Don't have an account? Register here.</a></p>
        </article>
    </div>

</div>

<!-- Background -->
<div id="bg"></div>
</body>
</html>