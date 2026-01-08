<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();

// Handle redirect after successful login
if (isset($_GET['login']) && $_GET['login'] == 'ok' && isset($_GET['redirect']) && !empty($_SESSION['user'])) {
    $redirect_url = $_GET['redirect'];
    // Show notification briefly then redirect
    echo '<script>
        setTimeout(function() {
            window.location.href = "' . htmlspecialchars($redirect_url) . '";
        }, 2000);
    </script>';
}

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
<h3 class="text-center mb-4"><img src="image/user.svg" alt="Account" class="me-2" style="height:1.25em; width:1.25em; vertical-align:middle;">Account Login</h3>
            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
              <div class="alert alert-success text-center">Your account was created. Please sign in.</div>
            <?php endif; ?>
            <?php if (isset($_GET['login'])): ?>
              <?php if ($_GET['login'] == 'ok'): ?>
                <div class="alert alert-success text-center">Login successful! Welcome back.</div>
              <?php elseif ($_GET['login'] == 'missing'): ?>
                <div class="alert alert-danger text-center">Please fill in all required fields.</div>
              <?php elseif ($_GET['login'] == 'notfound'): ?>
                <div class="alert alert-danger text-center">User not found. Please check your credentials.</div>
              <?php elseif ($_GET['login'] == 'bad'): ?>
                <div class="alert alert-danger text-center">Invalid password. Please try again.</div>
              <?php elseif ($_GET['login'] == 'err'): ?>
                <div class="alert alert-danger text-center">An error occurred. Please try again.</div>
              <?php endif; ?>
            <?php endif; ?>
            <?php
            // Check if user just logged in (session has user but no login parameter)
            if (isset($_SESSION['user']) && !isset($_GET['login']) && basename($_SERVER['HTTP_REFERER'] ?? '') != 'login.php') {
                echo '<div class="alert alert-success text-center">Login successful! Welcome back, ' . htmlspecialchars($_SESSION['user']['first_name'] ?? $_SESSION['user']['username'] ?? 'User') . '.</div>';
            }
            ?>
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
                </div>
                <div class="form-check mt-2">
                  <input type="checkbox" class="form-check-input" id="toggleLoginPassword">
                  <label class="form-check-label" for="toggleLoginPassword">Show Password</label>
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

?>
<script>
$(document).ready(function() {
  // Auto-trim spaces on input
  $('input[type="text"], input[type="email"]').on('blur', function() {
    $(this).val($(this).val().trim());
  });
  
  // Disable browser validation messages
  $('input[required]').on('invalid', function(e) {
    e.preventDefault();
  });
  
  // Function to show notification
  function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = $('<div class="alert ' + alertClass + ' text-center mb-3">' + message + '</div>');
    $('.contact-form-card h3').after(notification);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
      notification.fadeOut(500, function() {
        $(this).remove();
      });
    }, 5000);
  }
  
  // Check for URL parameters and show notifications
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('login') === 'ok') {
    showNotification('Login successful! Welcome back.', 'success');
  } else if (urlParams.get('login') === 'missing') {
    showNotification('Please fill in all required fields.', 'danger');
  } else if (urlParams.get('login') === 'notfound') {
    showNotification('User not found. Please check your credentials.', 'danger');
  } else if (urlParams.get('login') === 'bad') {
    showNotification('Invalid password. Please try again.', 'danger');
  } else if (urlParams.get('login') === 'err') {
    showNotification('An error occurred. Please try again.', 'danger');
  }
  
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