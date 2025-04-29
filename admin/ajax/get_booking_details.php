<?php
require_once '../../includes/config.php';

if (!isset($_GET['booking_id'])) {
    die('Booking ID is required');
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details with services
$sql = "SELECT b.*, u.full_name, u.email,
        GROUP_CONCAT(s.name ORDER BY FIND_IN_SET(s.id, b.service_ids)) as service_names,
        b.service_quantities,
        b.service_prices
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN services s ON FIND_IN_SET(s.id, b.service_ids)
        WHERE b.id = ?
        GROUP BY b.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Booking not found');
}

$booking = $result->fetch_assoc();

// Get status badge class
$statusClass = '';
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

<div class="booking-details">
    <div class="row mb-4">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Customer Information</h6>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong><br> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                    <p><strong>Email:</strong><br> <?php echo htmlspecialchars($booking['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Event Date:</strong><br> <?php echo date('M d, Y', strtotime($booking['event_date'])); ?></p>
                    <p><strong>Status:</strong><br>
                        <span class="badge <?php echo $statusClass; ?> rounded-pill" style="font-size: 14px; padding: 8px 15px;">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Booked Services</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($booking['service_names']) {
                            $services = explode(',', $booking['service_names']);
                            $quantities = explode(',', $booking['service_quantities']);
                            $prices = explode(',', $booking['service_prices']);
                            $total = 0;
                            
                            for ($i = 0; $i < count($services); $i++) {
                                $service_total = $quantities[$i] * $prices[$i];
                                $total += $service_total;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($services[$i]); ?></td>
                            <td class="text-center"><?php echo $quantities[$i]; ?></td>
                            <td class="text-end">$<?php echo number_format($prices[$i], 2); ?></td>
                            <td class="text-end">$<?php echo number_format($service_total, 2); ?></td>
                        </tr>
                        <?php 
                            }
                        ?>
                        <tr class="table-active fw-bold">
                            <td colspan="3" class="text-end">Total Amount:</td>
                            <td class="text-end">$<?php echo number_format($total, 2); ?></td>
                        </tr>
                        <?php 
                        } else {
                            echo '<tr><td colspan="4" class="text-center">No services booked</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($booking['special_requests'])): ?>
    <div class="row">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Special Requests</h6>
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.booking-details p {
    margin-bottom: 0.5rem;
}
.booking-details strong {
    color: #555;
}
.table-sm td, .table-sm th {
    padding: 0.5rem;
}
</style> 