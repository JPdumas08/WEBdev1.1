<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get cart items (PDO)
$cart_sql = "SELECT ci.cart_item_id AS item_id, ci.cart_id, ci.product_id, ci.quantity, ci.price,
                    p.product_name, p.product_image
             FROM cart_items ci
             JOIN cart c ON ci.cart_id = c.cart_id
             JOIN products p ON ci.product_id = p.product_id
             WHERE c.user_id = :uid";

$stmt = $pdo->prepare($cart_sql);
$stmt->execute([':uid' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$shipping = 150;
foreach ($cart_items as $item) {
    $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
}
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Jewelry Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2 class="mb-4">Checkout</h2>
                
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkoutForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Province</label>
                                    <select class="form-select" id="province" name="province" required>
                                        <option value="">Select Province</option>
                                        <option value="Metro Manila">Metro Manila</option>
                                        <option value="Cavite">Cavite</option>
                                        <option value="Laguna">Laguna</option>
                                        <option value="Batangas">Batangas</option>
                                        <option value="Rizal">Rizal</option>
                                        <option value="Quezon">Quezon</option>
                                        <option value="Bulacan">Bulacan</option>
                                        <option value="Pampanga">Pampanga</option>
                                        <option value="Tarlac">Tarlac</option>
                                        <option value="Zambales">Zambales</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="postalCode" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postalCode" name="postalCode" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="orderNotes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="orderNotes" name="orderNotes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod" checked>
                            <label class="form-check-label" for="cod">
                                Cash on Delivery (COD)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="gcash" value="gcash">
                            <label class="form-check-label" for="gcash">
                                GCash
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="paypal" value="paypal">
                            <label class="form-check-label" for="paypal">
                                PayPal
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($cart_items) > 0): ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" 
                                             class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?php echo $item['product_name']; ?></h6>
                                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                        </div>
                                    </div>
                                    <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>₱<?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <button type="submit" form="checkoutForm" class="btn btn-primary w-100 mt-3">
                                Place Order
                            </button>
                            
                            <div class="text-center mt-3">
                                <a href="cart.php" class="text-decoration-none">
                                    <small>← Back to Cart</small>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Your cart is empty.</p>
                            <div class="text-center">
                                <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            
            // Add payment method to form data
            formData.append('paymentMethod', paymentMethod);
            
            // Send to process_checkout.php
            fetch('process_checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to order confirmation
                    window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
                } else {
                    // Show error message
                    alert(data.message || 'An error occurred while processing your order.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your order.');
            });
        });
    </script>
</body>
</html>