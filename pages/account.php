<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    $_SESSION['error'] = "Please log in to access your account.";
    header("Location: ../includes/login & registration.php");
    exit();
}

// Get user details with error handling
$user_id = $_SESSION['user_id'];

// Get wedding planner information
$planner_query = "SELECT wp.*, w.* FROM wedding_plans w 
                  LEFT JOIN wedding_planners wp ON w.planner_id = wp.id 
                  WHERE w.user_id = ? AND w.status != 'completed'
                  ORDER BY w.created_at DESC LIMIT 1";
$planner_stmt = $conn->prepare($planner_query);
$planner_stmt->bind_param("i", $user_id);
$planner_stmt->execute();
$planner_result = $planner_stmt->get_result();
$planner_info = $planner_result->fetch_assoc();

$stmt = $conn->prepare("SELECT id, full_name, email, phone, created_at, role, profile_image FROM users WHERE id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Error getting result: " . $stmt->error);
}

$user = $result->fetch_assoc();
if (!$user) {
    $_SESSION['error'] = "User not found. Please try logging in again.";
    header("Location: ../includes/login & registration.php");
    exit();
}

// Debug query to check service images
$debug_query = "SELECT id, name, image FROM services WHERE id IN (
    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(b.service_ids, ',', n.n), ',', -1) as service_id
    FROM bookings b
    CROSS JOIN (
        SELECT a.N + b.N * 10 + 1 n
        FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
        CROSS JOIN (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
    ) n
    WHERE n.n <= 1 + (LENGTH(b.service_ids) - LENGTH(REPLACE(b.service_ids, ',', '')))
    AND b.user_id = ?
)";

$debug_stmt = $conn->prepare($debug_query);
$debug_stmt->bind_param("i", $_SESSION['user_id']);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();

while ($service = $debug_result->fetch_assoc()) {
    error_log("Service #{$service['id']} - Name: {$service['name']} - Image: {$service['image']}");
}

// Get user's bookings with error handling
$bookings_query = "SELECT b.*, 
                         GROUP_CONCAT(DISTINCT s.name) as service_names, 
                         GROUP_CONCAT(DISTINCT s.price) as service_prices,
                         GROUP_CONCAT(DISTINCT CONCAT('../', s.image)) as service_images,
                         GROUP_CONCAT(DISTINCT sc.name) as category_names,
                         GROUP_CONCAT(DISTINCT s.status) as service_statuses
    FROM bookings b 
                  LEFT JOIN services s ON FIND_IN_SET(s.id, b.service_ids)
                  LEFT JOIN service_categories sc ON s.category_id = sc.id
    WHERE b.user_id = ? 
                  GROUP BY b.id
                  ORDER BY b.created_at DESC";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings_result = $stmt->get_result();

// Store all bookings in an array for reuse
$all_bookings = [];
$total_pending = 0;
$has_pending = false;

while ($booking = $bookings_result->fetch_assoc()) {
    // Debug output for each booking's service images
    error_log("Booking #{$booking['id']} - Service Images: " . print_r($booking['service_images'], true));
    
    $all_bookings[] = $booking;
    if ($booking['status'] === 'pending') {
        $has_pending = true;
        $total_pending += $booking['total_amount'];
    }
}

