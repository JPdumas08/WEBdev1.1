<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add to wishlist.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit();
}

// Check if product already in wishlist
$check_sql = "SELECT wishlist_id FROM wishlist WHERE user_id = :uid AND product_id = :pid";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([':uid' => $user_id, ':pid' => $product_id]);
$exists = $check_stmt->fetch();

if ($exists) {
    // Remove from wishlist
    $delete_sql = "DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid";
    $pdo->prepare($delete_sql)->execute([':uid' => $user_id, ':pid' => $product_id]);
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist.', 'action' => 'removed']);
} else {
    // Add to wishlist
    $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (:uid, :pid)";
    try {
        $pdo->prepare($insert_sql)->execute([':uid' => $user_id, ':pid' => $product_id]);
        echo json_encode(['success' => true, 'message' => 'Added to wishlist!', 'action' => 'added']);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'unique_wishlist') !== false) {
            echo json_encode(['success' => true, 'message' => 'Already in wishlist.', 'action' => 'already_exists']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to wishlist.']);
        }
    }
}
