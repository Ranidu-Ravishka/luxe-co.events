<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session for the success message
session_start();
$_SESSION['success_message'] = 'You have been successfully logged out.';

// Redirect to home page with the correct absolute path
header("Location: /WeddinPlaning/pages/index.php");
exit();
?> 