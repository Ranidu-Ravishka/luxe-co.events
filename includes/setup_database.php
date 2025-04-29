<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS wedding_planning";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db('wedding_planning');
    
    // Read and execute the SQL file
    $sql_file = file_get_contents(__DIR__ . '/../database/wedding_planning.sql');
    
    if ($sql_file === false) {
        throw new Exception("Error reading SQL file");
    }
    
    // Split SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql_file)));
    
    // Execute each query
    foreach ($queries as $query) {
        if (!empty($query)) {
            if ($conn->query($query) === FALSE) {
                throw new Exception("Error executing query: " . $conn->error . "\nQuery: " . $query);
            }
        }
    }
    
    echo "Database setup completed successfully!<br>";
    
    // Verify users table
    $result = $conn->query("DESCRIBE users");
    if ($result === FALSE) {
        throw new Exception("Error verifying users table: " . $conn->error);
    }
    
    echo "Users table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 