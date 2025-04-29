<?php
ob_start(); // Start output buffering
require_once 'includes/header.php';

// Handle file upload and gallery actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'upload':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $category = $_POST['category'];
                
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/gallery/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Generate unique filename
                        $new_filename = uniqid() . '.' . $filetype;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Save to database
                            $image_path = 'uploads/gallery/' . $new_filename;
                            $stmt = $conn->prepare("INSERT INTO gallery (title, image, description, category) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("ssss", $title, $image_path, $description, $category);
                            $stmt->execute();
                        }
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['photo_id'];
                
                // Get image path before deleting
                $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($photo = $result->fetch_assoc()) {
                    // Delete file
                    $file_path = '../' . $photo['image'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                // Delete from database
                $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
                
            case 'update_status':
                $id = $_POST['photo_id'];
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE gallery SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $id);
                $stmt->execute();
                break;
        }
        
        // Clean output buffer and redirect
        ob_end_clean();
        header("Location: gallery.php");
        exit();
    }
}

// Get all photos
$photos = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Gallery</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
            <i class="fas fa-plus"></i> Upload Photo
        </button>
    </div>

    <!-- Gallery Grid -->
    <div class="row">
        <?php while ($photo = $photos->fetch_assoc()): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <img src="../<?php echo htmlspecialchars($photo['image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($photo['title']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($photo['title']); ?></h5>
                        <p class="card-text small"><?php echo htmlspecialchars($photo['description']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">Category: <?php echo htmlspecialchars($photo['category']); ?></small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary update-status"
                                        data-photo-id="<?php echo $photo['id']; ?>"
                                        data-status="<?php echo $photo['status']; ?>">
                                    <?php echo $photo['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-photo"
                                        data-photo-id="<?php echo $photo['id']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePhotoModal">
                                    Delete
                                </button>
                            </div>
                            <small class="text-muted">
                                <?php echo date('M d, Y', strtotime($photo['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Upload Photo Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Wedding">Wedding</option>
                            <option value="Pre-Wedding">Pre-Wedding</option>
                            <option value="Reception">Reception</option>
                            <option value="Decoration">Decoration</option>
                            <option value="Venue">Venue</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Photo Modal -->
<div class="modal fade" id="deletePhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this photo? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="photo_id" id="delete_photo_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Photo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Photo
    $('.delete-photo').click(function() {
        const photoId = $(this).data('photo-id');
        $('#delete_photo_id').val(photoId);
    });

    // Update Status
    $('.update-status').click(function() {
        const photoId = $(this).data('photo-id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        // Create and submit form
        const form = $('<form method="POST">')
            .append($('<input type="hidden" name="action" value="update_status">'))
            .append($('<input type="hidden" name="photo_id">').val(photoId))
            .append($('<input type="hidden" name="status">').val(newStatus));
        
        $('body').append(form);
        form.submit();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 