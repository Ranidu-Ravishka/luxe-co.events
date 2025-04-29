<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'includes/header.php';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Get current user data from the correct table
    $table = $_SESSION['admin']['table'];
    $stmt = $conn->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin']['id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user) {
        // Validate current password if trying to change password
        if (!empty($new_password) && !password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password match
        if (!empty($new_password) && $new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        
        if (empty($errors)) {
            // Update basic info in the correct table
            $stmt = $conn->prepare("UPDATE {$table} SET username = ?, email = ?, full_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $full_name, $_SESSION['admin']['id']);
            $stmt->execute();
            
            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE {$table} SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['admin']['id']);
                $stmt->execute();
            }
            
            $success = "Profile updated successfully";
            
            // Update session variables
            $_SESSION['admin']['username'] = $username;
            $_SESSION['admin']['full_name'] = $full_name;
        }
    } else {
        $errors[] = "User not found";
    }
}

// Get current user data from the correct table
$table = $_SESSION['admin']['table'];
$stmt = $conn->prepare("SELECT * FROM {$table} WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin']['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If user data not found, redirect to login
if (!$user) {
    $_SESSION['error'] = "Please log in again.";
    header("Location: login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="mb-4">Profile Settings</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Update Profile</h6>
        </div>
        <div class="card-body">
            <form method="POST" class="user">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>
                
                <hr>
                <h6 class="mb-3">Change Password (optional)</h6>
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 