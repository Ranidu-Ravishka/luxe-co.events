<?php
session_start();
require_once('../includes/config.php');

// For AJAX requests, check login status differently
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
        exit();
    }
}
// For regular page loads, redirect to login page
else if (!isset($_SESSION['user_id'])) {
    header('Location: ../includes/login & registration.php');
    exit();
}

// Handle POST requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Handle add to cart
    if (isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['service_id'])) {
        $service_id = (int)$_POST['service_id'];
        
        // Validate service exists and is active
        $service_query = "SELECT * FROM services WHERE id = ? AND status = 'active'";
        $stmt = $conn->prepare($service_query);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $service_result = $stmt->get_result();

        if ($service_result->num_rows === 0) {
            $response['message'] = 'Service not found or inactive';
        } else {
            // Check if service is already in cart
            $check_query = "SELECT id FROM cart WHERE user_id = ? AND service_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $_SESSION['user_id'], $service_id);
            $stmt->execute();
            $check_result = $stmt->get_result();

            if ($check_result->num_rows > 0) {
                $response['message'] = 'Service already in cart';
            } else {
                try {
                    // Simplified INSERT query with only required fields
                    $insert_query = "INSERT INTO cart (user_id, service_id, event_date, created_at) VALUES (?, ?, NOW(), NOW())";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("ii", $_SESSION['user_id'], $service_id);

                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Service added to cart successfully';
                    } else {
                        $response['message'] = 'Database error: ' . $stmt->error;
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Error: ' . $e->getMessage();
                }
            }
        }
    }
    
    // Handle remove from cart
    if (isset($_POST['action']) && $_POST['action'] === 'remove' && isset($_POST['cart_item_id'])) {
        $cart_item_id = (int)$_POST['cart_item_id'];
        
        // Verify the cart item belongs to the user
        $check_query = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $cart_item_id, $_SESSION['user_id']);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            $response['message'] = 'Cart item not found or unauthorized';
        } else {
            // Remove the item from cart
            $delete_query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $cart_item_id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item removed from cart successfully';
            } else {
                $response['message'] = 'Failed to remove item from cart';
            }
        }
    }
    
    // If it's an AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Fetch cart items with full image path
$cart_query = "SELECT c.*, s.name as service_name, s.price, s.image, s.description 
               FROM cart c 
               JOIN services s ON c.service_id = s.id 
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'];
}

// Include header
include_once('../includes/header.php');
?>

<div class="cart-wrapper">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0">Shopping Cart</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($cart_items)): ?>
                            <div class="text-center py-5">
                                <img src="../assets/images/empty-cart.png" alt="Empty Cart" class="mb-4" style="max-width: 200px; opacity: 0.5;">
                                <h5 class="text-muted mb-4">Your cart is empty</h5>
                                <a href="services.php" class="btn btn-primary px-4">Browse Services</a>
                            </div>
                        <?php else: ?>
                            <div class="cart-items">
                                <?php 
                                $total_items = count($cart_items);
                                foreach ($cart_items as $index => $item): 
                                ?>
                                    <div class="cart-item mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-3 mb-3 mb-md-0">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['service_name']); ?>" 
                                                         class="img-fluid rounded" 
                                                         style="object-fit: cover; height: 120px; width: 100%;">
                                                <?php else: ?>
                                                    <img src="../assets/images/default-service.jpg" 
                                                         alt="Default service image" 
                                                         class="img-fluid rounded" 
                                                         style="object-fit: cover; height: 120px; width: 100%;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <h5 class="mb-2"><?php echo $item['service_name']; ?></h5>
                                                <p class="text-muted mb-2"><?php echo substr($item['description'], 0, 100); ?>...</p>
                                                <h6 class="mb-0 text-primary">$<?php echo number_format($item['price'], 2); ?></h6>
                                            </div>
                                            <div class="col-md-3 text-md-end">
                                                <button class="btn btn-outline-danger remove-item" data-item-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($index < $total_items - 1): ?>
                                        <hr class="my-4">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
        </div>
                </div>
            </div>
            
            <?php if (!empty($cart_items)): ?>
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                                <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                            <hr class="my-3">
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                                <strong class="text-primary">$<?php echo number_format($total_amount, 2); ?></strong>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100 py-3">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.cart-wrapper {
    min-height: 100vh;
    padding: 80px 0;
    background-color: #f8f9fa;
}

.container {
    max-width: 1140px;
    margin: 0 auto;
}

.card {
    border: none;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08) !important;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background-color: #ff4081;
    border-color: #ff4081;
    font-weight: 600;
    font-size: 1.1rem;
    border-radius: 8px;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #f50057;
    border-color: #f50057;
    transform: translateY(-1px);
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
    padding: 8px 16px;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.text-primary {
    color: #ff4081 !important;
}

img.img-fluid {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .cart-wrapper {
        padding: 20px 0;
    }
    
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card {
        border-radius: 10px;
        margin: 0;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .cart-item {
        padding: 15px 0;
    }
    
    .col-md-3 img {
        height: 100px !important;
    }
}
</style>

<script>
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=remove&cart_item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to remove item. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
});
</script>

<?php
// Include footer
include_once('../includes/footer.php');
?> 