// Get user's payments
$payments_query = "SELECT p.*, b.booking_date, b.event_date, b.total_amount,
                  s.name as service_name
                  FROM payments p
                  JOIN bookings b ON p.booking_id = b.id
                  JOIN services s ON b.service_id = s.id
                  WHERE b.user_id = ?
                  ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($payments_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$payments_result = $stmt->get_result();

// Store all payments in an array
$all_payments = [];
while ($payment = $payments_result->fetch_assoc()) {
    $all_payments[] = $payment;
}

include '../includes/header.php';
?>

<style>
/* Main layout styles */
.account-section {
    padding-top: calc(76px + 2rem); /* Navbar height + additional padding */
    padding-bottom: 2rem;
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Card styling */
.account-card {
    background: #fff;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    height: 100%;
    margin-bottom: 1rem;
}

/* Sidebar specific styles */
.col-lg-3 .account-card {
    position: sticky;
    top: calc(76px + 1rem); /* Navbar height + some spacing */
}

/* List group styling */
.list-group {
    border-radius: 8px;
    overflow: hidden;
}

.list-group-item {
    border: none;
    padding: 0.8rem 1rem;
    margin-bottom: 2px;
    background-color: transparent;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: rgba(255, 64, 129, 0.1);
}

.list-group-item.active {
    background-color: #ff4081;
    color: white;
}

/* Profile image styles */
.profile-image-container {
    position: relative;
    display: inline-block;
    margin-bottom: 2rem;
}

.profile-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff4081;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.image-upload-controls {
    margin-top: 1rem;
}

/* Booking card styles */
.booking-card {
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(20px);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.booking-card.show {
    opacity: 1;
    transform: translateY(0);
}

.booking-image-container {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.booking-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
    opacity: 0;
}

.booking-image.loaded {
    opacity: 1;
}

.card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.card-title {
    font-size: 1.1rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.booking-details {
    font-size: 0.9rem;
    margin-top: auto;
}

.card-footer {
    background-color: transparent;
    border-top: 1px solid rgba(0,0,0,0.125);
    padding: 1rem;
}

/* Update the bookings section layout */
.col-lg-4.col-md-6.mb-4 {
    display: flex;
}

.booking-card .card-body {
    padding: 1.25rem;
}

.booking-card .badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

/* Fix for image container */
.booking-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Button styles */
.btn-primary {
    background-color: #ff4081;
    border-color: #ff4081;
}

.btn-primary:hover {
    background-color: #f50057;
    border-color: #f50057;
}

/* Tab content spacing */
.tab-pane {
    padding: 1rem 0;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .account-card {
        margin-bottom: 1.5rem;
    }
    
    .col-lg-3 .account-card {
        position: static;
    }
}

/* Update loading spinner styles */
.loading-spinner {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    transition: all 0.3s ease;
    opacity: 0;
    pointer-events: none;
}

.loading-spinner.show {
    display: flex;
    opacity: 1;
    pointer-events: auto;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #ff4081;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Add tab transition styles */
.tab-pane {
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(10px);
    display: none;
}

.tab-pane.show {
    opacity: 1;
    transform: translateY(0);
    display: block;
}

/* Add card transition styles */
.booking-card {
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(20px);
}

.booking-card.show {
    opacity: 1;
    transform: translateY(0);
}

/* Improve image loading */
.booking-image {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.booking-image.loaded {
    opacity: 1;
}
</style>

    <!-- Account Section -->
    <section class="account-section">
        <div class="container">
            <div class="row">
                <!-- Account Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="account-card" data-aos="fade-right">
                        <div class="account-header text-center mb-4">
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="list-group">
                            <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                            <a href="#bookings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-calendar-check me-2"></i> My Bookings
                            </a>
                            <a href="#payments" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-credit-card me-2"></i> Payments
                            </a>
                            <a href="#settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Content -->
                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile">
                            <div class="account-card" data-aos="fade-up">
                                <h3 class="mb-4">Profile Information</h3>
                                <form action="../includes/process_profile_update.php" method="POST" id="profileForm" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-12 mb-4 text-center">
                                            <div class="profile-image-container">
                                                <?php
                                                $profile_image = $user['profile_image'] ?? null;
                                                if ($profile_image && file_exists("../uploads/profile_images/" . $profile_image)) {
                                                    $image_url = "../uploads/profile_images/" . $profile_image;
                                                } else {
                                                    $image_url = "https://ui-avatars.com/api/?name=" . urlencode($user['full_name']) . "&background=ff4081&color=fff&size=128";
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                                     alt="Profile Image" 
                                                     class="profile-image mb-3" 
                                                     id="profileImagePreview">
                                                <div class="image-upload-controls">
                                                    <label for="profile_image" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-camera me-2"></i>Change Photo
                                                    </label>
                                                    <input type="file" 
                                                           name="profile_image" 
                                                           id="profile_image" 
                                                           class="d-none" 
                                                           accept="image/jpeg,image/png,image/gif">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Member Since</label>
                                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" readonly>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="updateProfileBtn">Update Profile</button>
                                </form>

                                <?php if ($planner_info && $planner_info['planner_id']): ?>
                                <!-- Wedding Planner Information -->
                                <div class="mt-5">
                                    <h4 class="mb-4">Your Wedding Planner</h4>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($planner_info['full_name']); ?></p>
                                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($planner_info['email']); ?></p>
                                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($planner_info['phone']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if (!empty($planner_info['bio'])): ?>
                                                        <p><strong>Bio:</strong> <?php echo htmlspecialchars($planner_info['bio']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php elseif ($planner_info): ?>
                                <div class="mt-5">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Your wedding plan is being reviewed. A planner will be assigned to you soon.
                                    </div>
                                </div>
                                <?php endif; ?>

                                <script>
                                // Image preview functionality
                                document.getElementById('profile_image').addEventListener('change', function(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        if (file.size > 5 * 1024 * 1024) { // 5MB limit
                                            alert('File size must be less than 5MB');
                                            this.value = '';
                                            return;
                                        }
                                        
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            document.getElementById('profileImagePreview').src = e.target.result;
                                        }
                                        reader.readAsDataURL(file);
                                    }
                                });

                                document.getElementById('profileForm').addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    const btn = document.getElementById('updateProfileBtn');
                                    btn.disabled = true;
                                    btn.innerHTML = 'Updating...';
                                    
                                    const formData = new FormData(this);
                                    
                                    fetch('../includes/process_profile_update.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.text())
                                    .then(html => {
                                        const debugDiv = document.createElement('div');
                                        debugDiv.innerHTML = html;
                                        document.body.appendChild(debugDiv);
                                        
                                        if (html.includes('Profile updated successfully')) {
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 3000);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred while updating your profile. Please try again.');
                                    })
                                    .finally(() => {
                                        btn.disabled = false;
                                        btn.innerHTML = 'Update Profile';
                                    });
                                });
                                </script>

                                <style>
                                .profile-image-container {
                                    position: relative;
                                    display: inline-block;
                                }
                                .profile-image {
                                    width: 150px;
                                    height: 150px;
                                    border-radius: 50%;
                                    object-fit: cover;
                                    border: 3px solid #ff4081;
                                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                }
                                .image-upload-controls {
                                    margin-top: 10px;
                                }
                                </style>
                            </div>
                        </div>

                        <!-- Bookings Tab -->
                        <div class="tab-pane fade" id="bookings">
                            <div class="account-card" data-aos="fade-up">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4>My Bookings</h4>
                                    <?php if ($has_pending): ?>
                                        <a href="bulk_payment.php" class="btn btn-primary">
                                            Pay All Pending ($<?php echo number_format($total_pending, 2); ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-4">
                                    <?php foreach ($all_bookings as $booking): 
                                        // Split the concatenated values
                                        $service_names = $booking['service_names'] ? explode(',', $booking['service_names']) : [];
                                        $service_images = $booking['service_images'] ? explode(',', $booking['service_images']) : [];
                                        $category_names = $booking['category_names'] ? explode(',', $booking['category_names']) : [];
                                        
                                        // Get the first service's details (or use defaults)
                                        $primary_service_name = $service_names[0] ?? 'Service Unavailable';
                                        $primary_image = !empty($service_images[0]) ? $service_images[0] : "../assets/images/default-service.jpg";
                                        $primary_category = $category_names[0] ?? 'Uncategorized';
                                    ?>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card booking-card shadow-sm">
                                                <!-- Service Image -->
                                                <div class="booking-image-container">
                                                    <img src="<?php echo htmlspecialchars($primary_image); ?>" 
                                                         class="booking-image" 
                                                         alt="<?php echo htmlspecialchars($primary_service_name); ?>"
                                                         onerror="this.src='../assets/images/default-service.jpg';">
                                                </div>
                                                
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($primary_service_name); ?></h5>
                                                        <span class="badge <?php 
                                                            echo $booking['status'] === 'pending' ? 'bg-warning' : 
                                                                ($booking['status'] === 'confirmed' ? 'bg-success' : 'bg-secondary'); 
                                                        ?>"><?php echo ucfirst($booking['status']); ?></span>
                                                    </div>
                                                    
                                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($primary_category); ?></p>
                                                    
                                                    <div class="booking-details mt-auto">
                                                        <p class="mb-2"><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                                                        <p class="mb-2"><strong>Event Date:</strong> <?php echo date('M d, Y', strtotime($booking['event_date'])); ?></p>
                                                        <p class="mb-2"><strong>Guests:</strong> <?php echo $booking['guest_count']; ?></p>
                                                        <p class="mb-0"><strong>Amount:</strong> $<?php echo number_format($booking['total_amount'], 2); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="card-footer">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments">
                            <div class="account-card" data-aos="fade-up">
                                <h3 class="mb-4">Payment History</h3>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Booking ID</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($all_payments)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No payment history found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($all_payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                                    <td>#<?php echo $payment['booking_id']; ?></td>
                                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($payment['status']); ?>
                                                    </span>
                                                </td>
                                                    <td>
                                                        <a href="generate_payment_receipt.php?payment_id=<?php echo $payment['id']; ?>" 
                                                           class="btn btn-sm btn-primary" target="_blank">
                                                            <i class="fas fa-download"></i> Download Receipt
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane fade" id="settings">
                            <div class="account-card" data-aos="fade-up">
                                <h3 class="mb-4">Account Settings</h3>
                                <div id="passwordChangeAlert" class="alert" style="display: none;"></div>
                                <form id="passwordChangeForm">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" id="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="updatePasswordBtn">Update Password</button>
                                </form>

                                <script>
                                document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    const btn = document.getElementById('updatePasswordBtn');
                                    const alert = document.getElementById('passwordChangeAlert');
                                    
                                    // Get form values
                                    const currentPassword = document.getElementById('current_password').value;
                                    const newPassword = document.getElementById('new_password').value;
                                    const confirmPassword = document.getElementById('confirm_password').value;
                                    
                                    // Basic validation
                                    if (newPassword !== confirmPassword) {
                                        alert.className = 'alert alert-danger';
                                        alert.textContent = 'New passwords do not match!';
                                        alert.style.display = 'block';
                                        return;
                                    }
                                    
                                    // Disable button and show loading state
                                    btn.disabled = true;
                                    btn.innerHTML = 'Updating...';
                                    
                                    // Send request
                                    fetch('../includes/process_password_change.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: new URLSearchParams({
                                            current_password: currentPassword,
                                            new_password: newPassword,
                                            confirm_password: confirmPassword
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        alert.className = `alert alert-${data.success ? 'success' : 'danger'}`;
                                        alert.textContent = data.message;
                                        alert.style.display = 'block';
                                        
                                        if (data.success) {
                                            // Clear form on success
                                            this.reset();
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert.className = 'alert alert-danger';
                                        alert.textContent = 'An error occurred. Please try again.';
                                        alert.style.display = 'block';
                                    })
                                    .finally(() => {
                                        btn.disabled = false;
                                        btn.innerHTML = 'Update Password';
                                    });
                                });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Update loading spinner HTML -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner"></div>
</div>

<script>
// Improved loading state management
let loadingTimeout;
function showLoading() {
    clearTimeout(loadingTimeout);
    const spinner = document.getElementById('loadingSpinner');
    spinner.classList.add('show');
}

function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    spinner.classList.remove('show');
}

// Improved tab switching behavior
document.addEventListener('DOMContentLoaded', function() {
    hideLoading();
    
    // Initialize all booking cards
    document.querySelectorAll('.booking-card').forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('show');
        }, index * 100);
    });
    
    // Initialize all images
    document.querySelectorAll('.booking-image').forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        }
    });
    
    // Get hash from URL (remove the # symbol)
    let hash = window.location.hash.substring(1);
    
    // If hash exists and corresponds to a tab
    if (hash && document.getElementById(hash)) {
        showLoading();
        
        // Remove active class from all tabs
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Activate the correct tab
        const targetTab = document.querySelector(`a[href="#${hash}"]`);
        const targetPane = document.getElementById(hash);
        
        if (targetTab && targetPane) {
            targetTab.classList.add('active');
            targetPane.classList.add('show', 'active');
            
            // Scroll to the top of the account section
            document.querySelector('.account-section').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        
        loadingTimeout = setTimeout(hideLoading, 500);
    }
});

