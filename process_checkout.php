<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Ensure session is active
init_session();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    error_log('Checkout failed: No user_id in session. Session data: ' . json_encode($_SESSION));
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Check if addressId is provided (required for saved address checkout)
if (!isset($_POST['addressId']) || empty($_POST['addressId'])) {
    echo json_encode(['success' => false, 'message' => 'Please select a shipping address.']);
    exit();
}

$address_id = (int)$_POST['addressId'];
$paymentMethod = isset($_POST['paymentMethod']) ? trim($_POST['paymentMethod']) : '';
$orderNotes = isset($_POST['orderNotes']) ? trim($_POST['orderNotes']) : '';

// Validate payment method
if (empty($paymentMethod)) {
    echo json_encode(['success' => false, 'message' => 'Please select a payment method.']);
    exit();
}

// Fetch the selected address
$address_sql = "SELECT * FROM addresses WHERE address_id = :aid AND user_id = :uid";
$address_stmt = $pdo->prepare($address_sql);
$address_stmt->execute([':aid' => $address_id, ':uid' => $user_id]);
$address = $address_stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    echo json_encode(['success' => false, 'message' => 'Invalid address selected. Please select a valid shipping address.']);
    exit();
}

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

if (count($cart_items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit();
}

// Calculate order totals
$subtotal = 0;
$shipping = 150;
$tax = 0;

foreach ($cart_items as $item) {
    $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
}
$total = $subtotal + $shipping + $tax;

// Build shipping address string from saved address
$shipping_address = sprintf(
    "%s\n%s\n%s, %s %s\n%s",
    $address['full_name'],
    $address['address_line1'] . ($address['address_line2'] ? ', ' . $address['address_line2'] : ''),
    $address['city'],
    $address['state'],
    $address['postal_code'],
    $address['phone']
);

// Begin transaction
$pdo->beginTransaction();

try {
    // Generate unique order number
    $order_number = 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Set payment status based on payment method
    // COD: pending, GCash: pending (user needs to send payment)
    $payment_status = 'pending';

    // Insert order (align with schema columns that exist in WEBDEV-MAIN.sql)
    $order_sql = "INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, tax, total_amount, payment_method, payment_status, shipping_address, billing_address)
                  VALUES (:uid, :order_number, :subtotal, :shipping, :tax, :total, :payment_method, :payment_status, :shipping_address, :billing_address)";

    $order_stmt = $pdo->prepare($order_sql);
    $order_stmt->execute([
        ':uid' => $user_id,
        ':order_number' => $order_number,
        ':subtotal' => $subtotal,
        ':shipping' => $shipping,
        ':tax' => $tax,
        ':total' => $total,
        ':payment_method' => $paymentMethod,
        ':payment_status' => $payment_status,
        ':shipping_address' => $shipping_address,
        ':billing_address' => $shipping_address
    ]);

    $order_id = (int)$pdo->lastInsertId();

    // Insert order items
    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)";
    $item_stmt = $pdo->prepare($item_sql);

    foreach ($cart_items as $item) {
        $item_total = ((float)$item['price']) * ((int)$item['quantity']);
        $item_stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['price'],
            ':total_price' => $item_total
        ]);
    }

    // Insert payment record
    $payment_sql = "INSERT INTO payments (order_id, payment_method, amount, status)
                    VALUES (:order_id, :payment_method, :amount, :status)";
    $pay_stmt = $pdo->prepare($payment_sql);
    $pay_stmt->execute([
        ':order_id' => $order_id,
        ':payment_method' => $paymentMethod,
        ':amount' => $total,
        ':status' => 'pending'
    ]);

    // Clear cart items for this user
    $clear_sql = "DELETE ci FROM cart_items ci
                  JOIN cart c ON c.cart_id = ci.cart_id
                  WHERE c.user_id = :uid";
    $clear_stmt = $pdo->prepare($clear_sql);
    $clear_stmt->execute([':uid' => $user_id]);

    $pdo->commit();

    // Determine redirect based on payment method
    $redirect_url = 'order_confirmation.php?order_id=' . $order_id;
    if ($paymentMethod === 'gcash') {
        $redirect_url = 'gcash_payment.php?order_id=' . $order_id;
    }

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'payment_method' => $paymentMethod,
        'redirect_url' => $redirect_url,
        'message' => 'Order placed successfully!'
    ]);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Checkout error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your order. Please try again.']);
    exit();
}
?>