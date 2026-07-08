<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database path (global variable)
$dbpath = '/home/killerb/databases/users.db';

// Connect to the SQLite database
function connectDatabase($dbPath) {
    $db = new SQLite3($dbPath);
    if (!$db) {
        die("Connection to database failed: " . $db->lastErrorMsg());
    }
    return $db;
}

// Check if the user has an admin role and a valid session token
function checkAdminAccess() {
    global $dbpath; // Access the global variable for database path

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
        header("Location: login.php");
        exit();
    }

    // Connect to the database
    $db = connectDatabase($dbpath);

    // Verify the session token and role from the database
    $stmt = $db->prepare("SELECT session_token, role FROM users WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // Check if session token matches and the user is an admin
    if (!$user || $user['session_token'] !== $_SESSION['session_token'] || $user['role'] !== 'donkey') {
        // Redirect to login if verification fails
        header("Location: login.php");
        exit();
    }

    // Close the database connection
    $db->close();
}
?>
