<?php

require_once 'auth.php';
checkLoggedInStatus();

$username = $_SESSION['username']; // Authenticated user's username
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

// Define the log file path
$logFile = '/var/log/nginx/challenge2_xss.log';

// Logging function
function logEvent($username, $input, $status) {
    global $logFile;

    $logEntry = [
        'timestamp2' => date('Y-m-d H:i:s'),
        'username2' => $username,
        'payload2' => $input,
        'attempt_status2' => $status,
        'challenge' => 'challenge2_xss'
    ];

    // Convert log entry to JSON
    $logMessage = json_encode($logEntry) . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Initialize variables
$temporaryComment = '';
$challengeSolved = false;
$showHint = false;

// Valid Payloads
$validPayloads = [
    "<script>alert('XSS');</script>",
    "<script>console.log('Challenge Solved');</script>",
    "<script>document.body.style.backgroundColor='orange';</script>"
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment'])) {
        $temporaryComment = trim($_POST['comment']); // Get the submitted comment

        // Check if the comment matches a valid payload
        if (in_array($temporaryComment, $validPayloads, true)) {
            // Mark challenge as solved
            $_SESSION['challenge_solved'] = true;
            $_SESSION['show_hint'] = false; // Disable the hint
            logEvent($username, $temporaryComment, 'success'); // Log the successful attempt
        } else {
            // Log the failed attempt
            $_SESSION['show_hint'] = true; // Show hint for invalid comments
            logEvent($username, $temporaryComment, 'failure');
        }

        // Store the temporary comment in the session
        $_SESSION['temporary_comment'] = $temporaryComment;
    }
    // Refresh the page
    header("Location: challenge2.php");
    exit();
}

// Retrieve and clear the temporary comment
if (isset($_SESSION['temporary_comment'])) {
    $temporaryComment = $_SESSION['temporary_comment'];
    unset($_SESSION['temporary_comment']);
}

// Retrieve and clear the challenge_solved flag
if (isset($_SESSION['challenge_solved']) && $_SESSION['challenge_solved'] === true) {
    $challengeSolved = true; // Set the flag to display the message
    unset($_SESSION['challenge_solved']); // Clear it so it doesn't persist
}

// Retrieve the hint flag
$showHint = isset($_SESSION['show_hint']) && $_SESSION['show_hint'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge 2</title>
    <!-- Template Styles -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/browser.min.js" defer></script>
    <script src="assets/js/breakpoints.min.js" defer></script>
    <script src="assets/js/util.js" defer></script>
    <script src="assets/js/main.js" defer></script>
</head>
<body class="is-preload">

<!-- Background -->
<div id="bg"></div>

<!-- Wrapper -->
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
                <h1>Challenge 2</h1>
                <p>Submit a comment below and see it displayed temporarily!</p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div id="main">
        <article class="active">
        <ul class="navigation">
                    <li><a href="index.php">Back to Home</a></li>
                </ul>
            <!-- Comment Form -->
            <form method="POST" id="challengeForm">
                <div class="fields">
                    <div class="field half">
                        <label for="comment">Your Comment:</label><br>
                        <textarea id="comment" name="comment" rows="4" placeholder="Enter your comment here..."></textarea><br>
                        <button type="submit" class="button primary">Post Comment</button>
                    </div>
                </div>
            </form>

            <!-- Hint Below the Form -->
            <?php if ($showHint && !$challengeSolved): ?>
                <div class="hint" style="margin-top: 10px; font-size: 14px; color: gray;">
                    <p>Hint: Try using some JavaScript magic!</p>
                </div>
            <?php endif; ?>

            <!-- Display Temporary Comment -->
            <?php if (!empty($temporaryComment)): ?>
                <div class="comments">
                    <h2>Submitted Comment:</h2>
                    <p><?php echo htmlspecialchars($temporaryComment); ?></p> <!-- Intentionally unsanitized -->
                </div>
            <?php endif; ?>

            <!-- Congratulations Message -->
            <?php if ($challengeSolved): ?>
                <div class="congratulations">
                    <div>
                        <h2>Congratulations! You solved the challenge!</h2>
                        <p>Your payload executed successfully.</p>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    </div>
</div>
</body>
</html>