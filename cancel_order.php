<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Ensure session is active
init_session();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

// Get POST data (handle both JSON and form data)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Fallback to $_POST if JSON parsing fails
if (!$data && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
}

$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit();
}

// Check if order belongs to user and can be cancelled
$check_sql = "SELECT order_id, order_status, payment_status FROM orders 
              WHERE order_id = :oid AND user_id = :uid";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([':oid' => $order_id, ':uid' => $user_id]);
$order = $check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit();
}

// Check if order can be cancelled (pending or processing status)
$cancelable_statuses = ['pending', 'processing'];
if (!in_array($order['order_status'] ?? 'pending', $cancelable_statuses)) {
    echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled. It may have already shipped or been delivered.']);
    exit();
}

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Update order status to cancelled
    $update_sql = "UPDATE orders SET order_status = 'cancelled' WHERE order_id = :oid AND user_id = :uid";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([':oid' => $order_id, ':uid' => $user_id]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Cancel order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order. Please try again.']);
}
?>