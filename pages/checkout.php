<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../includes/login & registration.php');
    exit();
}

// Check if cart table exists and has correct structure
$check_cart_table = "SHOW TABLES LIKE 'cart'";
$cart_table_exists = $conn->query($check_cart_table)->num_rows > 0;

if (!$cart_table_exists) {
    $create_cart_table = "CREATE TABLE cart (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (service_id) REFERENCES services(id)
    )";
    
    if (!$conn->query($create_cart_table)) {
        error_log("Error creating cart table: " . $conn->error);
        $_SESSION['error'] = "System error. Please try again later.";
        header('Location: services.php');
        exit();
    }
} else {
    // Check if service_id column exists
    $check_service_id = "SHOW COLUMNS FROM cart LIKE 'service_id'";
    $service_id_exists = $conn->query($check_service_id)->num_rows > 0;
    
    if (!$service_id_exists) {
        // Add service_id column if it doesn't exist
        $alter_table = "ALTER TABLE cart ADD COLUMN service_id INT NOT NULL AFTER user_id";
        if (!$conn->query($alter_table)) {
            error_log("Error adding service_id column: " . $conn->error);
            $_SESSION['error'] = "System error. Please try again later.";
            header('Location: services.php');
            exit();
        }
        
        // Add foreign key constraint
        $add_foreign_key = "ALTER TABLE cart ADD FOREIGN KEY (service_id) REFERENCES services(id)";
        if (!$conn->query($add_foreign_key)) {
            error_log("Error adding foreign key: " . $conn->error);
        }
    }
}

// Verify cart items have valid service IDs
$verify_cart = "SELECT c.*, s.id as service_id 
                FROM cart c 
                LEFT JOIN services s ON c.service_id = s.id 
                WHERE c.user_id = ? AND s.id IS NULL";
$stmt = $conn->prepare($verify_cart);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$invalid_items = $stmt->get_result()->num_rows;

if ($invalid_items > 0) {
    // Remove invalid items from cart
    $remove_invalid = "DELETE c FROM cart c 
                      LEFT JOIN services s ON c.service_id = s.id 
                      WHERE c.user_id = ? AND s.id IS NULL";
    $stmt = $conn->prepare($remove_invalid);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    $_SESSION['error'] = "Some items in your cart were invalid and have been removed.";
    header('Location: cart.php');
    exit();
}

// Check if bookings table exists, if not create it
$check_table = "SHOW TABLES LIKE 'bookings'";
$table_exists = $conn->query($check_table)->num_rows > 0;

