<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in as admin
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $bio = $_POST['bio'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO wedding_planners (full_name, email, phone, bio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $phone, $bio);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Wedding planner added successfully!";
            } else {
                $_SESSION['error'] = "Error adding wedding planner: " . $stmt->error;
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? 0;
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $is_active = $_POST['is_active'] ?? 1;
            
            $stmt = $conn->prepare("UPDATE wedding_planners SET full_name = ?, email = ?, phone = ?, bio = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $full_name, $email, $phone, $bio, $is_active, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Wedding planner updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating wedding planner: " . $stmt->error;
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            
            $stmt = $conn->prepare("DELETE FROM wedding_planners WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Wedding planner deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting wedding planner: " . $stmt->error;
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header("Location: wedding-planners.php");
    exit();
}

// Get all wedding planners
$planners = $conn->query("SELECT * FROM wedding_planners ORDER BY created_at DESC");

// Include header
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Wedding Planners</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlannerModal">
            <i class="fas fa-plus"></i> Add New Planner
        </button>
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

    <!-- Wedding Planners Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="plannersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($planner = $planners->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $planner['id']; ?></td>
                                <td><?php echo htmlspecialchars($planner['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($planner['email']); ?></td>
                                <td><?php echo htmlspecialchars($planner['phone']); ?></td>
                                <td>
                                    <span class="badge <?php echo $planner['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $planner['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-planner" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPlannerModal"
                                            data-id="<?php echo $planner['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($planner['full_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($planner['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($planner['phone']); ?>"
                                            data-bio="<?php echo htmlspecialchars($planner['bio']); ?>"
                                            data-active="<?php echo $planner['is_active']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-planner"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deletePlannerModal"
                                            data-id="<?php echo $planner['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($planner['full_name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Planner Modal -->
<div class="modal fade" id="addPlannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Wedding Planner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" name="bio" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Planner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Planner Modal -->
<div class="modal fade" id="editPlannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Wedding Planner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="edit_phone" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" name="bio" id="edit_bio" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active" id="edit_is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Planner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Planner Modal -->
<div class="modal fade" id="deletePlannerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Wedding Planner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Planner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#plannersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10
    });
    
    // Handle edit planner
    $('.edit-planner').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');
        const phone = $(this).data('phone');
        const bio = $(this).data('bio');
        const active = $(this).data('active');
        
        $('#edit_id').val(id);
        $('#edit_full_name').val(name);
        $('#edit_email').val(email);
        $('#edit_phone').val(phone);
        $('#edit_bio').val(bio);
        $('#edit_is_active').val(active);
    });
    
    // Handle delete planner
    $('.delete-planner').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#delete_id').val(id);
        $('#delete_name').text(name);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 