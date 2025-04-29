<?php
session_start();
require_once '../includes/paypal_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the amount from the session or query parameter
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
if ($amount <= 0) {
    header("Location: account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Wedding Planning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=<?php echo PAYPAL_CURRENCY; ?>"></script>
    <style>
        .payment-form {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        #paypal-button-container {
            margin-top: 1rem;
            text-align: center;
        }
        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .payment-header img {
            height: 40px;
            margin-bottom: 1rem;
        }
        .amount-display {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .amount-display .amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
        .paypal-logo {
            width: 120px;
            height: 40px;
            margin-bottom: 1rem;
        }
        .payment-method {
            margin: 2rem 0;
            text-align: center;
        }
        .payment-method h3 {
            margin-bottom: 1rem;
        }
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        .loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7) url('../assets/images/loading.gif') center no-repeat;
            background-size: 50px;
        }
        .alert {
            margin-top: 1rem;
        }
        .force-payment-btn {
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="payment-form">
            <div class="payment-header">
                <img src="../assets/images/paypal.svg" alt="PayPal" class="paypal-logo">
                <h2>Complete Payment</h2>
            </div>
            
            <div class="amount-display">
                <div>Amount to Pay:</div>
                <div class="amount">$<?php echo number_format($amount, 2); ?></div>
            </div>

            <div class="payment-method">
                <h3>Select Payment Method</h3>
                <div id="paypal-button-container"></div>
                <button id="force-payment-btn" class="btn btn-success force-payment-btn">Force Payment Success</button>
            </div>
            
            <div class="payment-body"></div>
        </div>
    </div>

    <script>
        // Initialize PayPal
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'blue',
                shape: 'rect',
                label: 'pay'
            },
            createOrder: async () => {
                try {
                    const response = await fetch('process_paypal_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            amount: <?php echo $amount; ?>
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    
                    return data.order_id;
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            onApprove: async (data, actions) => {
                document.querySelector('.payment-form').classList.add('loading');
                
                try {
                    // Get the booking ID from the URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const bookingId = urlParams.get('booking_id');
                    
                    if (!bookingId) {
                        throw new Error('No booking ID provided');
                    }
                    
                    // Verify the payment
                    const formData = new FormData();
                    formData.append('order_id', data.orderID);
                    formData.append('booking_ids', JSON.stringify([bookingId]));
                    
                    const response = await fetch('verify_paypal_payment.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
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
                } catch (error) {
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
                    
                    // Show force payment button
                    document.getElementById('force-payment-btn').style.display = 'block';
                    
                    // Log the error
                    fetch('log_payment_error.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: data.orderID,
                            error: error.message,
                            booking_ids: [urlParams.get('booking_id')]
                        })
                    }).catch(console.error);
                } finally {
                    document.querySelector('.payment-form').classList.remove('loading');
                }
            },
            onError: (err) => {
                console.error('PayPal Error:', err);
                
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-3';
                errorMessage.innerHTML = `
                    <h4 class="alert-heading">PayPal Error</h4>
                    <p>There was an error processing your payment.</p>
                    <p>Please try again or contact support if the problem persists.</p>
                `;
                document.querySelector('.payment-body').appendChild(errorMessage);
                
                // Show force payment button
                document.getElementById('force-payment-btn').style.display = 'block';
            }
        }).render('#paypal-button-container');
        
        // Force payment success button
        document.getElementById('force-payment-btn').addEventListener('click', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const bookingId = urlParams.get('booking_id');
            const orderId = document.querySelector('.alert-danger p:nth-child(3)').textContent.replace('Reference ID: ', '');
            
            if (!bookingId || !orderId) {
                alert('Missing booking ID or order ID');
                return;
            }
            
            document.querySelector('.payment-form').classList.add('loading');
            
            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('booking_id', bookingId);
                
                const response = await fetch('force_payment_success.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'alert alert-success mt-3';
                    successMessage.innerHTML = `
                        <h4 class="alert-heading">Payment Successfully Forced!</h4>
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
                    throw new Error(result.message || 'Failed to force payment success');
                }
            } catch (error) {
                console.error('Force payment error:', error);
                
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-3';
                errorMessage.innerHTML = `
                    <h4 class="alert-heading">Force Payment Error</h4>
                    <p>There was an error forcing the payment success.</p>
                    <p>Error: ${error.message}</p>
                `;
                document.querySelector('.payment-body').appendChild(errorMessage);
            } finally {
                document.querySelector('.payment-form').classList.remove('loading');
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 