<?php
// Start output buffering at the very beginning
ob_start();
session_start();
require_once 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? 0;
                $category_id = $_POST['category_id'] ?? 0;
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/services/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Generate unique filename
                        $new_filename = uniqid() . '.' . $filetype;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_path = 'uploads/services/' . $new_filename;
                        }
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO services (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->bind_param("ssdis", $name, $description, $price, $category_id, $image_path)) {
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Service added successfully!";
                    } else {
                        $message = "Error adding service: " . $conn->error;
                    }
                }
                break;

            case 'update':
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? 0;
                $status = $_POST['status'] ?? 'active';
                
                // Handle image upload for update
                $image_path = $_POST['existing_image'] ?? '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/services/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Generate unique filename
                        $new_filename = uniqid() . '.' . $filetype;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Delete old image if exists
                            if (!empty($_POST['existing_image'])) {
                                $old_image_path = '../' . $_POST['existing_image'];
                                if (file_exists($old_image_path)) {
                                    unlink($old_image_path);
                                }
                            }
                            $image_path = 'uploads/services/' . $new_filename;
                        }
                    }
                }
                
                $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, status = ?, image = ? WHERE id = ?");
                if ($stmt->bind_param("ssdssi", $name, $description, $price, $status, $image_path, $id)) {
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Service updated successfully!";
                    } else {
                        $message = "Error updating service: " . $conn->error;
                    }
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                // Get image path before deleting
                $stmt = $conn->prepare("SELECT image FROM services WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($image_data = $result->fetch_assoc()) {
                    // Delete image file if exists
                    if (!empty($image_data['image'])) {
                        $image_path = '../' . $image_data['image'];
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
                if ($stmt->bind_param("i", $id)) {
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Service deleted successfully!";
                    } else {
                        $message = "Error deleting service: " . $conn->error;
                    }
                }
                break;
        }
        
        // Store message in session
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $success ? 'success' : 'danger';
        
        // Clean output buffer before redirect
        ob_end_clean();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Display message if exists
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Get all services
$services = $conn->query("
    SELECT s.*, c.name as category_name 
    FROM services s 
    LEFT JOIN service_categories c ON s.category_id = c.id 
    ORDER BY s.id DESC
");

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM service_categories WHERE status = 'active'");
?>

<div class="container-fluid">
    <h2 class="mb-4">Manage Services</h2>

    <!-- Add New Service Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="fas fa-plus"></i> Add New Service
    </button>

    <!-- Services Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="servicesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($service = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td>
                                    <?php if (!empty($service['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($service['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                             style="max-width: 50px; height: auto;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo htmlspecialchars($service['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>$<?php echo number_format($service['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-service" 
                                            data-id="<?php echo $service['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($service['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($service['description']); ?>"
                                            data-price="<?php echo $service['price']; ?>"
                                            data-status="<?php echo $service['status']; ?>"
                                            data-image="<?php echo htmlspecialchars($service['image']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-service" 
                                            data-id="<?php echo $service['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($service['name']); ?>">
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

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Service Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="existing_image" id="edit_existing_image">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div id="current_image_preview" class="mb-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Update Image</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image. Supported formats: JPG, JPEG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Service Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete the service: <strong id="delete_service_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Service
    $('.edit-service').click(function() {
        const data = $(this).data();
        $('#edit_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_description').val(data.description);
        $('#edit_price').val(data.price);
        $('#edit_status').val(data.status);
        $('#edit_existing_image').val(data.image);
        
        // Update image preview
        const imagePreview = $('#current_image_preview');
        if (data.image) {
            imagePreview.html(`<img src="../${data.image}" alt="Current Image" style="max-width: 200px; height: auto;">`);
        } else {
            imagePreview.html('<span class="text-muted">No current image</span>');
        }
        
        $('#editServiceModal').modal('show');
    });

    // Delete Service
    $('.delete-service').click(function() {
        const data = $(this).data();
        $('#delete_id').val(data.id);
        $('#delete_service_name').text(data.name);
        $('#deleteServiceModal').modal('show');
    });

    // Initialize DataTable
    $('#servicesTable').DataTable({
        order: [[0, 'desc']]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

// End output buffering at the end of the file
ob_end_flush(); 