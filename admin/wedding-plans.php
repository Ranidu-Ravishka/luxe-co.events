<?php
require_once '../includes/config.php';
requireAdmin();

// Function to send email notification
function sendEmailNotification($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Luxe & Co. Events <noreply@luxecoevents.com>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Handle status updates and planner assignments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['plan_id'])) {
        $plan_id = $_POST['plan_id'];
        $action = $_POST['action'];
        $message = '';
        
        switch ($action) {
            case 'assign_planner':
                $planner_id = $_POST['planner_id'] ?? null;
                if ($planner_id === '') {
                    $planner_id = null;
                }
                
                // Get planner and wedding plan details before updating
                $details_sql = "SELECT wp.*, u.email as user_email, u.full_name as client_name, 
                              wp2.email as planner_email, wp2.full_name as planner_name 
                              FROM wedding_plans wp 
                              JOIN users u ON wp.user_id = u.id 
                              LEFT JOIN wedding_planners wp2 ON wp2.id = ?";
                $stmt = $conn->prepare($details_sql);
                $stmt->bind_param("i", $planner_id);
                $stmt->execute();
                $details = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                // Update the wedding plan
                $stmt = $conn->prepare("UPDATE wedding_plans SET planner_id = ? WHERE id = ?");
                $stmt->bind_param("ii", $planner_id, $plan_id);
                
                if ($stmt->execute()) {
                    // Send email to user
                    if ($planner_id && $details) {
                        $userSubject = "Wedding Planner Assigned to Your Wedding";
                        $userMessage = "
                            <html>
                            <body>
                                <h2>Wedding Planner Assigned</h2>
                                <p>Dear {$details['client_name']},</p>
                                <p>We're pleased to inform you that a wedding planner has been assigned to your wedding:</p>
                                <p><strong>Wedding Planner:</strong> {$details['planner_name']}</p>
                                <p><strong>Wedding Date:</strong> " . date('F d, Y', strtotime($details['wedding_date'])) . "</p>
                                <p>Your wedding planner will contact you soon to discuss your wedding plans in detail.</p>
                                <p>Best regards,<br>Luxe & Co. Events Team</p>
                            </body>
                            </html>";
                        sendEmailNotification($details['user_email'], $userSubject, $userMessage);
                        
                        // Send email to planner
                        $plannerSubject = "New Wedding Assignment";
                        $plannerMessage = "
                            <html>
                            <body>
                                <h2>New Wedding Assignment</h2>
                                <p>Dear {$details['planner_name']},</p>
                                <p>You have been assigned to a new wedding:</p>
                                <p><strong>Client:</strong> {$details['client_name']}</p>
                                <p><strong>Wedding Date:</strong> " . date('F d, Y', strtotime($details['wedding_date'])) . "</p>
                                <p><strong>Venue:</strong> {$details['venue_preference']}</p>
                                <p><strong>Guest Count:</strong> {$details['guest_count']}</p>
                                <p>Please log in to your dashboard to view the complete wedding details and contact the client.</p>
                                <p>Best regards,<br>Luxe & Co. Events Team</p>
                            </body>
                            </html>";
                        sendEmailNotification($details['planner_email'], $plannerSubject, $plannerMessage);
                    }
                    $_SESSION['success'] = "Wedding planner assigned successfully.";
                } else {
                    $_SESSION['error'] = "Error assigning wedding planner.";
                }
                $stmt->close();
                header("Location: wedding-plans.php");
                exit();
                
            case 'approve':
                // Get wedding plan details
                $details_sql = "SELECT wp.*, u.email as user_email, u.full_name as client_name,
                              wpl.email as planner_email, wpl.full_name as planner_name
                              FROM wedding_plans wp 
                              JOIN users u ON wp.user_id = u.id
                              LEFT JOIN wedding_planners wpl ON wp.planner_id = wpl.id 
                              WHERE wp.id = ?";
                $stmt = $conn->prepare($details_sql);
                $stmt->bind_param("i", $plan_id);
                $stmt->execute();
                $details = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE wedding_plans SET status = 'in_progress' WHERE id = ?");
                $stmt->bind_param("i", $plan_id);
                
                if ($stmt->execute()) {
                    // Send approval email to user
                    $userSubject = "Your Wedding Plan Has Been Approved";
                    $userMessage = "
                        <html>
                        <body>
                            <h2>Wedding Plan Approved</h2>
                            <p>Dear {$details['client_name']},</p>
                            <p>We're pleased to inform you that your wedding plan has been approved and is now in progress!</p>
                            <p><strong>Wedding Date:</strong> " . date('F d, Y', strtotime($details['wedding_date'])) . "</p>
                            " . ($details['planner_name'] ? "<p><strong>Your Wedding Planner:</strong> {$details['planner_name']}</p>" : "") . "
                            <p>We're excited to help make your special day perfect!</p>
                            <p>Best regards,<br>Luxe & Co. Events Team</p>
                        </body>
                        </html>";
                    sendEmailNotification($details['user_email'], $userSubject, $userMessage);

                    // Send notification to planner if assigned
                    if ($details['planner_email']) {
                        $plannerSubject = "Wedding Plan Approved - Action Required";
                        $plannerMessage = "
                            <html>
                            <body>
                                <h2>Wedding Plan Approved</h2>
                                <p>Dear {$details['planner_name']},</p>
                                <p>A wedding plan you're assigned to has been approved:</p>
                                <p><strong>Client:</strong> {$details['client_name']}</p>
                                <p><strong>Wedding Date:</strong> " . date('F d, Y', strtotime($details['wedding_date'])) . "</p>
                                <p><strong>Venue:</strong> {$details['venue_preference']}</p>
                                <p><strong>Guest Count:</strong> {$details['guest_count']}</p>
                                <p>Please begin the planning process and contact the client to discuss next steps.</p>
                                <p>Best regards,<br>Luxe & Co. Events Team</p>
                            </body>
                            </html>";
                        sendEmailNotification($details['planner_email'], $plannerSubject, $plannerMessage);
                    }
                    $_SESSION['success'] = "Wedding plan approved successfully.";
                } else {
                    $_SESSION['error'] = "Error approving wedding plan.";
                }
                $stmt->close();
                header("Location: wedding-plans.php");
                exit();
                
            case 'complete':
                $stmt = $conn->prepare("UPDATE wedding_plans SET status = 'completed' WHERE id = ?");
                $stmt->bind_param("i", $plan_id);
                $message = "Wedding plan marked as completed.";
                break;
                
            case 'delete':
                // First get the file paths
                $get_files_sql = "SELECT guest_list_file, invitation_files FROM wedding_plans WHERE id = ?";
                $stmt = $conn->prepare($get_files_sql);
                $stmt->bind_param("i", $plan_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $files = $result->fetch_assoc();
                $stmt->close();
                
                // Delete physical files
                if ($files) {
                    if ($files['guest_list_file']) {
                        $guest_list_path = __DIR__ . '/../' . $files['guest_list_file'];
                        if (file_exists($guest_list_path)) {
                            unlink($guest_list_path);
                        }
                    }
                    
                    if ($files['invitation_files']) {
                        $invitation_files = json_decode($files['invitation_files'], true);
                        if ($invitation_files) {
                            foreach ($invitation_files as $file) {
                                $file_path = __DIR__ . '/../' . $file;
                                if (file_exists($file_path)) {
                                    unlink($file_path);
                                }
                            }
                        }
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM wedding_plans WHERE id = ?");
                $stmt->bind_param("i", $plan_id);
                $message = "Wedding plan deleted successfully.";
                break;
        }
        
        // Execute the prepared statement for non-assign_planner actions
        if (isset($stmt) && $action !== 'assign_planner') {
            if ($stmt->execute()) {
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = "Error performing action. Please try again.";
            }
            $stmt->close();
            header("Location: wedding-plans.php");
            exit();
        }
    }
}

// Fetch all wedding planners for the dropdown
$planners_sql = "SELECT id, full_name FROM wedding_planners WHERE is_active = 1 ORDER BY full_name";
$planners_result = $conn->query($planners_sql);
$wedding_planners = [];
if ($planners_result && $planners_result->num_rows > 0) {
    while ($row = $planners_result->fetch_assoc()) {
        $wedding_planners[] = $row;
    }
}

// Fetch all wedding plans with user information and confirmed bookings
$sql = "SELECT 
            wp.*, 
            u.username, 
            u.full_name,
            GROUP_CONCAT(DISTINCT CONCAT('• ', s.name) SEPARATOR '<br>') as booked_services,
            COUNT(DISTINCT b.id) as total_bookings,
            wpl.full_name as planner_name
        FROM wedding_plans wp 
        JOIN users u ON wp.user_id = u.id 
        LEFT JOIN bookings b ON wp.user_id = b.user_id AND b.status = 'confirmed'
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN wedding_planners wpl ON wp.planner_id = wpl.id
        GROUP BY wp.id
        ORDER BY wp.created_at DESC";
$result = $conn->query($sql);
$wedding_plans = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wedding_plans[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Wedding Plans</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="weddingPlansTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Bride & Groom</th>
                            <th>Wedding Date</th>
                            <th>Venue</th>
                            <th>Guests</th>
                            <th>Budget</th>
                            <th>Booked Services</th>
                            <th>Assigned Planner</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wedding_plans as $plan): ?>
                            <tr>
                                <td><?php echo $plan['id']; ?></td>
                                <td><?php echo htmlspecialchars($plan['full_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($plan['bride_name']); ?> & 
                                    <?php echo htmlspecialchars($plan['groom_name']); ?>
                                    <br>
                                    <small class="text-muted">
                                        Ages: <?php echo $plan['bride_age']; ?> & <?php echo $plan['groom_age']; ?>
                                    </small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($plan['wedding_date'])); ?></td>
                                <td><?php echo htmlspecialchars($plan['venue_preference']); ?></td>
                                <td><?php echo $plan['guest_count']; ?></td>
                                <td><?php echo htmlspecialchars($plan['budget_range']); ?></td>
                                <td>
                                    <?php if ($plan['total_bookings'] > 0): ?>
                                        <div class="booked-services">
                                            <?php echo $plan['booked_services']; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No confirmed bookings</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="assign-planner-form">
                                        <input type="hidden" name="action" value="assign_planner">
                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                        <select name="planner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">Select Planner</option>
                                            <?php foreach ($wedding_planners as $planner): ?>
                                                <option value="<?php echo $planner['id']; ?>" 
                                                    <?php echo ($plan['planner_id'] == $planner['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($planner['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $plan['status'] === 'completed' ? 'success' : 
                                            ($plan['status'] === 'in_progress' ? 'primary' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($plan['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($plan['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewPlanModal<?php echo $plan['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($plan['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this wedding plan?');">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($plan['status'] === 'in_progress'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                                <input type="hidden" name="action" value="complete">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to mark this wedding plan as completed?');">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this wedding plan? This action cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- View Plan Modal -->
                                    <div class="modal fade" id="viewPlanModal<?php echo $plan['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Wedding Plan Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Couple Information</h6>
                                                            <p><strong>Bride:</strong> <?php echo htmlspecialchars($plan['bride_name']); ?> (<?php echo $plan['bride_age']; ?>)</p>
                                                            <p><strong>Groom:</strong> <?php echo htmlspecialchars($plan['groom_name']); ?> (<?php echo $plan['groom_age']; ?>)</p>
                                                            <p><strong>Wedding Date:</strong> <?php echo date('M d, Y', strtotime($plan['wedding_date'])); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Venue & Guests</h6>
                                                            <p><strong>Venue Preference:</strong> <?php echo htmlspecialchars($plan['venue_preference']); ?></p>
                                                            <p><strong>Guest Count:</strong> <?php echo $plan['guest_count']; ?></p>
                                                            <p><strong>Budget Range:</strong> <?php echo htmlspecialchars($plan['budget_range']); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <h6>Guest Considerations</h6>
                                                            <p><?php echo nl2br(htmlspecialchars($plan['guest_considerations'])); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Additional Notes</h6>
                                                            <p><?php echo nl2br(htmlspecialchars($plan['additional_notes'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <?php if ($plan['guest_list_file']): ?>
                                                        <div class="mt-3">
                                                            <h6>Guest List</h6>
                                                            <a href="../uploads/<?php echo $plan['guest_list_file']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-download"></i> Download Guest List
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php 
                                                    $invitation_files = json_decode($plan['invitation_files'], true);
                                                    if ($invitation_files): ?>
                                                        <div class="mt-3">
                                                            <h6>Invitation Designs</h6>
                                                            <div class="row">
                                                                <?php foreach ($invitation_files as $file): ?>
                                                                    <div class="col-md-4 mb-2">
                                                                        <a href="../uploads/<?php echo $file; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                                            <i class="fas fa-image"></i> View Design
                                                                        </a>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.booked-services {
    max-height: 100px;
    overflow-y: auto;
    font-size: 0.9em;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    line-height: 1.6;
}

.booked-services::-webkit-scrollbar {
    width: 4px;
}

.booked-services::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.booked-services::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.booked-services::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.assign-planner-form select {
    min-width: 150px;
}
</style>

<script>
$(document).ready(function() {
    $('#weddingPlansTable').DataTable({
        order: [[10, 'desc']], // Sort by Created At by default
        pageLength: 10,
        responsive: true
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 