<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_history');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get user's name and email from database
$user_sql = "SELECT first_name, last_name, email_address FROM users WHERE user_id = :uid";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([':uid' => $user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

$user_name = $user_data ? ($user_data['first_name'] . ' ' . $user_data['last_name']) : ($_SESSION['user_name'] ?? 'Valued Customer');
$user_email = $user_data ? $user_data['email_address'] : ($_SESSION['user_email'] ?? '');

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

// Prepared statement for quick product preview per order
$item_preview_stmt = $pdo->prepare(
    "SELECT oi.quantity, oi.unit_price, p.product_name, p.product_image
     FROM order_items oi
     JOIN products p ON oi.product_id = p.product_id
     WHERE oi.order_id = :oid
     ORDER BY oi.order_item_id ASC
     LIMIT 1"
);
?>

<?php
$pageTitle = 'Order History - Jeweluxe';
include 'includes/header.php';
?>
<link rel="stylesheet" href="styles.css">
<body class="order-history-page">

    <section class="orders-hero">
        <div class="container-xl">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light btn-sm" onclick="window.history.back();" type="button" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1 class="mb-0 text-white">Order History</h1>
            </div>
        </div>
    </section>

    <div class="orders-wrapper py-5">
        <div class="container-xl">
            <div class="row g-4">
                <aside class="col-lg-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <div>
                                <div class="text-muted small">Hello,</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($user_name); ?></div>
                                <?php if (!empty($user_email)): ?>
                                    <div class="small text-muted"><?php echo htmlspecialchars($user_email); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="list-group list-group-flush orders-nav">
                            <a class="list-group-item" href="account_settings.php">Personal Information</a>
                            <a class="list-group-item" href="address.php">Address</a>
                            <a class="list-group-item" href="wishlist.php">Wishlist</a>
                            <a class="list-group-item active" aria-current="true" href="order_history.php">My Orders</a>
                            <a class="list-group-item" href="notifications.php">Notifications</a>
                            <a class="list-group-item" href="account_settings.php">Change Password</a>
                            <a class="list-group-item" href="logout.php">Logout</a>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 need-help">
                        <div class="card-body text-center">
                            <div class="fs-4 mb-2">Need Help?</div>
                            <p class="text-muted small mb-3">Have questions or concerns regarding your account? Connect with our support team.</p>
                            <a href="contactus.php" class="btn btn-outline-primary w-100">Contact Support</a>
                        </div>
                    </div>
                </aside>

                <main class="col-lg-9">
                    <div class="card shadow-sm border-0 rounded-4 orders-panel">
                        <div class="card-body pb-0">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="small text-muted mb-1">Order Center</div>
                                    <h4 class="mb-0">My Orders</h4>
                                </div>
                                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                            </div>

                            <ul class="nav nav-pills orders-filter mb-3" id="ordersFilter" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-filter="all" type="button">All</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-filter="shipped" type="button">Shipped</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-filter="delivered" type="button">Delivered</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-filter="cancelled" type="button">Cancelled</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-filter="returned" type="button">Returned</button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body pt-0">
                            <?php if (count($orders) > 0): ?>
                                <div class="orders-list">
                                    <?php foreach ($orders as $order): ?>
                                        <?php
                                        $item_preview_stmt->execute([':oid' => $order['order_id']]);
                                        $preview = $item_preview_stmt->fetch(PDO::FETCH_ASSOC) ?: null;

                                        $status_key = strtolower((string) $order['order_status']);
                                        $status_labels = [
                                            'pending' => ['Pending', 'pill pending'],
                                            'processing' => ['Processing', 'pill processing'],
                                            'shipped' => ['Shipped', 'pill shipped'],
                                            'delivered' => ['Delivered', 'pill delivered'],
                                            'cancelled' => ['Cancelled', 'pill cancelled'],
                                            'returned' => ['Returned', 'pill returned'],
                                        ];
                                        $status_label = $status_labels[$status_key][0] ?? ucfirst($status_key ?: 'Unknown');
                                        $status_class = $status_labels[$status_key][1] ?? 'pill default';

                                        $payment_key = strtolower((string) $order['payment_status']);
                                        $payment_map = [
                                            'paid' => ['Paid', 'pill paid'],
                                            'pending' => ['Pending', 'pill pending'],
                                            'failed' => ['Failed', 'pill cancelled'],
                                        ];
                                        $payment_label = $payment_map[$payment_key][0] ?? ucfirst($payment_key ?: 'Unknown');
                                        $payment_class = $payment_map[$payment_key][1] ?? 'pill default';
                                        ?>

                                        <div class="order-card-ui" data-status="<?php echo $status_key; ?>">
                                            <div class="order-card-top d-flex flex-wrap justify-content-between align-items-start">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status_label); ?></span>
                                                    <div class="small text-muted">Order No: <?php echo htmlspecialchars($order['order_number']); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small text-muted">Total</div>
                                                    <div class="fw-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                                                </div>
                                            </div>
                                            <div class="order-body d-flex gap-3 align-items-center">
                                                <div class="order-thumb rounded-3 overflow-hidden bg-light">
                                                    <?php if ($preview && !empty($preview['product_image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($preview['product_image']); ?>" alt="<?php echo htmlspecialchars($preview['product_name']); ?>">
                                                    <?php else: ?>
                                                        <div class="thumb-placeholder">No Image</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold mb-1">
                                                        <?php echo $preview ? htmlspecialchars($preview['product_name']) : 'Items in this order'; ?>
                                                    </div>
                                                    <div class="text-muted small mb-1">
                                                        <?php if ($preview): ?>
                                                            ₱<?php echo number_format($preview['unit_price'], 2); ?> × <?php echo (int) $preview['quantity']; ?>
                                                        <?php else: ?>
                                                            <?php echo (int) $order['item_count']; ?> item(s)
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        <span class="status-pill <?php echo $payment_class; ?>"><?php echo htmlspecialchars($payment_label); ?></span>
                                                        <span class="text-muted small">Payment: <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-2">
                                                    <?php if ($status_key === 'pending'): ?>
                                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['order_id']; ?>)">Cancel</button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-primary btn-sm" onclick="toggleOrderDetails(<?php echo $order['order_id']; ?>)">Order Details</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="order-details-<?php echo $order['order_id']; ?>" class="order-details-card" style="display: none;">
                                            <div class="card border-0 shadow-sm rounded-4">
                                                <div class="card-body">
                                                    <?php
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
                                                                <div class="col-md-2 col-3">
                                                                    <div class="order-thumb rounded-3 overflow-hidden bg-light">
                                                                        <?php if (!empty($item['product_image'])): ?>
                                                                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                                        <?php else: ?>
                                                                            <div class="thumb-placeholder">No Image</div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 col-9">
                                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                                    <p class="text-muted mb-0">Quantity: <?php echo (int) $item['quantity']; ?></p>
                                                                </div>
                                                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                                                    <p class="mb-0">₱<?php echo number_format($item['unit_price'], 2); ?> each</p>
                                                                    <strong>₱<?php echo number_format($item['total_price'], 2); ?></strong>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <?php if (!empty($order['shipping_address'])): ?>
                                                                <div class="p-3 rounded-3 bg-light">
                                                                    <div class="fw-semibold mb-1">Shipping Address</div>
                                                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="p-3 rounded-3 bg-light">
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
                                                                <hr class="my-2">
                                                                <div class="d-flex justify-content-between fw-semibold">
                                                                    <span>Total:</span>
                                                                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="empty-icon mb-3">📦</div>
                                    <h4 class="mb-1">No Orders Yet</h4>
                                    <p class="text-muted mb-3">You have not placed any orders yet. Start shopping to see them here.</p>
                                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function toggleOrderDetails(orderId) {
            const detailsDiv = document.getElementById('order-details-' + orderId);
            if (!detailsDiv) return;
            const isHidden = detailsDiv.style.display === 'none' || detailsDiv.style.display === '';
            detailsDiv.style.display = isHidden ? 'block' : 'none';
        }

        function cancelOrder(orderId) {
            ConfirmModal.show(
                '⚠️ Cancel Order',
                'Are you sure you want to cancel this order?',
                function() {
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ order_id: orderId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            ToastNotification.success('Order cancelled successfully.');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            ToastNotification.error(data.message || 'Failed to cancel order.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ToastNotification.error('An error occurred while cancelling the order.');
                    });
                }
            );
        }

        (function registerFilters() {
            const buttons = document.querySelectorAll('#ordersFilter .nav-link');
            const cards = document.querySelectorAll('.order-card-ui');
            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    buttons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const filter = btn.dataset.filter;
                    cards.forEach(card => {
                        if (!filter || filter === 'all') {
                            card.style.display = '';
                            return;
                        }
                        const match = card.dataset.status === filter;
                        card.style.display = match ? '' : 'none';
                    });
                });
            });
        })();
    </script>