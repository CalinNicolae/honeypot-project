<?php
// Include the authorization file
require_once 'auth.php';

// Check if the user is an admin with a valid session
checkAdminAccess();

// If the user passes the check, proceed with the rest of the page
phpinfo();
?>
