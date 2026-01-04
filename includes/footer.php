<?php
// Footer include: prints footer markup, JS includes and closes body/html.
?>
  <footer class="bg-light text-dark text-center py-3">
    <p class="mb-0">© <?= date('Y') ?> Jeweluxe | Exquisite Jewelry for You</p>
  </footer>

  <!-- Shared modals have been moved to the header include -->

  <!-- JS: auth behaviors and bootstrap -->
  <!-- jQuery is already loaded in header -->
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom scripts (if any) -->
  <script src="js/validation.js"></script>

  <!-- Cart page functionality - only load if on cart page -->
  <?php if (basename($_SERVER['PHP_SELF']) === 'cart.php'): ?>
  <script>
  $(document).ready(function() {
    console.log('Cart page functionality loading...');
    
    let cart = [];
    
    function displayCart() {
      console.log('Displaying cart, items:', cart.length);
      if (cart.length === 0) {
        $('#emptyCart').show();
        $('#cartItems').hide();
      } else {
        $('#emptyCart').hide();
        $('#cartItems').show();
        updateCartDisplay();
      }
    }
    
    function updateCartDisplay() {
      let cartHtml = '';
      let subtotal = 0;
      const maxStock = 99;
      
      // Add table header
      cartHtml += '<div class="row mb-3 pb-2 border-bottom bg-light">' +
        '<div class="col-md-4"><strong>Product</strong></div>' +
        '<div class="col-md-2 text-center"><strong>Unit Price</strong></div>' +
        '<div class="col-md-2 text-center"><strong>Quantity</strong></div>' +
        '<div class="col-md-2 text-center"><strong>Total Price</strong></div>' +
        '<div class="col-md-2 text-center"><strong>Actions</strong></div>' +
      '</div>';
      
      cart.forEach(function(item, index) {
        const qty = item.quantity || 1;
        const itemTotal = (item.price || 0) * qty;
        subtotal += itemTotal;
        const itemId = item.item_id || index;
        const isMinQty = qty <= 1;
        const isMaxQty = qty >= maxStock;
        
        cartHtml += '<div class="card mb-3">' +
          '<div class="card-body">' +
            '<div class="row align-items-center">' +
              '<div class="col-md-4">' +
                '<div class="d-flex align-items-center">' +
                  '<img src="' + (item.image || 'image/placeholder.png') + '" alt="' + item.name + '" class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">' +
                  '<div>' +
                    '<h6 class="mb-0">' + item.name + '</h6>' +
                  '</div>' +
                '</div>' +
              '</div>' +
              '<div class="col-md-2 text-center">' +
                '<span class="fw-bold">₱' + item.price.toFixed(2) + '</span>' +
              '</div>' +
              '<div class="col-md-2 text-center">' +
                '<div class="quantity-selector d-inline-flex">' +
                  '<button type="button" id="decrement-' + itemId + '" onclick="changeQuantity(' + index + ', -1)" ' + (isMinQty ? 'disabled' : '') + '>-</button>' +
                  '<input type="number" id="quantity-' + itemId + '" value="' + qty + '" min="1" max="' + maxStock + '" onchange="handleQuantityInput(' + index + ', this.value)" style="width: 50px; height: 32px; text-align: center; border: 1px solid #ddd; border-left: 1px solid #ddd; border-right: 1px solid #ddd; padding: 0 0 0 7px; margin: 0; font-size: 16px;">' +
                  '<button type="button" id="increment-' + itemId + '" onclick="changeQuantity(' + index + ', 1)" ' + (isMaxQty ? 'disabled' : '') + '>+</button>' +
                '</div>' +
              '</div>' +
              '<div class="col-md-2 text-center">' +
                '<span class="fw-bold text-danger">₱' + itemTotal.toFixed(2) + '</span>' +
              '</div>' +
              '<div class="col-md-2 text-center">' +
                '<button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(' + index + ')">Delete</button>' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>';
      });
      
      $('.cart-item-list').html(cartHtml);
      
      // Update totals
      $('#cartSubtotal').text('₱' + subtotal.toFixed(2));
      const shipping = subtotal > 0 ? 150.00 : 0;
      $('#cartShipping').text('₱' + shipping.toFixed(2));
      $('#cartTotal').text('₱' + (subtotal + shipping).toFixed(2));
    }
    
    function useLocalCart() {
      console.log('Using localStorage cart');
      cart = JSON.parse(localStorage.getItem('jeweluxe_cart')) || [];
      displayCart();
    }
    
    // Fetch cart from server
    $.getJSON('get_cart.php').done(function(resp) {
      console.log('Cart server response:', resp);
      
      if (!resp || !resp.success) {
        console.log('Server failed, using localStorage');
        useLocalCart();
        return;
      }

      const items = resp.cart.items || [];
      if (items.length === 0) {
        console.log('No server items, using localStorage');
        useLocalCart();
        return;
      }

      // Map server items to local format
      cart = items.map(function(it) {
        return {
          item_id: it.item_id,
          product_id: it.product_id,
          name: it.name,
          price: parseFloat(it.price),
          image: it.image || 'image/placeholder.png',
          quantity: parseInt(it.quantity),
          sku: it.sku || it.name.replace(/\s+/g, '').toUpperCase()
        };
      });
      
      localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
      console.log('Cart loaded from server:', cart);
      displayCart();
      
    }).fail(function(xhr, status, err) {
      console.log('AJAX failed, using localStorage:', status, err);
      useLocalCart();
    });
    
    // Handle quantity input changes (when user types)
    window.handleQuantityInput = function(index, value) {
      const maxStock = 99;
      const newQuantity = parseInt(value) || 1;
      
      // Validate quantity limits
      if (newQuantity < 1) {
        cart[index].quantity = 1;
        displayCart();
        return;
      }
      
      if (newQuantity > maxStock) {
        cart[index].quantity = maxStock;
        displayCart();
        return;
      }
      
      // Update quantity
      cart[index].quantity = newQuantity;
      
      // Update in database if item has item_id
      const item = cart[index];
      if (item && item.item_id) {
        $.post('update_cart.php', { item_id: item.item_id, quantity: newQuantity })
          .done(function(resp) {
            if (resp && resp.success) {
              item.quantity = resp.quantity || newQuantity;
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            } else {
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            }
          })
          .fail(function() {
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          });
      } else {
        localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
        displayCart();
      }
    };
    
    // Cart management functions
    window.changeQuantity = function(index, change) {
      const maxStock = 99;
      const item = cart[index];
      if (!item) return;
      
      const newQuantity = (item.quantity || 1) + change;
      
      // Validate quantity limits
      if (newQuantity < 1 || newQuantity > maxStock) {
        return;
      }
      
      // Update quantity
      cart[index].quantity = newQuantity;
      
      // Update in database if item has item_id
      if (item && item.item_id) {
        $.post('update_cart.php', { item_id: item.item_id, quantity: newQuantity })
          .done(function(resp) {
            if (resp && resp.success) {
              item.quantity = resp.quantity || newQuantity;
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            } else {
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            }
          })
          .fail(function() {
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          });
      } else {
        localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
        displayCart();
      }
    };
    
    window.updateQuantity = function(index, change, newValue) {
      if (newValue !== undefined) {
        cart[index].quantity = parseInt(newValue);
      } else {
        cart[index].quantity = (cart[index].quantity || 1) + change;
      }
      
      if (cart[index].quantity < 1) {
        cart[index].quantity = 1;
      }
      
      const item = cart[index];
      if (item && item.item_id) {
        $.post('update_cart.php', { item_id: item.item_id, quantity: item.quantity })
          .done(function(resp) {
            if (resp && resp.success) {
              item.quantity = resp.quantity || item.quantity;
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            } else {
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            }
          })
          .fail(function() {
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          });
      } else {
        localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
        displayCart();
      }
    };
    
    window.removeFromCart = function(index) {
      const item = cart[index];
      if (item && item.item_id) {
        $.post('remove_from_cart.php', { item_id: item.item_id })
          .done(function(resp) {
            if (resp && resp.success) {
              cart.splice(index, 1);
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            } else {
              cart.splice(index, 1);
              localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
              displayCart();
            }
          })
          .fail(function() {
            cart.splice(index, 1);
            localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
            displayCart();
          });
      } else {
        cart.splice(index, 1);
        localStorage.setItem('jeweluxe_cart', JSON.stringify(cart));
        displayCart();
      }
    };
  });
  </script>
  <?php endif; ?>

  <script>
  // Determine if we should show modal or navigate to cart page
  function isProductsPage() {
    return window.location.pathname.includes('products.php') || 
           window.location.pathname.endsWith('products');
  }

  // Helper to refresh and render cart modal contents
  function refreshCartModal($modal) {
    $modal = $modal || $('#cartModal');
    const $empty = $modal.find('#emptyCart');
    const $items = $modal.find('#cartItems');
    const $list = $items.find('.cart-item-list');
    const $footer = $modal.find('#cartFooter');

    $empty.hide();
    $items.hide();
    $footer.hide();
    $list.html('<div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

    $.getJSON('get_cart.php').done(function(resp) {
      if (!resp || !resp.success) {
        $list.empty();
        $empty.show();
        return;
      }

      const items = resp.cart.items || [];
      if (items.length === 0) {
        $list.empty();
        $empty.show();
        return;
      }

      $list.empty();

      items.forEach(function(it) {
        // row container
        const $row = $(
          '<div class="d-flex align-items-center mb-3 cart-row" data-item-id="' + it.item_id + '">' +
            '<div class="me-3">' +
              '<img src="' + (it.image || 'image/placeholder.png') + '" alt="' + it.name + '" style="width: 70px; height: 70px; object-fit: cover; border-radius: 6px;">' +
            '</div>' +
            '<div class="flex-grow-1">' +
              '<strong></strong>' +
              '<div class="small text-muted price-line"></div>' +
              '<div class="mt-2 d-flex align-items-center qty-controls">' +
                '<div class="btn-group" role="group" aria-label="Quantity controls">' +
                  '<button type="button" class="btn btn-sm btn-outline-secondary btn-decrease">−</button>' +
                  '<input type="text" class="form-control form-control-sm qty-input text-center" value="' + it.quantity + '" style="width:70px; display:inline-block;">' +
                  '<button type="button" class="btn btn-sm btn-outline-secondary btn-increase">+</button>' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-link text-danger ms-3 btn-remove">Remove</button>' +
              '</div>' +
            '</div>' +
            '<div class="text-end ms-3 line-total">₱' + Number(it.line_total).toFixed(2) + '</div>' +
          '</div>'
        );

        $row.find('strong').text(it.name);
        $row.find('.price-line').text('₱' + Number(it.price).toFixed(2) + ' each');

        // attach handlers
        $row.find('.btn-increase').on('click', function() {
          const current = parseInt($row.find('.qty-input').val() || '0', 10);
          const next = current + 1;
          updateCartItem(it.item_id, next, $modal);
        });

        $row.find('.btn-decrease').on('click', function() {
          const current = parseInt($row.find('.qty-input').val() || '0', 10);
          const next = current - 1;
          // if next <= 0 we'll let the server remove the item
          updateCartItem(it.item_id, next, $modal);
        });

        $row.find('.qty-input').on('change', function() {
          let val = parseInt($(this).val() || '0', 10);
          if (isNaN(val) || val < 0) val = 0;
          updateCartItem(it.item_id, val, $modal);
        });

        $row.find('.btn-remove').on('click', function() {
          removeCartItem(it.item_id, $modal);
        });

        $list.append($row);
      });

      $modal.find('#cartTotal').text('₱' + Number(resp.cart.total).toFixed(2));
      $items.show();
      $footer.show();
    }).fail(function() {
      $list.empty();
      $empty.show();
    });
  }

  // Update a cart item quantity via server
  function updateCartItem(item_id, quantity, $modal) {
    $modal = $modal || $('#cartModal');
    // send as form-encoded POST
    $.post('update_cart.php', { item_id: item_id, quantity: quantity }).done(function(resp) {
      if (!resp || !resp.success) {
        // show a minimal alert (could be improved)
        alert('Could not update cart item.');
        return;
      }

      // if server returned removed flag or quantity was set to removed
      if (resp.removed) {
        // refresh the modal to reflect changes
        refreshCartModal($modal);
        return;
      }

      // At this point resp.success && resp.quantity present
      // Refresh to get accurate totals and line totals
      refreshCartModal($modal);
    }).fail(function() {
      alert('Network error while updating cart.');
    });
  }

  // Remove a cart item via server
  function removeCartItem(item_id, $modal) {
    $modal = $modal || $('#cartModal');
    $.post('remove_from_cart.php', { item_id: item_id }).done(function(resp) {
      if (!resp || !resp.success) {
        alert('Could not remove item from cart.');
        return;
      }
      refreshCartModal($modal);
    }).fail(function() {
      alert('Network error while removing item.');
    });
  }

  // Handle cart click based on current page
  $(document).ready(function() {
    console.log('Setting up cart navigation...');
    const $cartLink = $('#cartLink');
    const $cartModal = $('#cartModal');
    
    console.log('Cart link found:', $cartLink.length > 0);
    console.log('Cart modal found:', $cartModal.length > 0);
    console.log('Is products page:', isProductsPage());
    
    if ($cartLink.length) {
      $cartLink.on('click', function(e) {
        console.log('Cart link clicked!');
        e.preventDefault();
        
        if (isProductsPage() && $cartModal.length) {
          console.log('Showing cart modal...');
          // Show modal on products page
          $cartModal.modal('show');
        } else {
          console.log('Navigating to cart page...');
          // Navigate to cart page on other pages
          window.location.href = 'cart.php';
        }
      });
    }

    // Initialize cart link attributes based on current page
    if (isProductsPage() && $cartModal.length) {
      console.log('Setting up modal attributes...');
      $cartLink.attr('data-bs-toggle', 'modal').attr('data-bs-target', '#cartModal');
    } else {
      console.log('Setting up cart page link...');
      $cartLink.attr('href', 'cart.php');
    }
  });

  // When the cart modal is shown, load contents
  $(document).on('show.bs.modal', '#cartModal', function() {
    console.log('Cart modal shown, refreshing...');
    refreshCartModal($(this));
  });
  </script>
</body>
</html>
