<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

require_once 'config.php';

// Create uploads directory if it doesn't exist
$upload_dir = "../uploads/profile_images";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Verify database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connection successful<br>";

// Debug output
echo "<pre>";
echo "POST Data:\n";
print_r($_POST);
echo "\nFiles Data:\n";
print_r($_FILES);
echo "\nSession Data:\n";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    echo "Error: Not logged in";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $errors = [];

    // Handle file upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size must be less than 5MB";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $new_filename = uniqid('profile_') . '.' . $file_type;
            $upload_path = $upload_dir . '/' . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                echo "File uploaded successfully<br>";
                $profile_image = $new_filename;
                
                // Delete old profile image if exists
                $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($old_image = $result->fetch_assoc()) {
                    if ($old_image['profile_image'] && file_exists($upload_dir . '/' . $old_image['profile_image'])) {
                        unlink($upload_dir . '/' . $old_image['profile_image']);
                    }
                }
                $stmt->close();
            } else {
                $errors[] = "Failed to upload file";
            }
        }
    }

    // Debug output
    echo "Processing update for user ID: " . $user_id . "<br>";
    echo "Full Name: " . $full_name . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Phone: " . $phone . "<br>";
    if ($profile_image) {
        echo "Profile Image: " . $profile_image . "<br>";
    }

    // First, verify current user data
    $current_data_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$current_data_stmt) {
        die("Error preparing current data statement: " . $conn->error);
    }
    $current_data_stmt->bind_param("i", $user_id);
    $current_data_stmt->execute();
    $current_data = $current_data_stmt->get_result()->fetch_assoc();
    echo "Current user data in database:<br>";
    print_r($current_data);
    $current_data_stmt->close();

    // Validate inputs
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email already exists for another user
    if (empty($errors) && !empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        if (!$stmt) {
            echo "Error preparing email check statement: " . $conn->error . "<br>";
            $errors[] = "Database error occurred: " . $conn->error;
        } else {
            $stmt->bind_param("si", $email, $user_id);
            if (!$stmt->execute()) {
                echo "Error executing email check: " . $stmt->error . "<br>";
                $errors[] = "Error checking email: " . $stmt->error;
            } else {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors[] = "Email already exists";
                }
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        try {
            // Prepare the update query based on whether we have a new profile image
            if ($profile_image) {
                $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?";
                echo "SQL Query (with image): " . $update_sql . "<br>";
                
                $stmt = $conn->prepare($update_sql);
                if (!$stmt) {
                    throw new Exception("Error preparing update statement: " . $conn->error);
                }
                
                $stmt->bind_param("ssssi", $full_name, $email, $phone, $profile_image, $user_id);
            } else {
                $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                echo "SQL Query (without image): " . $update_sql . "<br>";
                
                $stmt = $conn->prepare($update_sql);
                if (!$stmt) {
                    throw new Exception("Error preparing update statement: " . $conn->error);
                }
                
                $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
            }
            
            // Debug the actual values being bound
            echo "Bound parameters:<br>";
            echo "full_name (string): " . var_export($full_name, true) . "<br>";
            echo "email (string): " . var_export($email, true) . "<br>";
            echo "phone (string): " . var_export($phone, true) . "<br>";
            if ($profile_image) {
                echo "profile_image (string): " . var_export($profile_image, true) . "<br>";
            }
            echo "user_id (integer): " . var_export($user_id, true) . "<br>";
            
            if (!$stmt->execute()) {
                throw new Exception("Error executing update: " . $stmt->error);
            }
            
            echo "Update executed successfully. Affected rows: " . $stmt->affected_rows . "<br>";
            
            // Verify the update immediately after
            $verify_sql = "SELECT * FROM users WHERE id = ?";
            echo "Verification SQL: " . $verify_sql . "<br>";
            
            $verify_stmt = $conn->prepare($verify_sql);
            if (!$verify_stmt) {
                throw new Exception("Error preparing verification statement: " . $conn->error);
            }
            
            $verify_stmt->bind_param("i", $user_id);
            if (!$verify_stmt->execute()) {
                throw new Exception("Error executing verification: " . $verify_stmt->error);
            }
            
            $updated_user = $verify_stmt->get_result()->fetch_assoc();
            
            if ($updated_user) {
                echo "Verification successful. Updated data in database:<br>";
                print_r($updated_user);
                
                // Compare old and new data
                echo "Changes made:<br>";
                foreach(['full_name', 'email', 'phone', 'profile_image'] as $field) {
                    if ($current_data[$field] !== $updated_user[$field]) {
                        echo "$field: {$current_data[$field]} -> {$updated_user[$field]}<br>";
                    } else {
                        echo "$field: No change (value: {$current_data[$field]})<br>";
                    }
                }
                
                // Update session variables
                $_SESSION['full_name'] = $updated_user['full_name'];
                $_SESSION['email'] = $updated_user['email'];
                
                echo "Profile updated successfully!";
            } else {
                throw new Exception("Failed to verify update");
            }
            
            $verify_stmt->close();
            $stmt->close();
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            http_response_code(500);
        }
    } else {
        echo "Validation errors:<br>";
        print_r($errors);
        http_response_code(400);
    }
    
    // Debug: Show final session state
    echo "<br>Final Session State:<br>";
    print_r($_SESSION);
    exit();
}

echo "No POST data received";
exit();
?> 