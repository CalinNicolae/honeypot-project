<?php

require_once 'auth.php';
checkLoggedInStatus();

$dbpath = '/home/killerb/databases/users.db';

// Connect to the database
$db = connectDatabase($dbpath);

// Helper functions
function isUsernameTaken($db, $username) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :username AND id != :id");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC)['count'] > 0;
}

function updateUsername($db, $userId, $username) {
    $stmt = $db->prepare("UPDATE users SET username = :username WHERE id = :id");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    return $stmt->execute();
}

function updatePassword($db, $userId, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    return $stmt->execute();
}

function uploadAvatar($username, $avatar) {
    $allowedExtensions = ['png', 'jpeg', 'jpg'];
    $extension = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions) || !getimagesize($avatar['tmp_name'])) {
        return "Invalid file type. Only PNG, JPEG, and JPG images are allowed.";
    }

    $avatarDir = "/usr/share/nginx/HoneyPot08/assets/avatars/" . $username;
    if (!is_dir($avatarDir)) {
        mkdir($avatarDir, 0777, true);
    }
    // Delete existing avatar files
    foreach ($allowedExtensions as $ext) {
        $existingFile = "$avatarDir/avatar.$ext";
        if (file_exists($existingFile)) {
            unlink($existingFile); // Delete the file
        }
    }

    // Save the new avatar
    $avatarPath = $avatarDir . "/avatar." . $extension;
    if (move_uploaded_file($avatar['tmp_name'], $avatarPath)) {
        return "Avatar uploaded successfully!";
    } else {
        return "Error: Failed to upload avatar.";
    }
}

// Process forms based on the form type
$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type'])) {
        switch ($_POST['form_type']) {
            case 'username':
                $newUsername = $_POST['username'];
                if (isUsernameTaken($db, $newUsername)) {
                    $errors[] = "Username is already taken.";
                } else if (updateUsername($db, $_SESSION['user_id'], $newUsername)) {
                    $_SESSION['username'] = $newUsername;
                    $_SESSION['message'] = "Username updated successfully!";
                    header("Location: edit_profile.php");
                    exit();
                } else {
                    $errors[] = "Error updating username.";
                }
                break;

            case 'password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];

                // Verify current password
                $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
                $result = $stmt->execute();
                $user = $result->fetchArray(SQLITE3_ASSOC);

                if (!password_verify($currentPassword, $user['password'])) {
                    $errors[] = "Current password is incorrect.";
                } else if ($newPassword !== $confirmPassword) {
                    $errors[] = "New passwords do not match.";
                } else if (strlen($newPassword) < 8) {
                    $errors[] = "New password must be at least 8 characters long.";
                } else if (updatePassword($db, $_SESSION['user_id'], $newPassword)) {
                    $_SESSION['message'] = "Password updated successfully!";
                    header("Location: edit_profile.php");
                    exit();
                } else {
                    $errors[] = "Error updating password.";
                }
                break;

            case 'avatar':
                $avatarError = uploadAvatar($_SESSION['username'], $_FILES['avatar']);
                if ($avatarError) {
                    $errors[] = $avatarError;
                } else {
                    $_SESSION['message'] = "Avatar uploaded successfully!";
                    header("Location: edit_profile.php");
                    exit();
                }
                break;
        }
    }
}

// Retrieve current user info
$currentUsername = $_SESSION['username'];
$avatarDir = "/usr/share/nginx/HoneyPot08/assets/avatars/" . $currentUsername;
$avatarPath = "";
foreach (['png', 'jpeg', 'jpg'] as $ext) {
    if (file_exists("$avatarDir/avatar.$ext")) {
        $avatarPath = "$avatarDir/avatar.$ext";
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/browser.min.js" defer></script>
    <script src="assets/js/breakpoints.min.js" defer></script>
    <script src="assets/js/util.js" defer></script>
    <script src="assets/js/main.js" defer></script>
</head>
<body class="is-preload">

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
                <img src="<?php echo $avatarPath ? htmlspecialchars($avatarPath) : '/usr/share/nginx/HoneyPot08/assets/avatars/default/default.jpg'; ?>" alt="User Avatar" style="width: 30px; height: 30px; border-radius: 50%;">
            <?php else: ?>
                <span>Guest</span>
            <?php endif; ?>
        </li>
    </ul>
</header>

<?php if (isset($_SESSION['message'])): ?>
    <div class="success-message">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
        <?php unset($_SESSION['message']); // Clear the message after displaying ?>
    </div>
<?php endif; ?>
    <h1>Edit Profile</h1>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="form_type" value="username">
        <label for="username">New Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($currentUsername); ?>" required>
        <button type="submit">Update Username</button>
    </form>

    <form method="post">
        <input type="hidden" name="form_type" value="password">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_type" value="avatar">
        <label for="avatar">Upload Avatar (PNG, JPEG, JPG):</label>
        <input type="file" name="avatar" id="avatar" accept="image/png, image/jpeg, image/jpg" onchange="previewAvatar(event)">
        <img id="avatar-preview" src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Avatar Preview">
        <button type="submit">Upload Avatar</button>
    </form>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>

<?php $db->close(); ?>