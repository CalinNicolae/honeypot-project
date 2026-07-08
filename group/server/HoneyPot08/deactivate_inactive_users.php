<?php
require_once 'auth.php';

// Deactivate users who have been inactive for more than 15 minutes
function deactivateInactiveUsers($db) {
    // Update users who have been inactive for more than 15 minutes
    $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE is_active = 1 AND last_activity < DATETIME('now', '-60 minutes')");
    $stmt->execute();
}

// Connect to the database and run the deactivation function
$db = connectDatabase('/home/killerb/databases/users.db');
deactivateInactiveUsers($db);
$db->close();
?>
