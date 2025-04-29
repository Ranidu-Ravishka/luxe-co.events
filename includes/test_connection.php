<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!<br>";
}

// Test if we can access the users table
$result = $conn->query("SELECT * FROM users");
if ($result === FALSE) {
    echo "Error accessing users table: " . $conn->error . "<br>";
} else {
    echo "Users table exists and is accessible<br>";
    echo "Number of existing users: " . $result->num_rows . "<br>";
}

// Show table structure
echo "<h3>Users Table Structure:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result === FALSE) {
    echo "Error getting table structure: " . $conn->error;
} else {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
    echo "</pre>";
}

$conn->close();
?> 