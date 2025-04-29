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

// Get payment statistics
try {
    // Total income
    $total_income = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM payments 
        WHERE status = 'completed'
    ")->fetch_assoc()['total'];

    // Monthly income
    $monthly_income = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM payments 
        WHERE status = 'completed' 
        AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(payment_date) = YEAR(CURRENT_DATE())
    ")->fetch_assoc()['total'];

    // Pending payments
    $pending_payments = $conn->query("
        SELECT COUNT(*) as count 
        FROM payments 
        WHERE status = 'pending'
    ")->fetch_assoc()['count'];

    // Failed payments
    $failed_payments = $conn->query("
        SELECT COUNT(*) as count 
        FROM payments 
        WHERE status = 'failed'
    ")->fetch_assoc()['count'];

    // Get recent payments with user and booking details
    $recent_payments = $conn->query("
        SELECT p.*, u.full_name, b.booking_date, b.event_date, b.total_amount,
               s.name as service_name
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");

    // Get monthly income data for the chart (last 6 months)
    $monthly_data = $conn->query("
        SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            COALESCE(SUM(amount), 0) as total
        FROM payments
        WHERE status = 'completed'
        AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month ASC
    ");

    $chart_labels = [];
    $chart_data = [];
    while ($row = $monthly_data->fetch_assoc()) {
        $chart_labels[] = date('M Y', strtotime($row['month']));
        $chart_data[] = $row['total'];
    }

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Include header
require_once 'includes/header.php';
?>

<h2 class="mb-4">Payments & Income</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_income, 2); ?></div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_income, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Payments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_payments; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed Payments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $failed_payments; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Income Chart -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Monthly Income (Last 6 Months)</h6>
    </div>
    <div class="card-body">
        <canvas id="incomeChart"></canvas>
    </div>
</div>

<!-- Recent Payments Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Booking ID</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($payment = $recent_payments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                        <td>#<?php echo $payment['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($payment['service_name']); ?></td>
                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $payment['status'] === 'completed' ? 'success' : 
                                    ($payment['status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize the income chart
const ctx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Monthly Income',
            data: <?php echo json_encode($chart_data); ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderWidth: 2,
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            tension: 0.3
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

// Initialize DataTable
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 