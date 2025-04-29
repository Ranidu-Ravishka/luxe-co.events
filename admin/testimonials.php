<?php
ob_start();
require_once 'includes/header.php';

// Handle testimonial actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'] ?? 0;
    $success = false;
    $message = '';
    
    switch ($_POST['action']) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $message = $success ? 'Testimonial approved successfully.' : 'Failed to approve testimonial.';
            break;
            
        case 'reject':
            $stmt = $conn->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $message = $success ? 'Testimonial rejected successfully.' : 'Failed to reject testimonial.';
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $message = $success ? 'Testimonial deleted successfully.' : 'Failed to delete testimonial.';
            break;
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['success'] = $success;
    
    // Redirect to remove the action from URL
    header("Location: testimonials.php");
    ob_end_flush();
    exit();
}

// Display message if exists in session
if (isset($_SESSION['message'])) {
    $alertClass = isset($_SESSION['success']) && $_SESSION['success'] ? 'alert-success' : 'alert-danger';
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['success']);
}

// Get all testimonials with user details
$testimonials = $conn->query("
    SELECT t.*, u.full_name, u.email 
    FROM testimonials t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
?>

<div class="container-fluid">
    <h2 class="mb-4">Manage Testimonials</h2>

    <!-- Testimonials Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="testimonialsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($testimonial = $testimonials->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $testimonial['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($testimonial['full_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['email']); ?></small>
                                </td>
                                <td>
                                    <?php for($i = 1; $i <= $testimonial['rating']; $i++): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?php echo htmlspecialchars($testimonial['content']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $testimonial['status'] === 'approved' ? 'success' : 
                                            ($testimonial['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($testimonial['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                <td>
                                    <?php if($testimonial['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Are you sure you want to approve this testimonial?');">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                    onclick="return confirm('Are you sure you want to reject this testimonial?');">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this testimonial? This action cannot be undone.');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#testimonialsTable').DataTable({
        order: [[5, 'desc']], // Sort by date column by default
        pageLength: 10, // Show 10 entries per page
        language: {
            search: "Search testimonials:",
            lengthMenu: "Show _MENU_ testimonials per page",
            info: "Showing _START_ to _END_ of _TOTAL_ testimonials",
            infoEmpty: "No testimonials available",
            infoFiltered: "(filtered from _MAX_ total testimonials)"
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 