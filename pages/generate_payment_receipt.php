<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please log in to download receipts');
}

// Check if payment ID is provided
if (!isset($_GET['payment_id'])) {
    die('Payment ID is required');
}

$payment_id = (int)$_GET['payment_id'];

// Get payment details
$query = "SELECT p.*, b.booking_date, b.event_date, b.guest_count, b.total_amount,
          s.name as service_name, s.price as service_price,
          u.full_name, u.email, u.phone
          FROM payments p
          JOIN bookings b ON p.booking_id = b.id
          JOIN services s ON b.service_id = s.id
          JOIN users u ON b.user_id = u.id
          WHERE p.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die('Payment not found or unauthorized access');
}

// Set headers for PDF download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="Payment_Receipt_' . $payment['transaction_id'] . '.html"');

// Generate HTML receipt
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt - <?php echo $payment['transaction_id']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #444;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .receipt {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Wedding Planning - Payment Receipt</h1>
        </div>

        <div class="section">
            <h2>Transaction Details</h2>
            <table>
                <tr>
                    <th>Transaction ID</th>
                    <td><?php echo $payment['transaction_id']; ?></td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?php echo ucfirst($payment['status']); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Customer Information</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo $payment['full_name']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $payment['email']; ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo $payment['phone']; ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Booking Details</h2>
            <table>
                <tr>
                    <th>Booking Date</th>
                    <td><?php echo date('F j, Y', strtotime($payment['booking_date'])); ?></td>
                </tr>
                <tr>
                    <th>Event Date</th>
                    <td><?php echo date('F j, Y', strtotime($payment['event_date'])); ?></td>
                </tr>
                <tr>
                    <th>Service</th>
                    <td><?php echo $payment['service_name']; ?></td>
                </tr>
                <tr>
                    <th>Guest Count</th>
                    <td><?php echo $payment['guest_count']; ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Payment Information</h2>
            <table>
                <tr>
                    <th>Amount Paid</th>
                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td>Credit Card (****<?php echo $payment['card_last_four']; ?>)</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing our wedding planning services!</p>
            <p>This is an official receipt for your payment.</p>
        </div>
    </div>
</body>
</html>
?> 