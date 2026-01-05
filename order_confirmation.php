<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Ensure session is active and user is logged in
if (empty($_SESSION['user_id'])) {
    $redir = 'login.php?redirect=order_confirmation&order_id=' . urlencode($_GET['order_id'] ?? '');
    header('Location: ' . $redir);
    exit();
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$user_id = (int) $_SESSION['user_id'];

if ($order_id === 0) {
    header('Location: products.php');
    exit();
}

// Fetch order details with PDO (matches schema in WEBDEV-MAIN.sql)
$order_sql = "SELECT o.*, u.first_name, u.last_name, u.email_address
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              WHERE o.order_id = :oid AND o.user_id = :uid";
$order_stmt = $pdo->prepare($order_sql);
$order_stmt->execute([':oid' => $order_id, ':uid' => $user_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found.</div></div>";
    exit();
}

// Fetch order items
$items_sql = "SELECT oi.*, p.product_name, p.product_image
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = :oid";
$items_stmt = $pdo->prepare($items_sql);
$items_stmt->execute([':oid' => $order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Jewelry Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .success-icon {
            font-size: 4rem;
            color: #28a745;
        }
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Success Message -->
                <div class="text-center mb-5">
                    <div class="success-icon mb-3">
                        ✓
                    </div>
                    <h1 class="mb-3">Thank You for Your Order!</h1>
                    <p class="lead">Your order has been successfully placed and will be processed soon.</p>
                </div>
                
                <!-- Order Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Order Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></p>
                                <p><strong>Order Status:</strong> 
                                    <span class="badge bg-warning text-dark"><?php echo ucfirst(htmlspecialchars($order['order_status'])); ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email_address']); ?></p>
                                <p><strong>Payment Status:</strong> 
                                    <span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['shipping_address'])): ?>
                        <div class="mt-3">
                            <h6>Shipping Address:</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['order_notes'])): ?>
                        <div class="mt-3">
                            <h6>Order Notes:</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['order_notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Order Items</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order_items as $item): ?>
                        <div class="row align-items-center mb-3 pb-3 border-bottom">
                            <div class="col-md-2">
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     class="img-fluid rounded" style="max-height: 80px;">
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                <p class="text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p class="mb-0">₱<?php echo number_format($item['unit_price'], 2); ?> each</p>
                                <strong>₱<?php echo number_format($item['total_price'], 2); ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Order Summary -->
                        <div class="row mt-4">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="order-summary p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>₱<?php echo number_format($order['subtotal'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping:</span>
                                        <span>₱<?php echo number_format($order['shipping_cost'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax:</span>
                                        <span>₱<?php echo number_format($order['tax'], 2); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Next Steps -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">What's Next?</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 50px; height: 50px;">1</div>
                                    <h6>Order Processing</h6>
                                    <p class="small text-muted">We'll review and process your order within 1-2 business days.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 50px; height: 50px;">2</div>
                                    <h6>Shipping</h6>
                                    <p class="small text-muted">Your order will be shipped and you'll receive tracking information.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 50px; height: 50px;">3</div>
                                    <h6>Delivery</h6>
                                    <p class="small text-muted">Estimated delivery: 3-7 business days after shipping.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center mb-5">
                    <a href="products.php" class="btn btn-outline-primary me-2">Continue Shopping</a>
                    <a href="order_history.php" class="btn btn-primary me-2">View Order History</a>
                    <button type="button" class="btn btn-outline-danger" id="cancelOrderBtn">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle cancel order functionality
        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.getElementById('cancelOrderBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    ConfirmModal.show(
                        '⚠️ Cancel Order',
                        'Are you sure you want to cancel this order? This action cannot be undone.',
                        function() {
                            // Get order_id from URL
                            const urlParams = new URLSearchParams(window.location.search);
                            const orderId = urlParams.get('order_id');
                            
                            if (!orderId) {
                                ToastNotification.error('Order ID not found.');
                                return;
                            }
                            
                            // Disable button during request
                            cancelBtn.disabled = true;
                            cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';
                            
                            // Send cancel request
                            fetch('cancel_order.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ order_id: parseInt(orderId) })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    ToastNotification.success('Order cancelled successfully.');
                                    setTimeout(() => {
                                        window.location.href = 'order_history.php';
                                    }, 1500);
                                } else {
                                    ToastNotification.error(data.message || 'Failed to cancel order.');
                                    cancelBtn.disabled = false;
                                    cancelBtn.innerHTML = '<i class="fas fa-times-circle"></i> Cancel Order';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                ToastNotification.error('An error occurred while cancelling the order.');
                                cancelBtn.disabled = false;
                                cancelBtn.innerHTML = '<i class="fas fa-times-circle"></i> Cancel Order';
                            });
                        }
                    );
                });
            }
        });
    </script>
</body>
</html>