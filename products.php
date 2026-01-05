<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
$pageTitle = 'Jeweluxe - Products';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
  <div class="container">
    <h1 class="display-4">Our Jewelry Collection</h1>
    <p class="lead">Discover timeless jewelry for every occasion!</p>
  </div>
</header>

<section class="container py-5">
  <?php
  // Load products once for all tabs
  require_once 'db.php';
  try {
    $pstmt = $pdo->query('SELECT product_id, product_name, product_price, product_image, category FROM products ORDER BY product_id DESC');
    $prods = $pstmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    $prods = [];
  }
  ?>
  <!-- Tabs navigation -->
  <ul class="nav nav-tabs justify-content-center mb-4" id="productTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="bracelets-tab" data-bs-toggle="tab" data-bs-target="#bracelets" type="button" role="tab" aria-controls="bracelets" aria-selected="false">Bracelets</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="earrings-tab" data-bs-toggle="tab" data-bs-target="#earrings" type="button" role="tab" aria-controls="earrings" aria-selected="false">Earrings</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="necklaces-tab" data-bs-toggle="tab" data-bs-target="#necklaces" type="button" role="tab" aria-controls="necklaces" aria-selected="false">Necklaces</button>
    </li>
  </ul>
  <!-- Tabs content -->
  <div class="tab-content" id="productTabsContent">
    
    <!-- All products -->
    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        if (empty($prods)) {
          echo '<div class="col"><p class="text-muted">No products available.</p></div>';
        } else {
          foreach ($prods as $p) {
            $img = !empty($p['product_image']) ? htmlspecialchars($p['product_image']) : 'image/placeholder.png';
            $name = htmlspecialchars($p['product_name']);
            $price = '₱' . number_format((float)$p['product_price'], 2);
        ?>
            <div class="col">
              <div class="card h-100 text-center product-card-large position-relative">
                <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle wishlist-btn" 
                        data-product-id="<?= $p['product_id'] ?>" 
                        style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: rgba(255,255,255,0.9); z-index: 10; pointer-events: auto; cursor: pointer;">
                  <span class="wishlist-icon" style="font-size: 1.2rem;">♡</span>
                </button>
                <img src="<?= $img ?>" class="card-img-top" alt="<?= $name ?>" style="position: relative; z-index: 1;">
                <div class="card-body">
                  <h5 class="card-title"><?= $name ?></h5>
                  <p class="card-text text-muted mb-2"><?= $price ?></p>
                  <a href="#" class="btn btn-primary w-100 add-to-cart" data-id="<?= $p['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= htmlspecialchars((float)$p['product_price']) ?>" data-image="<?= $img ?>">Add to Cart</a>
                </div>
              </div>
            </div>
        <?php
          }
        }
        ?>
      </div>
    </div>
       
   <!-- // Bracelets products -->
    <div class="tab-pane fade" id="bracelets" role="tabpanel" aria-labelledby="bracelets-tab">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $found = false;
        foreach ($prods as $p) {
          if (stripos($p['category'] ?? '', 'bracelet') === false) continue;
          $found = true;
          $img = !empty($p['product_image']) ? htmlspecialchars($p['product_image']) : 'image/placeholder.png';
          $name = htmlspecialchars($p['product_name']);
          $price = '₱' . number_format((float)$p['product_price'], 2);
        ?>
          <div class="col">
            <div class="card h-100 text-center product-card-large position-relative">
              <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle wishlist-btn" 
                      data-product-id="<?= $p['product_id'] ?>" 
                      style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: rgba(255,255,255,0.9); z-index: 10; pointer-events: auto; cursor: pointer;">
                <span class="wishlist-icon" style="font-size: 1.2rem;">♡</span>
              </button>
              <img src="<?= $img ?>" class="card-img-top" alt="<?= $name ?>" style="position: relative; z-index: 1;">
              <div class="card-body">
                <h5 class="card-title"><?= $name ?></h5>
                <p class="card-text text-muted mb-2"><?= $price ?></p>
                <a href="#" class="btn btn-primary w-100 add-to-cart" data-id="<?= $p['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= htmlspecialchars((float)$p['product_price']) ?>" data-image="<?= $img ?>">Add to Cart</a>
              </div>
            </div>
          </div>
        <?php } if (!$found) echo '<div class="col"><p class="text-muted">No products available.</p></div>'; ?>
      </div>
    </div>
       
    <!-- Earrings products -->
    <div class="tab-pane fade" id="earrings" role="tabpanel" aria-labelledby="earrings-tab">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $found = false;
        foreach ($prods as $p) {
          if (stripos($p['category'] ?? '', 'earring') === false) continue;
          $found = true;
          $img = !empty($p['product_image']) ? htmlspecialchars($p['product_image']) : 'image/placeholder.png';
          $name = htmlspecialchars($p['product_name']);
          $price = '₱' . number_format((float)$p['product_price'], 2);
        ?>
          <div class="col">
            <div class="card h-100 text-center product-card-large position-relative">
              <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle wishlist-btn" 
                      data-product-id="<?= $p['product_id'] ?>" 
                      style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: rgba(255,255,255,0.9); z-index: 10; pointer-events: auto; cursor: pointer;">
                <span class="wishlist-icon" style="font-size: 1.2rem;">♡</span>
              </button>
              <img src="<?= $img ?>" class="card-img-top" alt="<?= $name ?>" style="position: relative; z-index: 1;">
              <div class="card-body">
                <h5 class="card-title"><?= $name ?></h5>
                <p class="card-text text-muted mb-2"><?= $price ?></p>
                <a href="#" class="btn btn-primary w-100 add-to-cart" data-id="<?= $p['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= htmlspecialchars((float)$p['product_price']) ?>" data-image="<?= $img ?>">Add to Cart</a>
              </div>
            </div>
          </div>
        <?php } if (!$found) echo '<div class="col"><p class="text-muted">No products available.</p></div>'; ?>
      </div>
    </div>
    
    <!-- Necklaces products -->
  <div class="tab-pane fade" id="necklaces" role="tabpanel" aria-labelledby="necklaces-tab">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $found = false;
        foreach ($prods as $p) {
          if (stripos($p['category'] ?? '', 'necklace') === false) continue;
          $found = true;
          $img = !empty($p['product_image']) ? htmlspecialchars($p['product_image']) : 'image/placeholder.png';
          $name = htmlspecialchars($p['product_name']);
          $price = '₱' . number_format((float)$p['product_price'], 2);
        ?>
          <div class="col">
            <div class="card h-100 text-center product-card-large position-relative">
              <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle wishlist-btn" 
                      data-product-id="<?= $p['product_id'] ?>" 
                      style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: rgba(255,255,255,0.9); z-index: 10; pointer-events: auto; cursor: pointer;">
                <span class="wishlist-icon" style="font-size: 1.2rem;">♡</span>
              </button>
              <img src="<?= $img ?>" class="card-img-top" alt="<?= $name ?>" style="position: relative; z-index: 1;">
              <div class="card-body">
                <h5 class="card-title"><?= $name ?></h5>
                <p class="card-text text-muted mb-2"><?= $price ?></p>
                <a href="#" class="btn btn-primary w-100 add-to-cart" data-id="<?= $p['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= htmlspecialchars((float)$p['product_price']) ?>" data-image="<?= $img ?>">Add to Cart</a>
              </div>
            </div>
          </div>
        <?php } if (!$found) echo '<div class="col"><p class="text-muted">No products available.</p></div>'; ?>
      </div>
    </div>

  </div>
