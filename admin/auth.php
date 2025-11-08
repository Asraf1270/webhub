<?php
// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define current user ID (guest if not logged in)
if (!isset($_SESSION['user_id'])) {
    define('CURRENT_USER_ID', 'guest');
} else {
    define('CURRENT_USER_ID', $_SESSION['user_id']);
}
?>