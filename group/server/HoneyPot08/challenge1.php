<?php

require_once 'auth.php';
checkLoggedInStatus();

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$avatarDir = "/assets/avatars/" . htmlspecialchars($username);
$avatarPath = '/assets/avatars/default/default.jpg'; // Default avatar

// Check for user-specific avatar
foreach (['png', 'jpeg', 'jpg'] as $ext) {
    $filePath = "/usr/share/nginx/HoneyPot08/assets/avatars/" . $username . "/avatar.$ext";
    if (file_exists($filePath)) {
        $avatarPath = "$avatarDir/avatar.$ext";
        break;
    }
}

$dbpath = '/home/killerb/databases/challenges/honeypot_challenges.db';
$logFile = '/var/log/nginx/challenge1_sqli.log';

// Logging function
function logEvent($username, $input, $status) {
    global $logFile;

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $username,
        'payload' => $input,
        'attempt_status' => $status,
        'challenge' => 'challenge1_sqli'
    ];

    // Convert log entry to JSON
    $logMessage = json_encode($logEntry) . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$db = connectDatabase($dbpath);

// Handle form input and process the challenge query
function processChallenge($db, $input) {
    global $logFile;

    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

    $query = "SELECT * FROM users WHERE username = '$input'";
    try {
        $result = $db->query($query); // Intentionally vulnerable query
        $output = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $output[] = $row;
        }

        if (!empty($output)) {
            logEvent($username, $input, 'success');
        } else {
            logEvent($username, $input, 'failure');
        }

        return $output;
    } catch (Exception $e) {
        error_log("Error retrieving users from database: " . $e->getMessage());
        logEvent($username, $input, 'error');
        return null;
    }
}

// Initialize variables
$error = null;
$hint = null;
$result = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = sanitizeInput($_POST['username']);
    $result = processChallenge($db, $_POST['username']); // Pass raw input for SQL query

    if (!$result) {
        $error = "No results found for username: $usernameInput";
        $hint = "Hint: Try injecting some SQL syntax to bypass the username check.";
    }

    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge 1</title>
    <!-- Template Styles -->
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/browser.min.js" defer></script>
    <script src="assets/js/breakpoints.min.js" defer></script>
    <script src="assets/js/util.js" defer></script>
    <script src="assets/js/main.js" defer></script>
</head>
<div id="wrapper">
    <!-- Username at Top Left -->
    <div class="username">
        <?php echo htmlspecialchars($username); ?>
    </div>
<body class="is-preload">

<!-- Background -->
<div id="bg"></div>

<!-- Wrapper -->
<div id="wrapper">
    <div id="wrapper">
    <!-- Username at Top Left -->
    <div class="username">
        <?php echo htmlspecialchars($username); ?>
    </div>

    <!-- Header -->
    <header id="header">
        <div class="logo">
             <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar">
        </div>
        <div class="content">
            <div class="inner">
                <h1>Challenge 1</h1>
                <p>Your goal is to extract the password of the 'admin' user.</p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div id="main">
        <article class="active">
                <ul class="navigation">
                    <li><a href="index.php">Back to Home</a></li>
                </ul>

            <!-- Challenge Form -->
            <form method="POST">
                <div class="fields">
                    <div class="field half">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="field">
                        <button type="submit" class="button primary">Submit</button>
                    </div>
                </div>
            </form>

            <!-- Feedback Messages -->
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!empty($hint)): ?>
                <p style="color: blue;"><?php echo htmlspecialchars($hint); ?></p>
            <?php endif; ?>

            <?php if (!empty($result)): ?>
                <h2>Results:</h2>
                <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></pre>
            <?php endif; ?>
        </article>
    </div>
</div>
</body>
</html>