<?php

require_once 'auth.php';

// Ensure only admin users (donkey role) can access this page
checkAdminAccess();

$dbpath = '/home/killerb/databases/users.db';

// Connect to the database
$db = connectDatabase($dbpath);

// Fetch all users with additional fields
function getAllUsers($db) {
    $result = $db->query("SELECT id, username, role, is_active, is_enabled, last_login, created_at, session_token FROM users");
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    return $users;
}

// Toggle user status (enable/disable)
function toggleUserStatus($db, $userId, $status) {
    $stmt = $db->prepare("UPDATE users SET is_enabled = :status WHERE id = :id");
    $stmt->bindValue(':status', $status, SQLITE3_INTEGER);
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $stmt->execute();
}

// Delete user from the database
function deleteUser($db, $userId) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $stmt->execute();
}

// Handle enable/disable requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_user_id'])) {
        $userId = $_POST['toggle_user_id'];
        $newStatus = $_POST['new_status'] == 1 ? 1 : 0;
        toggleUserStatus($db, $userId, $newStatus);
        header("Location: donkeymaster.php#row-" . $userId);
        exit();
    }

    // Handle delete user request
    if (isset($_POST['delete_user_id'])) {
        $userId = $_POST['delete_user_id'];

        // Ensure the admin cannot delete themselves
        if ($userId != $_SESSION['user_id']) {
            deleteUser($db, $userId);
        }
        header("Location: donkeymaster.php");
        exit();
    }
}

// Retrieve users
$users = getAllUsers($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonkeyMaster Admin Panel</title>
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

    <!-- Header -->
    <header id="header">
        <div class="logo">
            <span class="icon fa-gem"></span>
        </div>
        <div class="content">
            <div class="inner">
                <h1>Admin Panel</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div id="main">
        <article class="active">
            <h2>Overview of All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Enabled</th>
                        <th>Last Login</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr id="row-<?php echo $user['id']; ?>">
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['is_active'] ? 'Online' : 'Offline'; ?></td>
                            <td><?php echo $user['is_enabled'] ? 'Enabled' : 'Disabled'; ?></td>
                            <td><?php echo $user['last_login']; ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <?php if ($user['role'] !== 'donkey'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="toggle_user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $user['is_enabled'] ? 0 : 1; ?>">
                                        <button type="submit">
                                            <?php echo $user['is_enabled'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <em>Admin</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </div>

    <!-- Back to Home Button -->
    <a href="index.php" class="back-button">Back to Home</a>

</div>
</body>
</html>
<?php
// Close the database connection
$db->close();
?>