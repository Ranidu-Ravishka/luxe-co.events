<?php
// Get pending testimonials
$pending_testimonials = $conn->query("
    SELECT t.*, u.full_name 
    FROM testimonials t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.status = 'pending' 
    LIMIT 5
");
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Pending Testimonials</h6>
        <a href="testimonials.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <?php if($pending_testimonials->num_rows > 0): ?>
            <?php while($testimonial = $pending_testimonials->fetch_assoc()): ?>
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['full_name']); ?></h6>
                        <div>
                            <?php for($i = 1; $i <= $testimonial['rating']; $i++): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="mb-2"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                    <div class="d-flex gap-2">
                        <a href="testimonials.php?action=approve&id=<?php echo $testimonial['id']; ?>" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i> Approve
                        </a>
                        <a href="testimonials.php?action=reject&id=<?php echo $testimonial['id']; ?>" 
                           class="btn btn-sm btn-danger">
                            <i class="fas fa-times"></i> Reject
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center mb-0">No pending testimonials</p>
        <?php endif; ?>
    </div>
</div> 