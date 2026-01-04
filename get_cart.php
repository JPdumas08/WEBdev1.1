<?php
// Returns the current user's cart items with product name, price and quantity
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$user_id = require_login_json();

try {
    // Find the user's cart
    $stmt = $pdo->prepare('SELECT cart_id FROM cart WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $user_id]);
    $cart = $stmt->fetch();

    if (!$cart) {
        echo json_encode(['success' => true, 'cart' => ['cart_id' => null, 'items' => [], 'total' => 0.0]]);
        exit;
    }

    $cart_id = (int)$cart['cart_id'];

    // Join cart_items and products for details. Use price from cart_items.
    // Products table uses product_name and product_price
    $q = "SELECT ci.cart_item_id AS item_id, ci.product_id, ci.quantity, p.product_name AS name, p.product_image AS image, ci.price
        FROM cart_items ci
        JOIN products p ON p.product_id = ci.product_id
        WHERE ci.cart_id = :cart_id";
    $cstmt = $pdo->prepare($q);
    $cstmt->execute([':cart_id' => $cart_id]);
    $items = $cstmt->fetchAll();

    $total = 0.0;
    $out = [];
    foreach ($items as $it) {
        $lineTotal = ((float)$it['price']) * ((int)$it['quantity']);
        $total += $lineTotal;
        $out[] = [
            'item_id' => (int)$it['item_id'],
            'product_id' => (int)$it['product_id'],
            'name' => $it['name'],
            'price' => (float)$it['price'],
            'image' => $it['image'],
            'quantity' => (int)$it['quantity'],
            'line_total' => $lineTotal
        ];
    }

    echo json_encode(['success' => true, 'cart' => ['cart_id' => $cart_id, 'items' => $out, 'total' => $total]]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
    exit;
}

?>
