<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/paypal_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all pending bookings
$query = "SELECT b.*, GROUP_CONCAT(s.name) as service_names 
          FROM bookings b
          LEFT JOIN services s ON FIND_IN_SET(s.id, b.service_ids)
          WHERE b.user_id = ? AND b.status = 'pending'
          GROUP BY b.id";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pending_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total amount
$total_amount = array_sum(array_column($pending_bookings, 'total_amount'));

$pageTitle = "Pay All Pending Bookings";
include_once('../includes/header.php');
?>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=<?php echo PAYPAL_CURRENCY; ?>&intent=capture"></script>

<style>
    .main-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: #f8f9fa;
    }
    .payment-container {
        background: white;
        width: 100%;
        max-width: 450px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .payment-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        position: relative;
    }
    .payment-header h1 {
        font-size: 20px;
        margin: 0;
        font-weight: 500;
    }
    .payment-body {
        padding: 15px 20px;
    }
    .booking-list {
        margin-bottom: 15px;
    }
    .booking-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .booking-item:last-child {
        border-bottom: none;
    }
    .booking-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 5px;
    }
    .booking-title {
        font-weight: 500;
        color: #333;
    }
    .booking-amount {
        font-weight: 500;
        color: #333;
    }
    .booking-details {
        color: #666;
        font-size: 14px;
    }
    .booking-date {
        color: #666;
        font-size: 14px;
    }
    .total-section {
        padding-top: 15px;
        border-top: 2px solid #eee;
        margin-top: 10px;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .total-label {
        font-size: 16px;
        font-weight: 500;
    }
    .total-amount {
        font-size: 20px;
        font-weight: 600;
    }
    #paypal-button-container {
        margin-top: 20px;
    }
    .close-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        padding: 5px;
        line-height: 1;
    }
    .close-btn:hover {
        color: #333;
    }
    .loading {
        pointer-events: none;
        opacity: 0.7;
    }
</style>

<div class="main-container">
    <div class="payment-container">
        <div class="payment-header">
            <h1>Pay All Pending Bookings</h1>
            <a href="account.php#bookings" class="close-btn">&times;</a>
        </div>
        <div class="payment-body">
            <div class="booking-list">
                <?php foreach ($pending_bookings as $booking): ?>
                <div class="booking-item">
                    <div class="booking-row">
                        <div class="booking-title">Booking #<?php echo $booking['id']; ?></div>
                        <div class="booking-amount">$<?php echo number_format($booking['total_amount'], 2); ?></div>
                    </div>
                    <div class="booking-details"><?php echo $booking['service_names']; ?></div>
                    <div class="booking-date">
                        <?php echo date('M d, Y', strtotime($booking['event_date'])); ?> • 
                        <?php echo $booking['guest_count']; ?> guests
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="total-section">
                <div class="total-row">
                    <div class="total-label">Total Amount</div>
                    <div class="total-amount">$<?php echo number_format($total_amount, 2); ?></div>
                </div>
            </div>

            <div id="paypal-button-container"></div>
        </div>
    </div>
</div>

<script>
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'gold',
            shape: 'pill',
            label: 'paypal'
        },
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo number_format($total_amount, 2, '.', ''); ?>'
                    },
                    description: 'Wedding Planning Services - Bulk Payment'
                }]
            });
        },
        onApprove: function(data, actions) {
            document.querySelector('.payment-container').classList.add('loading');
            
            const formData = new FormData();
            formData.append('order_id', data.orderID);
            formData.append('booking_ids', JSON.stringify(<?php echo json_encode(array_column($pending_bookings, 'id')); ?>));
            formData.append('is_bulk', 'true');

            return fetch('verify_paypal_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'alert alert-success mt-3';
                    successMessage.innerHTML = `
                        <h4 class="alert-heading">Payment Successful!</h4>
                        <p>Your payment has been processed successfully.</p>
                        <p>Transaction ID: ${result.transaction_id}</p>
                        <hr>
                        <p class="mb-0">Redirecting to your bookings...</p>
                    `;
                    document.querySelector('.payment-body').appendChild(successMessage);
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = '<?php echo PAYPAL_SUCCESS_URL; ?>';
                    }, 3000);
                } else {
                    throw new Error(result.message || 'Payment verification failed');
                }
            })
            .catch(error => {
                console.error('Payment verification error:', error);
                
                // Show error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-3';
                errorMessage.innerHTML = `
                    <h4 class="alert-heading">Payment Error</h4>
                    <p>There was an error confirming your payment.</p>
                    <p>Reference ID: ${data.orderID}</p>
                    <hr>
                    <p class="mb-0">Please contact support with the reference ID above.</p>
                `;
                document.querySelector('.payment-body').appendChild(errorMessage);
                
                // Log the error
                fetch('log_payment_error.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: data.orderID,
                        error: error.message,
                        booking_ids: <?php echo json_encode(array_column($pending_bookings, 'id')); ?>
                    })
                }).catch(console.error);
            })
            .finally(() => {
                document.querySelector('.payment-container').classList.remove('loading');
            });
        },
        onCancel: function() {
            const cancelMessage = document.createElement('div');
            cancelMessage.className = 'alert alert-warning mt-3';
            cancelMessage.innerHTML = `
                <h4 class="alert-heading">Payment Cancelled</h4>
                <p>Your payment has been cancelled. No charges were made to your account.</p>
                <hr>
                <p class="mb-0">You can try again when ready.</p>
            `;
            document.querySelector('.payment-body').appendChild(cancelMessage);
        },
        onError: function(err) {
            console.error('PayPal Error:', err);
            
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger mt-3';
            errorMessage.innerHTML = `
                <h4 class="alert-heading">PayPal Error</h4>
                <p>There was an error processing your payment.</p>
                <p>Please try again or contact support if the problem persists.</p>
            `;
            document.querySelector('.payment-body').appendChild(errorMessage);
            
            document.querySelector('.payment-container').classList.remove('loading');
        }
    }).render('#paypal-button-container');
</script>

<?php include '../includes/footer.php'; ?> 