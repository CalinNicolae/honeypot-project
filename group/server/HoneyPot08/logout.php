<?php

require_once 'auth.php';

// Update user's active status and clear session token in the database
function logoutUser($db, $userId) {
    try {
        $stmt = $db->prepare("UPDATE users SET is_active = 0, session_token = NULL WHERE id = :id");
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error logging out user: " . $e->getMessage());
    }
}

// Clear session and destroy it
function clearSession() {
    session_unset();
    session_destroy();
}

// Main logout handler
function handleLogout() {
    global $dbpath; // Access the global database path

    if (isset($_SESSION['user_id'])) {
        $db = connectDatabase($dbpath);

        // Update user's active status and clear the session token
        logoutUser($db, $_SESSION['user_id']);

        // Close the database connection
        $db->close();
    }

    // Clear session and redirect to the login page
    clearSession();
    header("Location: index.php");
    exit();
}

// Execute the logout process
handleLogout();
?>