// Update tab switching behavior with smooth transitions
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        showLoading();
        
        const targetId = this.getAttribute('href').substring(1);
        const targetPane = document.getElementById(targetId);
        
        // Remove active class from all tabs and panes
        document.querySelectorAll('.list-group-item').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Activate the clicked tab and its content
        this.classList.add('active');
        targetPane.classList.add('show', 'active');
        
        // Update URL hash without triggering scroll
        history.pushState(null, null, `#${targetId}`);
        
        // Initialize booking cards in the new tab
        if (targetId === 'bookings') {
            document.querySelectorAll('.booking-card').forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('show');
                }, index * 100);
            });
        }
        
        loadingTimeout = setTimeout(hideLoading, 300);
    });
});

// Update form submissions with improved loading states
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoading();
    
    const btn = document.getElementById('updateProfileBtn');
    btn.disabled = true;
    btn.innerHTML = 'Updating...';
    
    const formData = new FormData(this);
    
    fetch('../includes/process_profile_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        if (html.includes('Profile updated successfully')) {
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating your profile. Please try again.');
    })
    .finally(() => {
        hideLoading();
        btn.disabled = false;
        btn.innerHTML = 'Update Profile';
    });
});

// Update password change form with improved loading states
document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoading();
    
    const btn = document.getElementById('updatePasswordBtn');
    btn.disabled = true;
    btn.innerHTML = 'Updating...';
    
    fetch('../includes/process_password_change.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            confirm_password: document.getElementById('confirm_password').value
        })
    })
    .then(response => response.json())
    .then(data => {
        const alert = document.getElementById('passwordChangeAlert');
        alert.className = `alert alert-${data.success ? 'success' : 'danger'}`;
        alert.textContent = data.message;
        alert.style.display = 'block';
        
        if (data.success) {
            this.reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.getElementById('passwordChangeAlert');
        alert.className = 'alert alert-danger';
        alert.textContent = 'An error occurred. Please try again.';
        alert.style.display = 'block';
    })
    .finally(() => {
        hideLoading();
        btn.disabled = false;
        btn.innerHTML = 'Update Password';
    });
});
</script>

<?php include '../includes/footer.php'; ?> 