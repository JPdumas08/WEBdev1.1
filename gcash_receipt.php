<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$transaction_id = isset($_GET['transaction_id']) ? trim($_GET['transaction_id']) : '';
$user_email = isset($_GET['email']) ? trim($_GET['email']) : '';

// Validate order exists and belongs to user
if ($order_id <= 0 || empty($transaction_id)) {
    header('Location: order_history.php');
    exit();
}

$order_sql = "SELECT o.*, 
                     COUNT(oi.order_item_id) as item_count
              FROM orders o
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              WHERE o.order_id = :oid AND o.user_id = :uid
              GROUP BY o.order_id";
$order_stmt = $pdo->prepare($order_sql);
$order_stmt->execute([':oid' => $order_id, ':uid' => $user_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: order_history.php');
    exit();
}

// Get order items for receipt
$items_sql = "SELECT oi.*, p.product_name 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = :oid";
$items_stmt = $pdo->prepare($items_sql);
$items_stmt->execute([':oid' => $order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$user_sql = "SELECT first_name, last_name, email_address FROM users WHERE user_id = :uid";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([':uid' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Use email from parameter, fallback to user table
if (!$user_email && $user) {
    $user_email = $user['email_address'] ?? '';
}

// Provide defaults if user data not found
if (!$user) {
    $user = [
        'first_name' => 'Customer',
        'last_name' => '',
        'email_address' => $user_email
    ];
}

$payment_date = date('F d, Y h:i A');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCash Payment Receipt - Jeweluxe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .receipt-card { box-shadow: none; border: 1px solid #ddd; }
        }
        .receipt-card {
            border: 2px solid #28a745;
        }
        .success-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            padding: 2rem;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Back Button -->
                <div class="mb-4 no-print">
                    <button class="btn btn-outline-secondary" onclick="window.history.back();" type="button">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>

                <!-- Receipt Card -->
                <div class="card receipt-card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <!-- Success Message -->
                        <div class="text-center mb-4">
                            <div class="success-badge">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="text-success mb-2">Payment Successful!</h2>
                            <p class="text-muted mb-0">Your GCash payment has been confirmed</p>
                        </div>

                        <hr class="my-4">

                        <!-- Receipt Details -->
                        <div class="mb-4">
                            <h5 class="mb-3">Payment Receipt</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Transaction ID:</td>
                                    <td class="fw-bold text-end"><?php echo htmlspecialchars($transaction_id); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Order Number:</td>
                                    <td class="fw-bold text-end"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Order ID:</td>
                                    <td class="fw-bold text-end">#<?php echo $order_id; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Date:</td>
                                    <td class="fw-bold text-end"><?php echo $payment_date; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Method:</td>
                                    <td class="fw-bold text-end"><span class="badge bg-primary">GCash</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Status:</td>
                                    <td class="fw-bold text-end"><span class="badge bg-success">PAID</span></td>
                                </tr>
                            </table>
                        </div>

                        <hr class="my-4">

                        <!-- Customer Information -->
                        <div class="mb-4">
                            <h5 class="mb-3">Customer Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Name:</td>
                                    <td class="fw-bold text-end"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td class="fw-bold text-end"><?php echo htmlspecialchars($user_email ?: ($user['email_address'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipping Address:</td>
                                    <td class="text-end" style="font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></td>
                                </tr>
                            </table>
                        </div>

                        <hr class="my-4">

                        <!-- Order Items -->
                        <div class="mb-4">
                            <h5 class="mb-3">Order Items (<?php echo count($items); ?> item<?php echo count($items) !== 1 ? 's' : ''; ?>)</h5>
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td class="text-end">₱<?php echo number_format($item['total_price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <!-- Amount Summary -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($order['subtotal'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping Fee:</span>
                                <span>₱<?php echo number_format($order['shipping_cost'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>₱<?php echo number_format($order['tax'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-3">
                                <strong>Total Amount Paid:</strong>
                                <strong class="text-success" style="font-size: 1.3rem;">₱<?php echo number_format($order['total_amount'], 2); ?></strong>
                            </div>
                        </div>

                        <!-- GCash Transfer Confirmation -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle"></i> Transfer Confirmation
                            </h6>
                            <p class="small mb-0">
                                <strong>From:</strong> Your GCash Account<br>
                                <strong>To:</strong> Jeweluxe Store (+63 917 123 4567)<br>
                                <strong>Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?><br>
                                <strong>Reference:</strong> <?php echo htmlspecialchars($order['order_number']); ?>
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 no-print mb-3">
                            <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                            <a href="order_history.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-list"></i> View Order History
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-lg" id="cancelOrderBtn">
                                <i class="fas fa-times-circle"></i> Cancel Order
                            </button>
                        </div>

                        <!-- Footer Message -->
                        <div class="alert alert-success">
                            <small>
                                Thank you for your purchase! Your order is being processed and will be shipped soon. 
                                You will receive a tracking number via email once your order ships.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Message -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-envelope"></i> <strong>Confirmation email sent to:</strong> <?php echo htmlspecialchars($user['email_address'] ?? 'your email'); ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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

        // Auto-print on first visit (optional, commented out)
        // window.print();
    </script>
</body>
</html>
