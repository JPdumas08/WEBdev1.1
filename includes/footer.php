<?php
// Footer include: prints footer markup, JS includes and closes body/html.
?>
  <footer class="bg-light text-dark text-center py-3">
    <p class="mb-0">© <?= date('Y') ?> Jeweluxe | Exquisite Jewelry for You</p>
  </footer>

  <!-- Shared modals have been moved to the header include -->

  <!-- JS: auth behaviors and bootstrap -->
  <script src="/js/auth.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom scripts (if any) -->
  <script src="/js/validation.js"></script>
  <script>
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

  // When the cart modal is shown, load contents
  $(document).on('show.bs.modal', '#cartModal', function() {
    refreshCartModal($(this));
  });
  </script>
</body>
</html>
