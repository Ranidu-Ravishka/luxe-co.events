<?php
session_start();

// Only unset admin session data
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

header("Location: login.php");
exit();
?> 