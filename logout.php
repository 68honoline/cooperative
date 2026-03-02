<?php
/**
 * Logout Page
 * Destroys session and redirects to login
 */

require_once 'config/db.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to login page
header('Location: login.php');
exit;
