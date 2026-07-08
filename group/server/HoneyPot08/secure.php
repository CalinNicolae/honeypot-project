<?php
// Include the authorization file
require_once 'auth.php';

// Check if the user is an admin with a valid session
checkAdminAccess();

// If the user passes the check, they can proceed with secure content
// Place any additional secure content or functionality here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Page</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional: Link to a CSS file for styling -->
</head>
<body>
    <header>
    <ul>
        <li>
            <?php if (isset($_SESSION['username'])): ?>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php
                // Construct the avatar directory relative to the web root
                $avatarDir = "/assets/avatars/" . htmlspecialchars($_SESSION['username']);
                $avatarPath = '';
                // Check for existing avatar file
                foreach (['png', 'jpeg', 'jpg'] as $ext) {
                    if (file_exists("/usr/share/nginx/HoneyPot08/assets/avatars/" . $_SESSION['username'] . "/avatar.$ext")) {
                        $avatarPath = "$avatarDir/avatar.$ext"; // Set the URL for the avatar
                        break;
                    }
                }
                ?>
                <img src="<?php echo $avatarPath ? htmlspecialchars($avatarPath) : '/usr/share/nginx/HoneyPot08/assets/avatars/default/default.jpg'; ?>" alt="User Avatar" style="width: 40px; height: 40px;">
            <?php else: ?>
                <span>Guest</span>
            <?php endif; ?>
        </li>
    </ul>
        <h1>Welcome to the Secure Page</h1>
    </header>
    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
