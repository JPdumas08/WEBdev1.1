<?php
$pageTitle = 'Jeweluxe - Cart';
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
init_session();
?>

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
      <div id="emptyCart" class="text-center py-5">
        <div class="mb-4">
          <i class="fas fa-shopping-cart fa-4x text-muted"></i>
        </div>
        <h4 class="text-muted mb-3">Your cart is empty</h4>
        <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
        <a href="products.php" class="btn btn-primary btn-lg">
          <i class="fas fa-shopping-bag me-2"></i>Start Shopping
        </a>
      </div>
      
      <!-- Cart Items Content (hidden by default) -->
      <div id="cartItems" style="display: none;">
        <div class="row">
          <div class="col-lg-8">
            <div class="cart-item-list">
              <!-- Cart items will be populated here via JavaScript -->
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
                  <span id="cartSubtotal">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Shipping:</span>
                  <span id="cartShipping">₱150.00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                  <strong>Total:</strong>
                  <strong id="cartTotal">₱150.00</strong>
                </div>
                <button type="button" class="btn btn-success w-100 mb-2">
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

<script>
$(document).ready(function() {
  // Initialize cart (we try server-side first, then fallback to localStorage)
  let cart = [];

  function useLocalCart() {
    cart = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
    displayCart();
  }

  // Try fetching server-side cart (works when user is logged in). If that fails or returns empty, fall back to localStorage.
  $.get('get_cart.php')
    .done(function(resp) {
      try {
        // get_cart.php returns { success: true, cart: { items: [...] } }
        const items = resp && resp.cart && Array.isArray(resp.cart.items) ? resp.cart.items : [];
        if (resp && resp.success && items.length > 0) {
          // Map server items into the local shape used by the UI, preserving cart_item id
          cart = items.map(function(it) {
            return {
              item_id: it.item_id ? parseInt(it.item_id) : (it.itemId ? parseInt(it.itemId) : null),
              product_id: it.product_id || it.productId || null,
              name: it.name || it.product_name || it.productName || '',
              price: parseFloat(it.price) || parseFloat(it.product_price) || 0,
              image: it.image || it.product_image || 'image/placeholder.png',
              quantity: parseInt(it.quantity || it.qty || 1) || 1,
              sku: it.sku || ''
            };
          });
          // Keep a localStorage mirror for offline UX
          localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
          displayCart();
        } else {
          useLocalCart();
        }
      } catch (e) {
        console.error('Error parsing server cart response', e, resp);
        useLocalCart();
      }
    })
    .fail(function() {
      // server-side not available or user not logged in — use localStorage
      useLocalCart();
    });

  // Display cart items
  function displayCart() {
    if (cart.length === 0) {
      $('#emptyCart').show();
      $('#cartItems').hide();
    } else {
      $('#emptyCart').hide();
      $('#cartItems').show();
      updateCartDisplay();
    }
  }
  
  // Update cart display
  function updateCartDisplay() {
    let cartHtml = '';
    let subtotal = 0;
    
    cart.forEach(function(item, index) {
      subtotal += (item.price || 0) * (item.quantity || 1);
      cartHtml += `
        <div class="card mb-3">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2">
                <img src="${item.image}" alt="${item.name}" class="img-fluid rounded" style="height: 80px; object-fit: cover;">
              </div>
              <div class="col-md-4">
                <h6 class="mb-1">${item.name}</h6>
                <small class="text-muted">SKU: ${item.sku || 'N/A'}</small>
              </div>
              <div class="col-md-2">
                <span class="fw-bold">₱${item.price.toFixed(2)}</span>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(${index}, -1)">-</button>
                  <input type="number" class="form-control form-control-sm text-center" value="${item.quantity || 1}" min="1" max="10" onchange="updateQuantity(${index}, 0, this.value)">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(${index}, 1)">+</button>
                </div>
              </div>
              <div class="col-md-2 text-end">
                <span class="fw-bold">₱${((item.quantity || 1) * item.price).toFixed(2)}</span>
                <br>
                <button class="btn btn-outline-danger btn-sm mt-1" onclick="removeFromCart(${index})">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    });
    
    $('.cart-item-list').html(cartHtml);
    
    // Update totals
    $('#cartSubtotal').text('₱' + subtotal.toFixed(2));
    const shipping = subtotal > 0 ? 150.00 : 0;
    $('#cartShipping').text('₱' + shipping.toFixed(2));
    $('#cartTotal').text('₱' + (subtotal + shipping).toFixed(2));
  }
  
  // Update quantity
  window.updateQuantity = function(index, change, newValue) {
    if (newValue !== undefined) {
      cart[index].quantity = parseInt(newValue);
    } else {
      cart[index].quantity = (cart[index].quantity || 1) + change;
    }
    
    if (cart[index].quantity < 1) {
      cart[index].quantity = 1;
    }

    // If this item exists on server (has item_id), update server as well
    const item = cart[index];
    if (item && item.item_id) {
      $.post('update_cart.php', { item_id: item.item_id, quantity: item.quantity })
        .done(function(resp) {
          if (resp && resp.success) {
            // reflect any canonical quantity from server
            item.quantity = resp.quantity || item.quantity;
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            updateCartDisplay();
          } else {
            console.error('Failed to update cart on server', resp);
            // fallback: still update locally
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            updateCartDisplay();
          }
        })
        .fail(function(xhr, status, err) {
          console.error('update_cart.php request failed', status, err, xhr.responseText);
          // fallback to local update
          localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
          updateCartDisplay();
        });
    } else {
      // local-only item
      localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
      updateCartDisplay();
    }
  };
  
  // Remove from cart
  window.removeFromCart = function(index) {
    const item = cart[index];
    if (item && item.item_id) {
      // ask server to remove
      $.post('remove_from_cart.php', { item_id: item.item_id })
        .done(function(resp) {
          if (resp && resp.success) {
            cart.splice(index, 1);
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          } else {
            console.error('Server failed to remove item', resp);
            // still remove locally to keep UX responsive
            cart.splice(index, 1);
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          }
        })
        .fail(function(xhr, status, err) {
          console.error('remove_from_cart.php request failed', status, err, xhr.responseText);
          // fallback: remove locally
          cart.splice(index, 1);
          localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
          displayCart();
        });
    } else {
      // local-only item
      cart.splice(index, 1);
      localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
      displayCart();
    }
  };
  
  // Initialize display
  displayCart();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>