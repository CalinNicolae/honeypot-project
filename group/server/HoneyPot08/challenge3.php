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


$logFile = '/var/log/nginx/challenge3_idor.log';

// Function to log interactions
function logEvent($username, $profile) {
    global $logFile;

    $logEntry = [
        'timestamp3' => date('Y-m-d H:i:s'),
        'username3' => $username,
        'profile_accessed3' => $profile,
        'challenge' => 'challenge3_idor'
    ];

    $logMessage = json_encode($logEntry) . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$employees = [
            'ZW1wbG95ZWUx' => ['name' => 'Employee Alpha', 'info' => 'Details about Employee Alpha'],
            'ZW1wbG95ZWUy' => ['name' => 'Employee Beta', 'info' => 'Details about Employee Beta'],
            'ZW1wbG95ZWUz' => ['name' => 'Employee Gamma', 'info' => 'Details about Employee Gamma'],
            'ZW1wbG95ZWU0' => ['name' => 'Employee Delta', 'info' => 'Details about Employee Delta'],
            'ZW1wbG95ZWU1' => ['name' => 'Employee Epsilon', 'info' => 'Details about Employee Epsilon'],
            'ZW1wbG95ZWU2' => ['name' => 'Employee Zeta', 'info' => 'Details about Employee Zeta'],
            'ZW1wbG95ZWU3' => ['name' => 'Employee Eta', 'info' => 'Details about Employee Eta'],
            'ZW1wbG95ZWU4' => ['name' => 'Employee Theta', 'info' => 'Details about Employee Theta'],
            'ZW1wbG95ZWU5' => ['name' => 'Employee Iota', 'info' => 'Details about Employee Iota'],
            'ZW1wbG95ZWUxMA==' => ['name' => 'Employee Kappa', 'info' => 'Details about Employee Kappa'],
            'YWRtaW4wOA==' => ['name' => 'Employer John', 'info' => 'Challenge Solved! Congratulations!']
        ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Employee Portal</title>
    <!-- Template Styles -->
    <link rel="stylesheet" href="assets/css/main.css">
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
    <div id="wrapper">
    <!-- Username at Top Left -->
    <div class="username">
        <?php echo htmlspecialchars($username); ?>
    </div>

    <!-- Header -->
    <header id="header">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar 3">
        </div>
        <div class="content">
            <div class="inner">
                <h1>Welcome to the Secure Employee Portal</h1>
                <p>Explore various employee profiles to learn more about the organization.</p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div id="main">
        <article class="active">
            <div class="container">
                <ul class="navigation">
                    <li><a href="index.php">Back to Home</a></li>
                </ul>

                <?php
                if (isset($_GET['profile'])) {
                    $profile = $_GET['profile'];
                    logEvent($username, $profile);

                    if (array_key_exists($profile, $employees)) {
                        echo "<div class='profile'>";
                        echo "<h3>Profile Information</h3>";
                        echo "<p>Name: " . htmlspecialchars($employees[$profile]['name']) . "</p>";
                        echo "<p>Info: " . htmlspecialchars($employees[$profile]['info']) . "</p>";
                        echo '</div>';
                    } else {
                        echo "<p class='error'>Profile not found!</p>";
                    }
                } else {
                    echo "<p>Select a profile to view details.</p>";
                    echo "<p class='hint'>Hint: Can you find the admin08 profile?</p>";
                }
                ?>
            </div>
        </article>
    </div>
</div>
</body>
</html>