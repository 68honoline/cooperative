<?php
/**
 * Session Protection and Authentication Check
 * This file should be included at the top of all protected pages
 */

// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session validation function
function checkSession() {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // Not logged in, redirect to login page
        header('Location: login.php');
        exit;
    }

    // Session timeout (30 minutes)
    $session_timeout = 1800; // 30 minutes in seconds
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        if ($elapsed > $session_timeout) {
            // Session expired
            logout();
            header('Location: login.php?timeout=1');
            exit;
        }
        // Update login time to extend session
        $_SESSION['login_time'] = time();
    }

    // Session fixation protection - regenerate ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else {
        // Regenerate session ID every hour
        if (time() - $_SESSION['created'] > 3600) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    return true;
}

// Logout function
function logout() {
    // Clear session variables
    $_SESSION = array();

    // Destroy session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Check session on include
checkSession();