</section>

?>
<script>
$(document).ready(function() {
  // Initialize cart as empty
  let cart = [];
  
  // Handle cart modal display
  $('#cartModal').on('show.bs.modal', function() {
    if (cart.length === 0) {
      $('#emptyCart').show();
      $('#cartItems').hide();
      $('#cartFooter').hide();
    } else {
      $('#emptyCart').hide();
      $('#cartItems').show();
      $('#cartFooter').show();
      updateCartDisplay();
    }
  });
  
  // Function to update cart display (for future use when items are added)
  function updateCartDisplay() {
    let total = 0;
    let cartHtml = '';
    
    cart.forEach(function(item, index) {
      total += item.price;
      cartHtml += `
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
          <div class="d-flex align-items-center">
            <img src="${item.image}" alt="${item.name}" class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
            <div>
              <h6 class="mb-1">${item.name}</h6>
              <small class="text-muted">₱${item.price.toFixed(2)}</small>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary me-2" onclick="removeFromCart(${index})">Remove</button>
          </div>
        </div>
      `;
    });
    
    $('.cart-item-list').html(cartHtml);
    $('#cartTotal').text('₱' + total.toFixed(2));
  }
  
  // Make functions globally available
  window.addToCart = function(name, price, image) {
    // Get existing cart from localStorage
    let cart = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
    
    // Check if item already exists
    let existingItem = cart.find(item => item.name === name);
    if (existingItem) {
      existingItem.quantity = (existingItem.quantity || 1) + 1;
    } else {
      cart.push({
        name: name, 
        price: price, 
        image: image,
        quantity: 1,
        sku: name.replace(/\s+/g, '').toUpperCase()
      });
    }
    
    // Save to localStorage
    localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
    
    // Update cart count in navbar if needed
    console.log('Added to cart:', name);
    
    // Show success message
    showCartNotification(name + ' added to cart!');
  };
  
  // Show cart notification
  function showCartNotification(message) {
    // Create notification element
    const notification = $('<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
      '<i class="fas fa-check-circle me-2"></i>' + message +
      '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
      '</div>');
    
    $('body').append(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
      notification.alert('close');
    }, 3000);
  };
  
  window.removeFromCart = function(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    if (cart.length === 0) {
      $('#emptyCart').show();
      $('#cartItems').hide();
      $('#cartFooter').hide();
    }
  };
  
  // Add click handlers to all "Add to Cart" buttons (only those inside product cards)
  $('.card .btn-primary').click(function(e) {
    e.preventDefault();
    const btn = $(this);

    // prefer data attributes if available (added to the anchor in PHP)
    const productId = btn.data('id');
    const name = btn.data('name') || btn.closest('.card').find('.card-title').text();
    const price = parseFloat(btn.data('price')) || parseFloat(btn.closest('.card').find('.card-text').text().replace('₱', '').replace(/,/g, '')) || 0;
    const image = btn.data('image') || btn.closest('.card').find('img').attr('src');

    // Visual feedback on the button (show it's processing)
    btn.text('Adding...').removeClass('btn-primary').addClass('btn-secondary').prop('disabled', true);

    // Send add-to-cart request to server (relative path, no leading slash)
    $.post('add_to_cart.php', { product_id: productId, quantity: 1 })
      .done(function(resp) {
          // Check if user needs to log in
          if (resp && resp.error === 'Please log in to use the cart.') {
            ToastNotification.warning('Please log in to add items to your cart.');
            // Open account modal for login
            setTimeout(() => {
              const accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
              accountModal.show();
            }, 500);
            // Reset button
            btn.text('Add to Cart').removeClass('btn-secondary').addClass('btn-primary').prop('disabled', false);
            return;
          }
          
          if (resp && resp.success) {
            // NOW add to localStorage after server confirms
            addToCart(name, price, image);
            
            // sync server-side item_id into localStorage cart
            try {
              const serverItem = resp.item;
              let local = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
              // try to find by product_id
              let found = local.find(it => (it.product_id && serverItem && serverItem.product_id && it.product_id == serverItem.product_id) || (it.name === name));
              if (found) {
                found.item_id = serverItem.item_id || found.item_id;
                found.quantity = serverItem.quantity || found.quantity || 1;
              } else {
                local.push({
                  item_id: serverItem.item_id || null,
                  product_id: serverItem.product_id || productId || null,
                  name: serverItem.name || name,
                  price: parseFloat(serverItem.price) || price,
                  image: serverItem.image || image,
                  quantity: serverItem.quantity || 1,
                  sku: (serverItem.name || name).replace(/\s+/g, '').toUpperCase()
                });
              }
              localStorage.setItem('jeweluxe_cart', JSON.stringify(local));
            } catch (e) {
              console.error('Failed to sync cart with server response', e, resp);
            }
            ToastNotification.success(name + ' added to cart!');
          } else {
            const msg = resp && resp.message ? resp.message : 'Could not add to cart.';
            ToastNotification.error('Server error: ' + msg);
            console.error('Add to cart response error:', resp);
            // Reset button
            btn.text('Add to Cart').removeClass('btn-secondary').addClass('btn-primary').prop('disabled', false);
          }
        })
      .fail(function(xhr, status, err) {
        ToastNotification.error('Server error adding to cart');
        console.error('Add to cart AJAX failed:', status, err, xhr.responseText);
        // Reset button
        btn.text('Add to Cart').removeClass('btn-secondary').addClass('btn-primary').prop('disabled', false);
      });
  });

  function displayCartModal() {
    let cart = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
    
    if (cart.length === 0) {
      $('#emptyCart').show();
      $('#cartItems').hide();
      $('#cartFooter').hide();
    } else {
      $('#emptyCart').hide();
      $('#cartItems').show();
      $('#cartFooter').show();
      updateCartModalDisplay();
    }
  }

  function updateCartModalDisplay() {
    let cart = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
    let cartHtml = '';
    let subtotal = 0;
    
    cart.forEach(function(item, index) {
      subtotal += item.price * (item.quantity || 1);
      cartHtml += `
        <div class="card mb-3">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2">
                <img src="${item.image}" alt="${item.name}" class="img-fluid rounded" style="height: 60px; object-fit: cover;">
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
    $('#cartTotal').text('₱' + subtotal.toFixed(2));
  }

  // Global functions for cart management
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

  // Wishlist functionality
  document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const productId = this.dataset.productId;
      const icon = this.querySelector('.wishlist-icon');
      
      // Check if user is logged in
      if (!<?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        ToastNotification.warning('Please log in to use the wishlist.');
        setTimeout(() => window.location.href = 'login.php?redirect=products.php', 1500);
        return;
      }
      
      fetch('add_to_wishlist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.action === 'added') {
            icon.textContent = '♥';
            icon.style.color = '#e74c3c';
            this.style.background = 'rgba(231, 76, 60, 0.15)';
            showCartNotification(data.message);
          } else if (data.action === 'removed') {
            icon.textContent = '♡';
            icon.style.color = 'inherit';
            this.style.background = 'rgba(255,255,255,0.9)';
            showCartNotification(data.message);
          }
        } else {
          ToastNotification.error(data.message || 'Error updating wishlist.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        ToastNotification.error('An error occurred.');
      });
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php';
?>