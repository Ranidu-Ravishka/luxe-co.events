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

// Get statistics from database
try {
    $stats = [
        'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
        'bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
        'services' => $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'],
        'testimonials' => $conn->query("SELECT COUNT(*) as count FROM testimonials")->fetch_assoc()['count'],
        'total_revenue' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'],
        'monthly_revenue' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'],
        'pending_plans' => $conn->query("SELECT COUNT(*) as count FROM wedding_plans WHERE status = 'pending'")->fetch_assoc()['count'],
        'active_plans' => $conn->query("SELECT COUNT(*) as count FROM wedding_plans WHERE status = 'in_progress'")->fetch_assoc()['count']
    ];

    // Get recent bookings
    $recent_bookings = $conn->query("
        SELECT b.*, u.full_name, s.name as service_name
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        LEFT JOIN services s ON b.service_id = s.id
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");

    // Get upcoming events
    $upcoming_events = $conn->query("
        SELECT wp.*, u.full_name as client_name, wpl.full_name as planner_name
        FROM wedding_plans wp 
        JOIN users u ON wp.user_id = u.id
        LEFT JOIN wedding_planners wpl ON wp.planner_id = wpl.id
        WHERE wp.wedding_date >= CURRENT_DATE()
        ORDER BY wp.wedding_date ASC
        LIMIT 5
    ");

    // Get recent activities
    $recent_activities = $conn->query("
        (SELECT 'booking' as type, b.created_at, u.full_name, 'made a new booking' as action
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
        LIMIT 5)
        UNION
        (SELECT 'plan' as type, wp.created_at, u.full_name, 'submitted a wedding plan' as action
        FROM wedding_plans wp
        JOIN users u ON wp.user_id = u.id
        ORDER BY wp.created_at DESC
        LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 10
    ");

    // Get monthly revenue data for chart
    $monthly_revenue = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(total_amount) as revenue
        FROM bookings
        WHERE status = 'confirmed'
        AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $revenue_data = [];
    while ($row = $monthly_revenue->fetch_assoc()) {
        $revenue_data[] = $row;
    }

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Include header
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0">Dashboard</h2>
        <div>
            <a href="wedding-plans.php" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-clipboard-list fa-sm text-white-50"></i> View Wedding Plans
            </a>
            <a href="bookings.php" class="btn btn-sm btn-success shadow-sm">
                <i class="fas fa-calendar fa-sm text-white-50"></i> Manage Bookings
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                            <div class="text-xs text-success mt-2">
                                <i class="fas fa-dollar-sign"></i> $<?php echo number_format($stats['monthly_revenue'], 2); ?> this month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Wedding Plans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_plans']; ?> Active</div>
                            <div class="text-xs text-warning mt-2">
                                <i class="fas fa-clock"></i> <?php echo $stats['pending_plans']; ?> pending approval
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Bookings & Services</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['bookings']; ?> Bookings</div>
                            <div class="text-xs text-info mt-2">
                                <i class="fas fa-concierge-bell"></i> <?php echo $stats['services']; ?> services available
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Users & Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?> Users</div>
                            <div class="text-xs text-warning mt-2">
                                <i class="fas fa-star"></i> <?php echo $stats['testimonials']; ?> testimonials
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Events</h6>
                </div>
                <div class="card-body">
                    <?php if($upcoming_events->num_rows > 0): ?>
                        <?php while($event = $upcoming_events->fetch_assoc()): ?>
                            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                                <div class="flex-shrink-0 text-center me-3" style="width: 60px;">
                                    <div class="text-primary fw-bold"><?php echo date('M', strtotime($event['wedding_date'])); ?></div>
                                    <div class="h4 mb-0"><?php echo date('d', strtotime($event['wedding_date'])); ?></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['client_name']); ?>'s Wedding</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue_preference']); ?>
                                        <?php if($event['planner_name']): ?>
                                            <br><i class="fas fa-user"></i> Planner: <?php echo htmlspecialchars($event['planner_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center mb-0">No upcoming events</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Event Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['full_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($booking['service_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['event_date'] ?? 'now')); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo ($booking['status'] ?? '') === 'confirmed' ? 'success' : 
                                                (($booking['status'] ?? '') === 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($booking['status'] ?? 'unknown'); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities & Pending Testimonials -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                </div>
                <div class="card-body">
                    <?php if($recent_activities->num_rows > 0): ?>
                        <?php while($activity = $recent_activities->fetch_assoc()): ?>
                            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar bg-light-<?php echo $activity['type'] === 'booking' ? 'success' : 'primary'; ?> p-2 rounded">
                                        <i class="fas fa-<?php echo $activity['type'] === 'booking' ? 'calendar-check' : 'clipboard-list'; ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">
                                        <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                        <?php echo $activity['action']; ?>
                                    </p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center mb-0">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Testimonials -->
            <?php require 'includes/pending_testimonials.php'; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueData = <?php echo json_encode($revenue_data); ?>;
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => {
            const [year, month] = item.month.split('-');
            return new Date(year, month - 1).toLocaleDateString('default', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
            label: 'Monthly Revenue',
            data: revenueData.map(item => item.revenue),
            borderColor: 'rgb(78, 115, 223)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<style>
.avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-light-primary {
    background-color: rgba(78, 115, 223, 0.1);
    color: #4e73df;
}
.bg-light-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}
.chart-area {
    height: 300px;
}
</style>

<?php require_once 'includes/footer.php'; ?> 