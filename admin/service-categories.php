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
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/categories/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Generate unique filename
                        $new_filename = uniqid() . '.' . $filetype;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_path = 'uploads/categories/' . $new_filename;
                        }
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO service_categories (name, description, image) VALUES (?, ?, ?)");
                if ($stmt->bind_param("sss", $name, $description, $image_path)) {
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Category added successfully!";
                    } else {
                        $message = "Error adding category: " . $conn->error;
                    }
                }
                break;

            case 'update':
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $status = $_POST['status'] ?? 'active';
                
                // Start with existing image path
                $image_path = $_POST['existing_image'] ?? '';
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($filetype, $allowed)) {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/categories/';
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
                            $image_path = 'uploads/categories/' . $new_filename;
                        } else {
                            $success = false;
                            $message = "Error uploading image. Please try again.";
                            break;
                        }
                    } else {
                        $success = false;
                        $message = "Invalid file type. Allowed types: JPG, JPEG, PNG, GIF";
                        break;
                    }
                }
                
                try {
                    $stmt = $conn->prepare("UPDATE service_categories SET name = ?, description = ?, status = ?, image = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $name, $description, $status, $image_path, $id);
                    
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Category updated successfully!";
                    } else {
                        $success = false;
                        $message = "Error updating category: " . $conn->error;
                    }
                } catch (Exception $e) {
                    $success = false;
                    $message = "Database error: " . $e->getMessage();
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                try {
                    // First check if there are any services using this category
                    $check_stmt = $conn->prepare("SELECT COUNT(*) as service_count FROM services WHERE category_id = ?");
                    $check_stmt->bind_param("i", $id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    $row = $check_result->fetch_assoc();
                    
                    if ($row['service_count'] > 0) {
                        $success = false;
                        $message = "Cannot delete this category because it has " . $row['service_count'] . " service(s) associated with it. Please delete or reassign these services first.";
                    } else {
                        // Get image path before deleting
                        $stmt = $conn->prepare("SELECT image FROM service_categories WHERE id = ?");
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
                        
                        // Delete the category
                        $stmt = $conn->prepare("DELETE FROM service_categories WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        
                        if ($stmt->execute()) {
                            $success = true;
                            $message = "Category deleted successfully!";
                        } else {
                            $success = false;
                            $message = "Error deleting category: " . $conn->error;
                        }
                    }
                } catch (Exception $e) {
                    $success = false;
                    $message = "Error: " . $e->getMessage();
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

// Get all categories
$categories = $conn->query("SELECT * FROM service_categories ORDER BY id DESC");
?>

<div class="container-fluid">
    <h2 class="mb-4">Manage Service Categories</h2>

    <!-- Add New Category Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add New Category
    </button>

    <!-- Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="categoriesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td>
                                    <?php if (!empty($category['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($category['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                             style="max-width: 50px; height: auto;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $category['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($category['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-category" 
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                            data-status="<?php echo $category['status']; ?>"
                                            data-image="<?php echo htmlspecialchars($category['image']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-category" 
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editCategoryForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="existing_image" id="edit_existing_image">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
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
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete the category: <strong id="delete_category_name"></strong>?</p>
                    <p class="text-danger">Warning: This will permanently delete the category and its image.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#categoriesTable').DataTable();

    // Edit Category
    $('.edit-category').click(function() {
        const data = $(this).data();
        $('#edit_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_description').val(data.description);
        $('#edit_status').val(data.status);
        $('#edit_existing_image').val(data.image);
        
        // Show current image if exists
        const currentImagePreview = $('#current_image_preview');
        if (data.image) {
            currentImagePreview.html(`
                <div class="position-relative">
                    <img src="../${data.image}" alt="Current Image" style="max-width: 200px; height: auto;" class="img-thumbnail">
                </div>
            `);
        } else {
            currentImagePreview.html('<p class="text-muted">No image uploaded</p>');
        }
        
        $('#editCategoryModal').modal('show');
    });

    // Form validation and file type check
    $('#editCategoryForm').on('submit', function(e) {
        const fileInput = $('#edit_image')[0];
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Invalid file type. Please upload a JPG, JPEG, PNG, or GIF file.');
                return false;
            }

            if (file.size > maxSize) {
                e.preventDefault();
                alert('File is too large. Maximum size is 5MB.');
                return false;
            }
        }
    });

    // Delete Category
    $('.delete-category').click(function() {
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        $('#delete_id').val(categoryId);
        $('#delete_category_name').text(categoryName);
        $('#deleteCategoryModal').modal('show');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

// End output buffering at the end of the file
ob_end_flush(); 