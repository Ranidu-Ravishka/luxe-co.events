<?php
// PayPal API configuration
define('PAYPAL_CLIENT_ID', 'AVSV4x2wvYbB71mvesvXS9T0KdqAv_9gknXd7en690LjjVCuNzl5xnV9kHCougwSIfZRJVEwkCnVz4Hd');
define('PAYPAL_CLIENT_SECRET', 'EAN9wz8k4Thb1SRgMY-Bnp2ESK46B325gpnL_ThOtloquZ3snbxiN_1Rf6miy9fxxmNmZ458NHMh9LHL');
define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production

// Currency settings
define('PAYPAL_CURRENCY', 'USD');

// Success and cancel URLs
define('PAYPAL_SUCCESS_URL', 'http://localhost/WeddinPlaning/pages/payment_success.php');
define('PAYPAL_CANCEL_URL', 'http://localhost/WeddinPlaning/pages/payment_cancel.php');

// PayPal API endpoints
define('PAYPAL_API_BASE', PAYPAL_MODE === 'sandbox' 
    ? 'https://api-m.sandbox.paypal.com'
    : 'https://api-m.paypal.com');
?> 