<?php
// Adds a product to the logged-in user's cart
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Enable verbose error reporting during debugging (remove or lower in production)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Simple logger helper
function cart_log($msg) {
    $f = __DIR__ . '/cart_debug.log';
    @file_put_contents($f, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

// Convert PHP errors to exceptions so they get caught and logged
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
    cart_log('Uncaught exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
    exit;
});

// Log request for debugging
cart_log('Request: ' . $_SERVER['REQUEST_METHOD'] . ' ' . ($_SERVER['REQUEST_URI'] ?? '') . ' POST:' . json_encode($_POST));

// Validate user session
$user_id = require_login_json();

header('Content-Type: application/json');

// Accept both form-encoded and JSON payloads
$input = $_POST;
if (empty($input) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
}

$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'invalid_input']);
    exit;
}

try {
    // Start transaction to keep operations consistent
    $pdo->beginTransaction();

    // Ensure cart exists for user; create if needed
    $stmt = $pdo->prepare('SELECT cart_id FROM cart WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $user_id]);
    $cart = $stmt->fetch();
    if ($cart) {
        $cart_id = (int)$cart['cart_id'];
    } else {
        $ins = $pdo->prepare('INSERT INTO cart (user_id, created_at) VALUES (:user_id, NOW())');
        $ins->execute([':user_id' => $user_id]);
        $cart_id = (int)$pdo->lastInsertId();
    }

    // Fetch product and available stock. Use actual columns from schema.
    $pstmt = $pdo->prepare('SELECT product_id, product_name AS name, product_price AS price, NULL AS stock FROM products WHERE product_id = :pid LIMIT 1');
    $pstmt->execute([':pid' => $product_id]);
    $product = $pstmt->fetch();
    if (!$product) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'product_not_found']);
        exit;
    }

    // If there's no stock column, treat available as effectively unlimited
    $available = isset($product['stock']) && $product['stock'] !== null ? (int)$product['stock'] : PHP_INT_MAX;

    // Check existing item (use cart_items table and cart_item_id PK)
    $cstmt = $pdo->prepare('SELECT cart_item_id AS item_id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
    $cstmt->execute([':cart_id' => $cart_id, ':product_id' => $product_id]);
    $item = $cstmt->fetch();

    if ($item) {
        // increase quantity but do not exceed stock
        $newQty = $item['quantity'] + $quantity;
        if ($newQty > $available) { $newQty = $available; }
    $ustmt = $pdo->prepare('UPDATE cart_items SET quantity = :quantity WHERE cart_item_id = :item_id');
    $ustmt->execute([':quantity' => $newQty, ':item_id' => $item['item_id']]);
        $resultQty = $newQty;
        $item_id = (int)$item['item_id'];
    } else {
        // insert new item with quantity limited by stock
        $useQty = $quantity > $available ? $available : $quantity;
    // insert price snapshot into cart_items (schema requires price NOT NULL)
    $istmt = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, :price)');
    $istmt->execute([':cart_id' => $cart_id, ':product_id' => $product_id, ':quantity' => $useQty, ':price' => $product['price']]);
    $item_id = (int)$pdo->lastInsertId();
        $resultQty = $useQty;
    }

    $pdo->commit();

    // Return item details
    echo json_encode([
        'success' => true,
        'cart_id' => $cart_id,
        'item' => [
            'item_id' => $item_id,
            'product_id' => $product['product_id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => (int)$resultQty,
            'stock' => $available
        ]
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
    exit;
}


?>
