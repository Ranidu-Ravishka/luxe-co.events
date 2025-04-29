<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php';

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? 'pending';
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
}

// Get all bookings with user details and services
$query = "
    SELECT b.*, 
           u.full_name as customer_name, 
           u.email as customer_email,
           GROUP_CONCAT(s.name ORDER BY FIND_IN_SET(s.id, b.service_ids)) as service_names,
           b.service_quantities,
           b.service_prices,
           b.total_amount,
           b.event_date,
           b.special_requests
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN services s ON FIND_IN_SET(s.id, b.service_ids)
    GROUP BY b.id
    ORDER BY b.created_at DESC";

$bookings = $conn->query($query);

// Debug information
if (!$bookings) {
    echo '<div class="alert alert-danger">Query error: ' . $conn->error . '</div>';
}

// Check if there are any bookings
if ($bookings && $bookings->num_rows === 0) {
    echo '<div class="alert alert-info">No bookings found in the database.</div>';
}

?>

<div class="container-fluid">
    <h2 class="mb-4">Manage Bookings</h2>
    
    <!-- Debug information -->
    <?php if ($bookings): ?>
    <div class="alert alert-info">
        Number of bookings found: <?php echo $bookings->num_rows; ?>
    </div>
    <?php endif; ?>

    <!-- Bookings Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Event Date</th>
                            <th>Services</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($booking['customer_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></td>
                                <td>
                                    <?php
                                    $service_ids = $booking['service_ids'] ?? '';
                                    $service_prices = $booking['service_prices'] ?? '';
                                    
                                    if (!empty($service_ids) && !empty($service_prices)) {
                                        $service_id_array = explode(',', $service_ids);
                                        $service_price_array = explode(',', $service_prices);
                                        
                                        // Get service names
                                        $service_names = [];
                                        foreach ($service_id_array as $service_id) {
                                            $service_query = "SELECT name FROM services WHERE id = ?";
                                            $stmt = $conn->prepare($service_query);
                                            $stmt->bind_param("i", $service_id);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            if ($service = $result->fetch_assoc()) {
                                                $service_names[] = $service['name'];
                                            }
                                        }
                                        
                                        // Display services with their prices
                                        foreach ($service_names as $index => $name) {
                                            echo htmlspecialchars($name);
                                            if (isset($service_price_array[$index])) {
                                                echo " ($" . number_format((float)$service_price_array[$index], 2) . ")";
                                            }
                                            if ($index < count($service_names) - 1) {
                                                echo "<br>";
                                            }
                                        }
                                    } else {
                                        echo "No services";
                                    }
                                    ?>
                                </td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = ucfirst($booking['status']);
                                    
                                    switch($booking['status']) {
                                        case 'confirmed':
                                            $statusClass = 'bg-success';
                                            break;
                                        case 'pending':
                                            $statusClass = 'bg-warning';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge rounded-pill <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm status-btn" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <form method="POST" class="status-form">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <button type="submit" name="status" value="pending" class="dropdown-item <?php echo $booking['status'] === 'pending' ? 'active' : ''; ?>">
                                                        <i class="fas fa-clock text-warning"></i> Pending
                                                    </button>
                                                    <button type="submit" name="status" value="confirmed" class="dropdown-item <?php echo $booking['status'] === 'confirmed' ? 'active' : ''; ?>">
                                                        <i class="fas fa-check text-success"></i> Confirmed
                                                    </button>
                                                    <button type="submit" name="status" value="cancelled" class="dropdown-item <?php echo $booking['status'] === 'cancelled' ? 'active' : ''; ?>">
                                                        <i class="fas fa-times text-danger"></i> Cancelled
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Badge styles */
.badge {
    font-weight: 500;
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 50px;
    text-transform: capitalize;
    border: none;
}
.bg-success {
    background-color: #198754 !important;
    color: white;
}
.bg-warning {
    background-color: #ffc107 !important;
    color: black;
}
.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

/* Make badges more compact */
td .badge {
    display: inline-block;
    min-width: 90px;
    text-align: center;
}

/* Status button styles */
.status-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    border: none;
    background: transparent;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
}
.status-btn:hover {
    background-color: #f8f9fa;
    color: #000;
}

/* Dropdown styles */
.dropdown-menu {
    min-width: 160px;
    padding: 0.5rem 0;
    margin: 0;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    font-size: 14px;
}
.dropdown-item.active {
    background-color: #f8f9fa;
    color: inherit;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
.dropdown-item i {
    width: 16px;
}

/* Table styles */
#bookingsTable td {
    vertical-align: middle;
}
.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with custom options
    $('#bookingsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6] }, // Disable sorting on Actions column
            { className: "align-middle", targets: "_all" }
        ]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 