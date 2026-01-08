<?php
// Header include: prints document head and opens <body>.
// Usage: set $pageTitle before including if you want a custom title.
require_once __DIR__ . '/auth.php';
init_session();
$pageTitle = $pageTitle ?? 'Jeweluxe';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Custom stylesheet -->
  <link href="dist/styles.css" rel="stylesheet">
  <!-- jQuery (needed by some inline page scripts) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<!-- Shared Modals moved here so they are available early in the document -->
<!-- Account Modal (Login) -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accountModalLabel"><img src="image/user.svg" alt="Account" class="me-2" style="height:1.25em; width:1.25em; vertical-align:middle;">Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        // auth.php and init_session() are already included above in this header.
        if (is_logged_in()):
          $u = current_user();
          $display = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['username'] ?? $u['email'] ?? '');
        ?>
          <div class="text-center py-3">
            <h5>Signed in as</h5>
            <p class="lead"><?php echo htmlspecialchars($display, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="d-grid gap-2">
              <a class="btn btn-outline-secondary" href="logout.php">Sign Out</a>
            </div>
          </div>
        <?php else: ?>
          <form id="loginForm" method="post" action="login_handler.php">
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email or Username</label>
              <input name="email" type="text" class="form-control" id="loginEmail" placeholder="Enter your email or username" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-group">
                <input name="password" type="password" class="form-control" id="loginPassword" placeholder="Enter your password" data-required="true">
              </div>
              <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" id="toggleLoginPassword">
                <label class="form-check-label" for="toggleLoginPassword">Show Password</label>
              </div>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Sign In</button>
            </div>
          </form>
          <hr class="my-4">
          <div class="text-center">
            <p class="mb-3">Don't have an account yet?</p>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Create New Account</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">📝 Create New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm" method="post" action="register.php" novalidate>
          <div id="registerFeedback" class="mb-3" aria-live="polite"></div>
          <div id="registerPreview" class="mb-3" style="display:none;">
            <div class="card p-2 bg-light">
              <strong>Preview (not saved):</strong>
              <div id="previewFirstLast" class="small text-muted"></div>
              <div id="previewEmail" class="small text-muted"></div>
              <div id="previewUsername" class="small text-muted"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="firstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter your first name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter your last name" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="registerEmail" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Enter your email address" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
          </div>
          <div class="mb-3">
            <label for="registerPassword" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Create a password" data-required="true">
            </div>
            <div class="form-check mt-2">
              <input type="checkbox" class="form-check-input" id="toggleRegisterPassword">
              <label class="form-check-label" for="toggleRegisterPassword">Show Password</label>
            </div>
            <div class="form-text">Password must be at least 8 characters long.</div>
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" data-required="true">
            </div>
            <div class="form-check mt-2">
              <input type="checkbox" class="form-check-input" id="toggleConfirmPassword">
              <label class="form-check-label" for="toggleConfirmPassword">Show Password</label>
            </div>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
            <label class="form-check-label" for="agreeTerms">
              I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
            </label>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success">Create Account</button>
          </div>
        </form>
        <hr class="my-4">
        <div class="text-center">
          <p class="mb-3">Already have an account?</p>
          <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#accountModal" data-bs-dismiss="modal">
            Sign In Instead
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Shopping Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cartModalLabel">🛒 Shopping Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Empty Cart Content -->
        <div id="emptyCart" class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-shopping-cart fa-4x text-muted"></i>
          </div>
          <h4 class="text-muted mb-3">Your cart is empty</h4>
          <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
            Continue Shopping
          </button>
        </div>
        
        <!-- Cart Items Content (hidden by default) -->
        <div id="cartItems" style="display: none;">
          <div class="cart-item-list">
            <!-- Cart items will be populated here via JavaScript -->
          </div>
          <div class="cart-summary mt-4 pt-4 border-top">
            <div class="d-flex justify-content-between">
              <strong>Total: <span id="cartTotal">₱0.00</span></strong>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer" id="cartFooter" style="display: none;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="cartProceedBtn"
                data-logged-in="<?php echo is_logged_in() ? '1' : '0'; ?>">Proceed to Checkout</button>
      </div>
    </div>
  </div>
</div>

<script>
// Handle proceed-to-checkout from the cart modal
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('cartProceedBtn');
  if (!btn) return;

  btn.addEventListener('click', function() {
    const loggedIn = btn.getAttribute('data-logged-in') === '1';
    if (loggedIn) {
      window.location.href = 'checkout.php';
      return;
    }

    // Not logged in: close cart modal and open account modal for login
    const cartEl = document.getElementById('cartModal');
    const accountEl = document.getElementById('accountModal');
    if (cartEl && typeof bootstrap !== 'undefined') {
      const cartModal = bootstrap.Modal.getOrCreateInstance(cartEl);
      cartModal.hide();
    }
    if (accountEl && typeof bootstrap !== 'undefined') {
      const accountModal = bootstrap.Modal.getOrCreateInstance(accountEl);
      accountModal.show();
    } else {
      // Fallback: redirect to login page
      window.location.href = 'login.php?redirect=checkout';
    }
  });
});

