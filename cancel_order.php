<?php
require_once 'includes/auth.php';
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit();
}

// Check if order belongs to user and is in pending status
$check_query = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ? AND order_status = 'pending'";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled.']);
    exit();
}

// Update order status
$conn->begin_transaction();
try {
    $update_query = "UPDATE orders SET order_status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE order_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    // Update payment status
    $update_payment_query = "UPDATE payments SET status = 'refunded' WHERE order_id = ?";
    $stmt = $conn->prepare($update_payment_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Cancel order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order. Please try again.']);
}
?>