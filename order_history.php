<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_history');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get user's orders (PDO)
$orders_sql = "SELECT o.*, COUNT(oi.order_item_id) as item_count
               FROM orders o
               LEFT JOIN order_items oi ON o.order_id = oi.order_id
               WHERE o.user_id = :uid
               GROUP BY o.order_id
               ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($orders_sql);
$stmt->execute([':uid' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Jewelry Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .order-card {
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Order History</h2>
                    <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
                </div>
                
                <?php if (count($orders) > 0): ?>
                    <div class="row">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card order-card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($order['order_number']); ?></h6>
                                            <small class="text-muted"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                                        </div>
                                        <div>
                                            <?php
                                            $status_class = '';
                                            switch ($order['order_status']) {
                                                case 'pending':
                                                    $status_class = 'bg-warning text-dark';
                                                    break;
                                                case 'processing':
                                                    $status_class = 'bg-info text-dark';
                                                    break;
                                                case 'shipped':
                                                    $status_class = 'bg-primary';
                                                    break;
                                                case 'delivered':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'bg-danger';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Items:</small>
                                                <p class="mb-0"><?php echo $order['item_count']; ?> items</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Total:</small>
                                                <p class="mb-0 fw-bold">₱<?php echo number_format($order['total_amount'], 2); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Payment:</small>
                                                <p class="mb-0"><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Payment Status:</small>
                                                <?php
                                                $payment_status_class = '';
                                                switch ($order['payment_status']) {
                                                    case 'paid':
                                                        $payment_status_class = 'bg-success';
                                                        break;
                                                    case 'pending':
                                                        $payment_status_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'failed':
                                                        $payment_status_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $payment_status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <p class="mb-0">
                                                    <span class="badge status-badge <?php echo $payment_status_class; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="toggleOrderDetails(<?php echo $order['order_id']; ?>)">
                                                View Details
                                            </button>
                                            <?php if ($order['order_status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                                    Cancel Order
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Details (Hidden by default) -->
                            <div id="order-details-<?php echo $order['order_id']; ?>" class="col-12 mb-4" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Order Details - <?php echo htmlspecialchars($order['order_number']); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get order items for this order (PDO)
                                        $items_sql = "SELECT oi.*, p.product_name, p.product_image
                                                      FROM order_items oi
                                                      JOIN products p ON oi.product_id = p.product_id
                                                      WHERE oi.order_id = :oid";
                                        $stmt_items = $pdo->prepare($items_sql);
                                        $stmt_items->execute([':oid' => $order['order_id']]);
                                        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        
                                        <?php if (count($order_items) > 0): ?>
                                            <?php foreach ($order_items as $item): ?>
                                                <div class="row align-items-center mb-3 pb-3 border-bottom">
                                                    <div class="col-md-2">
                                                        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                             class="img-fluid rounded" style="max-height: 60px;">
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
                                        <?php endif; ?>
                                        
                                        <!-- Order Summary -->
                                        <div class="row mt-3">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-4">
                                                <div class="bg-light p-3 rounded">
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
                                        
                                        <?php if (!empty($order['shipping_address'])): ?>
                                        <div class="mt-3">
                                            <h6>Shipping Address:</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-box-seam" style="font-size: 4rem; color: #6c757d;"></i>
                        </div>
                        <h3>No Orders Yet</h3>
                        <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleOrderDetails(orderId) {
            const detailsDiv = document.getElementById('order-details-' + orderId);
            if (detailsDiv.style.display === 'none') {
                detailsDiv.style.display = 'block';
            } else {
                detailsDiv.style.display = 'none';
            }
        }
        
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order cancelled successfully.');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to cancel order.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the order.');
                });
            }
        }
    </script>
</body>
</html>