// Custom Toast Notification System
const ToastNotification = {
  show(message, type = 'success', duration = 3000) {
    // Create toast container if doesn't exist
    let toastContainer = document.getElementById('customToastContainer');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'customToastContainer';
      toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    const typeClass = {
      'success': 'bg-success',
      'error': 'bg-danger',
      'warning': 'bg-warning',
      'info': 'bg-info'
    }[type] || 'bg-info';

    toast.innerHTML = `
      <div class="alert alert-dismissible fade show ${typeClass} text-white mb-3" role="alert" style="min-width: 300px;">
        <div>
          <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
      </div>
    `;

    toastContainer.appendChild(toast.firstElementChild);

    // Auto dismiss after duration
    if (duration > 0) {
      setTimeout(() => {
        const dismissBtn = toast.querySelector('[data-bs-dismiss="alert"]');
        if (dismissBtn) dismissBtn.click();
      }, duration);
    }
  },

  success(message) { this.show(message, 'success'); },
  error(message) { this.show(message, 'error'); },
  warning(message) { this.show(message, 'warning'); },
  info(message) { this.show(message, 'info'); }
};

// Custom Confirmation Modal
const ConfirmModal = {
  show(title, message, onConfirm, onCancel) {
    let modal = document.getElementById('customConfirmModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'customConfirmModal';
      modal.className = 'modal fade';
      modal.tabIndex = -1;
      modal.setAttribute('aria-hidden', 'true');
      document.body.appendChild(modal);
    }

    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            ${message}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
          </div>
        </div>
      </div>
    `;

    const bsModal = new bootstrap.Modal(modal);
    const confirmBtn = modal.querySelector('#confirmBtn');

    confirmBtn.addEventListener('click', () => {
      bsModal.hide();
      if (onConfirm) onConfirm();
    });

    modal.addEventListener('hidden.bs.modal', () => {
      if (onCancel) onCancel();
    });

    bsModal.show();
    return false;
  }
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const isLoggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
  const params = new URLSearchParams(window.location.search);
  const loginStatus = params.get('login');
  const showLogin = params.get('showLogin');
  const registered = params.get('registered');

  const loginMessages = {
    ok: { type: 'success', text: 'Login successful! Welcome back.' },
    missing: { type: 'error', text: 'Please fill in all required fields.' },
    notfound: { type: 'error', text: 'User not found. Please check your credentials.' },
    bad: { type: 'error', text: 'Invalid password. Please try again.' },
    err: { type: 'error', text: 'An error occurred. Please try again.' }
  };

  if (loginStatus && typeof ToastNotification !== 'undefined' && loginMessages[loginStatus]) {
    const msg = loginMessages[loginStatus];
    ToastNotification[msg.type](msg.text);
  }

  if (registered === '1' && typeof ToastNotification !== 'undefined') {
    ToastNotification.success('Your account was created. Please sign in.');
  }

  if (isLoggedIn) {
    return;
  }

  const shouldOpenModal = showLogin === '1' || loginStatus === 'required' || loginStatus === 'missing' || loginStatus === 'notfound' || loginStatus === 'bad' || loginStatus === 'err';
  if (shouldOpenModal) {
    const accountEl = document.getElementById('accountModal');
    if (accountEl && typeof bootstrap !== 'undefined') {
      const modal = bootstrap.Modal.getOrCreateInstance(accountEl);
      modal.show();
    }
  }
});
</script>
