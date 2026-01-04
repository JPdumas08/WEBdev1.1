<?php
require_once 'db.php';
require_once 'init_session.php';

try {
    // Check what's in the products table
    $stmt = $pdo->query("SELECT product_id, product_name, product_image FROM products LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Products in database:\n";
    echo "<pre>";
    foreach ($results as $row) {
        echo "ID: {$row['product_id']}, Name: {$row['product_name']}, Image: " . (empty($row['product_image']) ? "EMPTY/NULL" : $row['product_image']) . "\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
