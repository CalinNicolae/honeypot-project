<?php

require_once 'auth.php';


$dbpath = '/home/killerb/databases/users.db';

// Validate registration inputs
function validateRegistrationInputs($username, $password) {
    if (empty($username) || empty($password)) {
        return "Username and password cannot be empty.";
    }
    if (strlen($username) > 12) {
        return "Username cannot exceed 12 characters.";
    }
    if (!preg_match('/^[a-z0-9]+$/', $username)) {
        return "Username can only contain lowercase letters (a-z) and numbers (0-9).";
    }
    if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        return "Password must be at least 8 characters long. No special characters allowed.";
    }
    if (strtolower($username) === 'default') {
        return "Username is not allowed. Please choose another one.";
    }
    return null;
}

// Check if the username already exists in the database
function isUsernameTaken($db, $username) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result) {
        die("Error executing query: " . $db->lastErrorMsg());
    }
    $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
    return $count > 0;
}

// Register a new user
function registerUser($db, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

    if ($stmt->execute()) {
        $avatarDir = "/usr/share/nginx/HoneyPot08/assets/avatars/" . $username;
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0777, true);
        }
        $defaultAvatarPath = "/usr/share/nginx/HoneyPot08/assets/avatars/default/default.jpg";
        $userAvatarPath = $avatarDir . "/avatar.jpg";
        if (!copy($defaultAvatarPath, $userAvatarPath)) {
            die("Failed to copy default avatar to user directory.");
        }
        return true;
    }
    return false;
}

// Handle registration form submission
function handleRegistration($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Validate inputs
        $validationError = validateRegistrationInputs($username, $password);
        if ($validationError) {
            echo "<p style='color: red;'>" . $validationError . "</p>";
            return;
        }

        // Check if username is taken
        if (isUsernameTaken($db, $username)) {
            echo "<p style='color: red;'>Username is already taken. Please choose another one.</p>";
            return;
        }

        // Register user
        if (registerUser($db, $username, $password)) {
            $_SESSION['message'] = "User registered successfully!";
            header("Location: index.php");
            exit();
        } else {
            echo "<p style='color: red;'>Error registering user: " . $db->lastErrorMsg() . "</p>";
        }
    }
}

// Execute the registration process
$db = connectDatabase($dbpath);
handleRegistration($db);
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <style>
        /* Ensure the form is always visible */
        #main {
            display: flex !important;
            justify-content: center;
            align-items: center;
        }
        #register {
            display: block !important;
        }
    </style>
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

        <!-- Registration Form -->
        <article id="register">
            <h2 class="major">Register</h2>
            <?php if (!empty($error)): ?>
            <p class="error" style="color: red; text-align: center;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="fields">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username (a-z, 0-9, max 12 chars)" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password (min 8 chars)" required>
                    </div>
                </div>
                <ul class="actions">
                    <li><button type="submit" class="button primary">Register</button></li>
                </ul>
            </form>
            <p><a href="index.php">Back to Home</a></p>
        </article>

    </div>

</div>

<!-- Background -->
<div id="bg"></div>
</body>
</html>