if (!$table_exists) {
    $create_table = "CREATE TABLE bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        service_id INT,
        booking_date DATE NOT NULL,
        event_date DATE NOT NULL,
        guest_count INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        service_ids TEXT,
        service_quantities TEXT,
        service_prices TEXT,
        special_requests TEXT,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (service_id) REFERENCES services(id)
    )";
    
    if (!$conn->query($create_table)) {
        error_log("Error creating bookings table: " . $conn->error);
        $_SESSION['error'] = "System error. Please try again later.";
        header('Location: cart.php');
        exit();
    }
} else {
    // Check if necessary columns exist
    $required_columns = [
        'service_id' => 'INT',
        'service_ids' => 'TEXT',
        'service_quantities' => 'TEXT',
        'service_prices' => 'TEXT',
        'total_amount' => 'DECIMAL(10,2) NOT NULL',
        'booking_date' => 'DATE NOT NULL'
    ];
    
    foreach ($required_columns as $column => $definition) {
        $check_column = "SHOW COLUMNS FROM bookings LIKE '$column'";
        $column_exists = $conn->query($check_column)->num_rows > 0;
        
        if (!$column_exists) {
            $alter_table = "ALTER TABLE bookings ADD COLUMN $column $definition";
            if (!$conn->query($alter_table)) {
                error_log("Error adding $column column: " . $conn->error);
                $_SESSION['error'] = "System error. Please try again later.";
                header('Location: cart.php');
                exit();
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the booking
    if (isset($_POST['event_date']) && isset($_POST['guest_count'])) {
        $event_date = $_POST['event_date'];
        $guest_count = (int)$_POST['guest_count'];
        $special_requests = $_POST['special_requests'] ?? '';
        $booking_date = date('Y-m-d'); // Current date as booking date
        
        // Start transaction
        $conn->begin_transaction();
        try {
            // First, check if cart has items and get all cart items
            $check_cart = "SELECT c.*, s.id as service_id, s.name as service_name, s.price,
                           COUNT(*) as quantity 
                           FROM cart c 
                           JOIN services s ON c.service_id = s.id 
                           WHERE c.user_id = ?
                           GROUP BY s.id";
            $stmt = $conn->prepare($check_cart);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $cart_result = $stmt->get_result();
            
            if ($cart_result->num_rows === 0) {
                throw new Exception("Your cart is empty");
            }

            // Prepare arrays for comma-separated values
            $service_ids = [];
            $service_quantities = [];
            $service_prices = [];
            $total_amount = 0;
            $first_service_id = null;

            while ($item = $cart_result->fetch_assoc()) {
                if ($first_service_id === null) {
                    $first_service_id = $item['service_id'];
                }
                $service_ids[] = $item['service_id'];
                $service_quantities[] = $item['quantity'];
                $service_prices[] = $item['price'];
                $total_amount += ($item['price'] * $item['quantity']);
            }

            // Insert into bookings table with both single service_id and multiple service_ids
            $booking_query = "INSERT INTO bookings (
                user_id,
                service_id,
                booking_date,
                event_date, 
                guest_count, 
                status, 
                total_amount,
                service_ids,
                service_quantities,
                service_prices,
                special_requests
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($booking_query);
            if (!$stmt) {
                throw new Exception("Error preparing booking query: " . $conn->error);
            }
            
            // Convert arrays to comma-separated strings
            $service_ids_str = implode(',', $service_ids);
            $service_quantities_str = implode(',', $service_quantities);
            $service_prices_str = implode(',', $service_prices);
            
            // Log the values being bound
            error_log("Binding values for booking - user_id: " . $_SESSION['user_id'] . 
                     ", service_id: " . $first_service_id .
                     ", booking_date: " . $booking_date . 
                     ", event_date: " . $event_date . 
                     ", guest_count: " . $guest_count . 
                     ", total_amount: " . $total_amount . 
                     ", service_ids: " . $service_ids_str);
            
            $stmt->bind_param("iissiissss", 
                $_SESSION['user_id'],
                $first_service_id,
                $booking_date,
                $event_date,
                $guest_count,
                $total_amount,
                $service_ids_str,
                $service_quantities_str,
                $service_prices_str,
                $special_requests
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting booking: " . $stmt->error);
            }
            
            error_log("Successfully inserted booking with service IDs: " . $service_ids_str);

            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($clear_cart);
            if (!$stmt) {
                throw new Exception("Error preparing clear cart query: " . $conn->error);
            }
            
            $stmt->bind_param("i", $_SESSION['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error clearing cart: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['success'] = "Booking placed successfully!";
            header('Location: ../pages/account.php#bookings');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Booking Error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to place booking: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please provide all required information";
    }
}

// Fetch cart items
$cart_query = "SELECT c.*, s.name, s.description, s.price, s.image, sc.name as category_name 
               FROM cart c 
               JOIN services s ON c.service_id = s.id 
               JOIN service_categories sc ON s.category_id = sc.id 
               WHERE c.user_id = ? 
               ORDER BY c.created_at DESC";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_result = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['price'];
}

// Include header
include_once('../includes/header.php');
?>

<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="services.php">Browse our services</a> to add items.
        </div>
    <?php else: ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Event Details</h5>
                        <form id="checkoutForm" method="POST" action="">
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="guest_count" class="form-label">Number of Guests *</label>
                                <input type="number" class="form-control" id="guest_count" name="guest_count" 
                                       required min="1" max="1000">
                            </div>
                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" 
                                          rows="3" placeholder="Any special requirements or notes for your event"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                </div>
                                <span class="price-tag">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%)</span>
                            <span>$<?php echo number_format($total * 0.1, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong>$<?php echo number_format($total * 1.1, 2); ?></strong>
                        </div>
                        <button type="submit" form="checkoutForm" class="btn btn-primary w-100">
                            Place Order
                        </button>
                        <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                            Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for event_date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('event_date').min = tomorrow.toISOString().split('T')[0];

    // Form validation
    const form = document.getElementById('checkoutForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const eventDate = document.getElementById('event_date').value;
        const guestCount = document.getElementById('guest_count').value;

        if (!eventDate) {
            alert('Please select an event date');
            return;
        }

        if (!guestCount || guestCount < 1) {
            alert('Please enter a valid number of guests');
            return;
        }

        // If validation passes, submit the form
        this.submit();
    });
});
</script>

<?php
include_once('../includes/footer.php');
?> 