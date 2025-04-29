<?php
require_once 'includes/header.php';

// Handle blog post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = $_POST['title'];
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'];
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, excerpt, status, author_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssi", $title, $content, $excerpt, $status, $_SESSION['user_id']);
                $stmt->execute();
                break;
                
            case 'update':
                $id = $_POST['post_id'];
                $title = $_POST['title'];
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'];
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssssi", $title, $content, $excerpt, $status, $id);
                $stmt->execute();
                break;
                
            case 'delete':
                $id = $_POST['post_id'];
                $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        
        header("Location: blog.php");
        exit();
    }
}

// Get all blog posts
$posts = $conn->query("
    SELECT p.*, u.full_name as author_name 
    FROM blog_posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Blog Posts</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
            <i class="fas fa-plus"></i> New Post
        </button>
    </div>

    <!-- Blog Posts Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="blogPostsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Excerpt</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $posts->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['excerpt']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-post" 
                                            data-post='<?php echo json_encode($post); ?>'
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPostModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-post"
                                            data-post-id="<?php echo $post['id']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deletePostModal">
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

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Blog Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control editor" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Post Modal -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Blog Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="post_id" id="edit_post_id">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" id="edit_excerpt" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" id="edit_content" class="form-control editor" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Post Modal -->
<div class="modal fade" id="deletePostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Blog Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this blog post? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" id="delete_post_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE
    tinymce.init({
        selector: '.editor',
        height: 300,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help'
    });

    // Initialize DataTable
    if (!$.fn.DataTable.isDataTable('#blogPostsTable')) {
        $('#blogPostsTable').DataTable({
            "order": [[5, "desc"]]
        });
    }

    // Edit Post
    $('.edit-post').click(function() {
        const post = $(this).data('post');
        $('#edit_post_id').val(post.id);
        $('#edit_title').val(post.title);
        $('#edit_excerpt').val(post.excerpt);
        tinymce.get('edit_content').setContent(post.content);
        $('#edit_status').val(post.status);
    });

    // Delete Post
    $('.delete-post').click(function() {
        const id = $(this).data('post-id');
        $('#delete_post_id').val(id);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 