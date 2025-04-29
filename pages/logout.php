<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Only unset user-specific session data
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}
if (isset($_SESSION['username'])) {
    unset($_SESSION['username']);
}
if (isset($_SESSION['user_email'])) {
    unset($_SESSION['user_email']);
}
if (isset($_SESSION['user_role'])) {
    unset($_SESSION['user_role']);
}

// Destroy the session cookie only if no admin is logged in
if (!isset($_SESSION['admin']) && isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Start a new session to store the message if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['success'] = "You have been successfully logged out.";

// Redirect to the home page
header("Location: /WeddinPlaning/index.php");
exit();
?> 