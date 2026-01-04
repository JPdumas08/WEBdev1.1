<?php
require_once __DIR__ . '/includes/auth.php';
// ensure the session is started before any output
init_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jeweluxe - Bootstrap</title>

  <!-- Bootstrap CSS (comment out to see plain HTML) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery CDN -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Validation Script -->
  <script src="js/validation.js"></script>
  <!-- Custom CSS -->
  <link href="styles.css" rel="stylesheet">
</head>
<body>
  <div class="main-content">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
  <a class="navbar-brand text-start" href="home.php">← Jeweluxe</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav mb-2 mb-lg-0">
          <li class="nav-item"><span class="nav-link active" style="cursor:default;">Home</span></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contactus.php">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Cart</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">👤 Account</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
    <div class="container">
      <h1 class="display-4">Shine Bright with Jeweluxe</h1>
      <p class="lead">Discover timeless jewelry for every occasion!</p>
      <div class="mt-4">
        <a href="products.php" class="btn btn-primary btn-lg me-3">Shop Collection</a>
        <a href="about.php" class="btn btn-outline-light btn-lg">Learn More</a>
      </div>
    </div>
  </header>

  <!-- FEATURED SECTION -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-5">Why Choose Jeweluxe?</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="text-center">
            <i class="fas fa-gem fa-3x text-primary mb-3"></i>
            <h4>Premium Quality</h4>
            <p>Each piece is crafted with the finest materials and attention to detail.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="text-center">
            <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
            <h4>Fast Shipping</h4>
            <p>Quick and secure delivery to your doorstep worldwide.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="text-center">
            <i class="fas fa-heart fa-3x text-primary mb-3"></i>
            <h4>Customer Love</h4>
            <p>Join thousands of satisfied customers who trust Jeweluxe.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PRODUCTS -->
  <section class="py-100">
    <div class="container">
      <h2 class="text-center mb-10" style="
        font-size: 1.8rem;
        font-weight: bold;
        color: #8B4513;
        letter-spacing: 2px;
        align-items: center;
      ">
        Featured Products
      </h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100">
            <video src="Video/bracelet.mp4" class="card-img-top" autoplay muted loop poster="" style="width:100%;height:auto;">
            </video>
            <div class="card-body">
              <h5 class="card-title">Bracelet</h5>
              <p class="card-text">₱2,345.00</p>
              <a href="#" class="btn btn-primary w-100 add-to-cart" data-name="Bracelet" data-price="2345" data-image="image/bracelet.jpg">Add to Cart</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <video src="Video/earring.mp4" class="card-img-top" autoplay muted loop poster="" style="width:100%;height:auto;">
            </video>
            <div class="card-body">
              <h5 class="card-title">Earrings</h5>
              <p class="card-text">₱1,600.00</p>
              <a href="#" class="btn btn-primary w-100 add-to-cart" data-name="Earrings" data-price="1600" data-image="image/earring.jpg">Add to Cart</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <video src="Video/necklace.mp4" class="card-img-top" autoplay muted loop poster="" style="width:100%;height:auto;">
            </video>
            <div class="card-body">
              <h5 class="card-title">Necklace</h5>
              <p class="card-text">₱2,499.00</p>
              <a href="#" class="btn btn-primary w-100 add-to-cart" data-name="Necklace" data-price="2499" data-image="image/necklace.jpg">Add to Cart</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  </div>
  <footer class="bg-light text-dark text-center py-3">
    <p class="mb-0">© 2025 Jeweluxe | Exquisite Jewelry for You</p>
  </footer>

  <!-- Account Modal (Login) -->
  <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="accountModalLabel">👤 Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php
          require_once __DIR__ . '/includes/auth.php';
          init_session();
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
                  <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                    <i class="fas fa-eye" id="loginPasswordIcon"></i>
                  </button>
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
            <!-- Server / client feedback will be shown here -->
            <div id="registerFeedback" class="mb-3" aria-live="polite"></div>
            <!-- Non-sensitive preview of entered credentials (JS may populate) -->
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
                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                  <i class="fas fa-eye" id="registerPasswordIcon"></i>
                </button>
              </div>
              <div class="form-text">Password must be at least 8 characters long.</div>
            </div>
            <div class="mb-3">
              <label for="confirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" data-required="true">
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                  <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                </button>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Shared authentication JS -->
  <script src="js/auth.js"></script>

  <script>
  $(document).ready(function() {
    // Function to add item to cart
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
    
    // Add click handlers to all "Add to Cart" buttons
    $('.add-to-cart').click(function(e) {
      e.preventDefault();
      const btn = $(this);
      
      const name = btn.data('name');
      const price = parseFloat(btn.data('price')) || 0;
      const image = btn.data('image');
      
      // Add to localStorage cart
      addToCart(name, price, image);
      
      // Visual feedback on button
      btn.text('Added!').removeClass('btn-primary').addClass('btn-success');
      setTimeout(() => {
        btn.text('Add to Cart').removeClass('btn-success').addClass('btn-primary');
      }, 1500);
    });
  });
  </script>

</body>
</html>