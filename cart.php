<?php
$pageTitle = 'Jeweluxe - Cart';
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
require_once __DIR__ . '/includes/header.php';

// Get cart from database instead of session
$user_id = $_SESSION['user_id'] ?? null;
$cart = [];
$subtotal = 0;
$shipping = 150.00;  // Fixed shipping cost
$total = $shipping;   // Start with shipping

if ($user_id) {
    try {
        // Get user's cart from database
        $stmt = $pdo->prepare('SELECT cart_id FROM cart WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $user_id]);
        $cartRow = $stmt->fetch();
        
        if ($cartRow) {
            $cart_id = (int)$cartRow['cart_id'];
            
            // Get cart items with product details
            $q = "SELECT ci.cart_item_id AS item_id, ci.product_id, ci.quantity, p.product_name AS name, p.product_image AS image, ci.price
                FROM cart_items ci
                JOIN products p ON p.product_id = ci.product_id
                WHERE ci.cart_id = :cart_id
                ORDER BY ci.cart_item_id DESC";
            $cstmt = $pdo->prepare($q);
            $cstmt->execute([':cart_id' => $cart_id]);
            $cart = $cstmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate subtotal
            foreach ($cart as $item) {
                $itemTotal = (float)$item['price'] * (int)$item['quantity'];
                $subtotal += $itemTotal;
            }
        }
    } catch (Exception $e) {
        error_log('Cart retrieval error: ' . $e->getMessage());
    }
}

$total += $subtotal;

// Determine if cart is empty
$isEmpty = empty($cart);
?>

  <main class="flex-grow-1">
    <!-- HERO -->
    <header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
      <div class="container">
        <h1 class="display-4">Your Shopping Cart</h1>
        <p class="lead">Review your selected jewelry items before checkout!</p>
      </div>
    </header>

    <!-- CART CONTENT -->
    <section class="py-5">
      <div class="container">
        <!-- Empty Cart Content -->
        <div id="emptyCart" class="text-center py-5" style="display: <?php echo $isEmpty ? 'block' : 'none'; ?>;">
          <div class="mb-4">
            <i class="fas fa-shopping-cart fa-4x text-muted"></i>
          </div>
          <h4 class="text-muted mb-3">Your cart is empty</h4>
          <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
          <a href="products.php" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Start Shopping
          </a>
        </div>
        
        <!-- Cart Items Content -->
        <div id="cartItems" style="display: <?php echo !$isEmpty ? 'block' : 'none'; ?>;">
          <div class="row">
            <div class="col-lg-8">
              <div class="cart-item-list">
                <?php if (!$isEmpty): ?>
                  <?php foreach ($cart as $index => $item): ?>
                    <?php $maxStock = 99; // Maximum stock limit per item ?>
                    <div class="card mb-3 product-card-large-square" data-item-id="<?php echo (int)$item['item_id']; ?>">  <!-- Added data-item-id -->
                      <div class="card-body d-flex align-items-center">
                        <?php 
                          // Use product image from database
                          $img = !empty($item['image']) ? htmlspecialchars($item['image']) : 'image/placeholder.png';
                        ?>
                        <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                        <div class="flex-grow-1">
                          <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                          <p class="card-text mb-2">Price: ₱<?php echo number_format($item['price'], 2); ?></p>
                          <div class="d-flex align-items-center mb-2">
                            <span class="me-2">Quantity:</span>
                            <div class="quantity-selector">
                              <button type="button" 
                                      id="decrement-<?php echo $item['item_id']; ?>" 
                                      onclick="changeQuantity(<?php echo (int)$item['item_id']; ?>, -1, <?php echo $item['quantity']; ?>, <?php echo $maxStock; ?>)"
                                      <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                              <input type="number" 
                                     id="quantity-<?php echo $item['item_id']; ?>" 
                                     value="<?php echo $item['quantity']; ?>" 
                                     min="1" 
                                     max="<?php echo $maxStock; ?>" 
                                     readonly>
                              <button type="button" 
                                      id="increment-<?php echo $item['item_id']; ?>" 
                                      onclick="changeQuantity(<?php echo (int)$item['item_id']; ?>, 1, <?php echo $item['quantity']; ?>, <?php echo $maxStock; ?>)"
                                      <?php echo $item['quantity'] >= $maxStock ? 'disabled' : ''; ?>>+</button>
                            </div>
                          </div>
                          <p class="card-text"><strong>Subtotal: ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></p>
                        </div>
                        <div class="d-flex flex-column">
                          <button class="btn btn-sm btn-danger" onclick="removeItem(<?php echo (int)$item['item_id']; ?>)"><i class="fas fa-trash"></i> Remove</button>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span id="cartShipping">₱<?php echo number_format($shipping, 2); ?></span>
                  </div>
                  <hr>
                  <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong id="cartTotal">₱<?php echo number_format($total, 2); ?></strong>
                  </div>
                  <button type="button" class="btn btn-success w-100 mb-2" onclick="proceedToCheckout()">
                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                  </button>
                  <a href="products.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Add JavaScript for cart interactions (place this before </body> in footer.php or here) -->
  <script>
    function changeQuantity(itemId, change, currentQty, maxStock) {
      const newQuantity = currentQty + change;
      
      // Validate quantity limits
      if (newQuantity < 1 || newQuantity > maxStock) {
        return; // Don't proceed if out of bounds
      }
      
      // Update button states immediately for UX
      const decrementBtn = document.getElementById('decrement-' + itemId);
      const incrementBtn = document.getElementById('increment-' + itemId);
      const quantityInput = document.getElementById('quantity-' + itemId);
      
      if (decrementBtn && incrementBtn && quantityInput) {
        decrementBtn.disabled = (newQuantity <= 1);
        incrementBtn.disabled = (newQuantity >= maxStock);
        quantityInput.value = newQuantity;
      }
      
      // Send AJAX request to update cart in database
      updateQuantity(itemId, newQuantity);
    }
    
    function recalculateTotals() {
      // Get all cart items and recalculate
      let subtotal = 0;
      document.querySelectorAll('.card-body').forEach(function(card) {
        const priceText = card.querySelector('.card-text strong')?.textContent;
        if (priceText && priceText.includes('Subtotal:')) {
          const amount = parseFloat(priceText.replace('Subtotal: ₱', '').replace(/,/g, ''));
          if (!isNaN(amount)) {
            subtotal += amount;
          }
        }
      });
      
      const shipping = subtotal > 0 ? 150.00 : 0;
      const total = subtotal + shipping;
      
      // Update display
      document.getElementById('cartSubtotal').textContent = '₱' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      document.getElementById('cartShipping').textContent = '₱' + shipping.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      document.getElementById('cartTotal').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function updateQuantity(itemId, newQuantity) {
      // Send AJAX request to update cart in database
      fetch('update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, quantity: newQuantity })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update the item subtotal in the DOM
          const card = document.querySelector('[data-item-id="' + itemId + '"]');
          if (card) {
            const priceElement = card.querySelector('.card-text:first-of-type');
            if (priceElement) {
              const priceMatch = priceElement.textContent.match(/₱([\d,]+\.\d{2})/);
              if (priceMatch) {
                const unitPrice = parseFloat(priceMatch[1].replace(/,/g, ''));
                const newSubtotal = unitPrice * newQuantity;
                const subtotalElement = card.querySelector('.card-text strong');
                if (subtotalElement) {
                  subtotalElement.parentElement.innerHTML = '<strong>Subtotal: ₱' + newSubtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</strong>';
                }
              }
            }
          }
          // Recalculate totals
          recalculateTotals();
        } else {
          ToastNotification.error('Error updating cart');
          location.reload();
        }
      })
      .catch(error => {
        ToastNotification.error('Error updating cart');
        console.error('Update error:', error);
        location.reload();
      });
    }

    function removeItem(itemId) {
      // Remove item from database
      fetch('remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          ToastNotification.error('Error removing item');
        }
      });
    }

    function proceedToCheckout() {
      // Redirect to checkout page or handle logic
      window.location.href = 'checkout.php';
    }
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>