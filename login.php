<?php
$pageTitle = 'Jeweluxe - Login';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- HERO -->
  <header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
    <div class="container">
      <h1 class="display-4">Welcome Back to Jeweluxe</h1>
      <p class="lead">Sign in to access your account and continue shopping!</p>
    </div>
  </header>

  <!-- LOGIN FORM SECTION -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="contact-form-card">
            <h3 class="text-center mb-4">👤 Account Login</h3>
            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
              <div class="alert alert-success text-center">Your account was created. Please sign in.</div>
            <?php endif; ?>
            <form id="loginForm" method="post" action="login_handler.php">
              <input type="hidden" name="redirect_to" value="home.php">
              <div class="mb-3">
                <label for="loginEmail" class="form-label">Email Address</label>
                <input name="email" type="email" class="form-control" id="loginEmail" placeholder="Enter your email" required>
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
                <label class="form-check-label" for="rememberMe">
                  Remember me
                </label>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Sign In</button>
              </div>
            </form>
            <hr class="my-4">
            <div class="text-center">
              <p class="mb-3">Don't have an account yet?</p>
              <button type="button" class="btn btn-outline-primary" onclick="window.location.href='home.php'">
                Go Back to Home
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
?>
<script>
$(document).ready(function() {
  // Password toggle functionality
  $('#toggleLoginPassword').click(function() {
    const passwordField = $('#loginPassword');
    const icon = $('#loginPasswordIcon');
    
    if (passwordField.attr('type') === 'password') {
      passwordField.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      passwordField.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });
  
  // Auto-trim spaces on input
  $('input[type="text"], input[type="email"]').on('blur', function() {
    $(this).val($(this).val().trim());
  });
  
  // Disable browser validation messages
  $('input[required]').on('invalid', function(e) {
    e.preventDefault();
  });
  
  // The login form submits to server-side handler; client-side validation only
  $('#loginForm').on('submit', function(e) {
    // perform small client-side checks and allow the form to submit normally
    let isValid = true;
    const email = $('#loginEmail').val().trim();
    const password = $('#loginPassword').val();

    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    if (!email) {
      $('#loginEmail').addClass('is-invalid');
      if (!$('#loginEmail').siblings('.invalid-feedback').length) {
        $('#loginEmail').after('<div class="invalid-feedback">This field is required</div>');
      }
      isValid = false;
    }
    if (!password) {
      $('#loginPassword').addClass('is-invalid');
      if (!$('#loginPassword').siblings('.invalid-feedback').length) {
        $('#loginPassword').after('<div class="invalid-feedback">This field is required</div>');
      }
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
    }
    // otherwise allow normal submit to login_handler.php which will set the session
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>