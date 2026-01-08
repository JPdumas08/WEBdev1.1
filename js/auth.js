// Lightweight auth behavior: preview & inline feedback
$(document).ready(function() {
  // Password toggle functionality - using event delegation for all password fields
  $(document).on('change', '#toggleLoginPassword', function() {
    const passwordField = $('#loginPassword');
    if (passwordField.length) {
      if (this.checked) {
        passwordField.attr('type', 'text');
      } else {
        passwordField.attr('type', 'password');
      }
    }
  });

  $(document).on('change', '#toggleRegisterPassword', function() {
    const passwordField = $('#registerPassword');
    if (passwordField.length) {
      if (this.checked) {
        passwordField.attr('type', 'text');
      } else {
        passwordField.attr('type', 'password');
      }
    }
  });

  $(document).on('change', '#toggleConfirmPassword', function() {
    const passwordField = $('#confirmPassword');
    if (passwordField.length) {
      if (this.checked) {
        passwordField.attr('type', 'text');
      } else {
        passwordField.attr('type', 'password');
      }
    }
  });

  // Live preview for non-sensitive fields
  function updatePreview() {
    const first = $('#firstName').val().trim();
    const last = $('#lastName').val().trim();
    const email = $('#registerEmail').val().trim();
    const username = $('#username').val().trim();

    if (first || last || email || username) {
      $('#registerPreview').show();
      $('#previewFirstLast').text((first || '') + (first && last ? ' ' : '') + (last || ''));
      $('#previewEmail').text(email ? 'Email: ' + email : '');
      $('#previewUsername').text(username ? 'Username: ' + username : '');
    } else {
      $('#registerPreview').hide();
    }
  }

  $('#firstName, #lastName, #registerEmail, #username').on('input', updatePreview);

  // Show inline feedback messages
  function showFeedback(message, type) {
    // type: 'success' | 'danger' | 'info'
    const cls = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
    $('#registerFeedback').html('<div class="alert ' + cls + ' small mb-0">' + message + '</div>');
  }

  // Intercept form submit to show feedback (server integration will replace this)
  $('#registerForm').on('submit', function(e) {
    // If a server endpoint exists, normal submit will proceed; otherwise, prevent and simulate
    const action = $(this).attr('action');
    if (!action || action === '#' || action === '') {
      e.preventDefault();
    }

    // simple client-side check (duplicate of existing validation)
    const firstName = $('#firstName').val().trim();
    const lastName = $('#lastName').val().trim();
    const email = $('#registerEmail').val().trim();
    const username = $('#username').val().trim();
    const password = $('#registerPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    const agree = $('#agreeTerms').is(':checked');

    if (!firstName || !lastName || !email || !username || !password || !confirmPassword) {
      showFeedback('Please fill in all required fields.', 'danger');
      return false;
    }
    if (password.length < 8) {
      showFeedback('Password must be at least 8 characters long.', 'danger');
      return false;
    }
    if (password !== confirmPassword) {
      showFeedback('Passwords do not match.', 'danger');
      return false;
    }
    if (!agree) {
      showFeedback('You must agree to the Terms and Conditions.', 'danger');
      return false;
    }

    // If action points to a server handler, allow form submit to proceed (so server can handle)
    if (action && action !== '#' && action !== '') {
      // Let the browser submit the form normally to the server
      showFeedback('Submitting registration to server...', 'info');
      return true; // allow submit
    }

    // Otherwise simulate success
    $('#registerForm button[type="submit"]').text('Creating Account...').prop('disabled', true);
    setTimeout(function() {
      showFeedback('Account created successfully! You can now sign in.', 'success');
      $('#registerForm')[0].reset();
      $('#registerPreview').hide();
      $('#registerForm button[type="submit"]').text('Create Account').prop('disabled', false);
    }, 1200);

    e.preventDefault();
    return false;
  });

  // AJAX login for modal form
  $(document).on('submit', '#loginForm', function(e) {
    // Check if this is the modal form (in header) vs the page form (in login.php)
    const $form = $(this);
    const $modal = $form.closest('.modal');
    
    // Only intercept modal login form, let page form submit normally
    if (!$modal.length) {
      return true; // Allow normal form submission on login page
    }
    
    e.preventDefault();
    const url = $form.attr('action') || 'login_handler.php';
    const data = $form.serialize();

    // disable form while submitting
    $form.find('button[type="submit"]').prop('disabled', true).text('Signing in...');

    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      dataType: 'json',
      timeout: 5000
    }).done(function(resp) {
      if (resp && resp.success) {
        // Show success toast notification
        const container = document.getElementById('customToastContainer') || 
          (() => {
            const c = document.createElement('div');
            c.id = 'customToastContainer';
            c.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(c);
            return c;
          })();
        
        const toast = document.createElement('div');
        toast.innerHTML = `
          <div class="alert-dismissible fade show text-white mb-3" role="alert" style="min-width: 350px; font-weight: 500; border-radius: 6px; background-color: #22c55e; border: none; padding: 1rem 1.25rem;">
            Login successful! Welcome back.
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
          </div>
        `;
        container.appendChild(toast);
        
        // Close modal and reload after showing notification
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance($modal[0]);
          if (modal) modal.hide();
          window.location.reload();
        }, 2000);
      } else {
        const msg = (resp && resp.error) ? resp.error : 'Login failed';
        // Clear previous errors
        $form.find('.alert-danger').remove();
        // Show error toast
        const container = document.getElementById('customToastContainer') || 
          (() => {
            const c = document.createElement('div');
            c.id = 'customToastContainer';
            c.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(c);
            return c;
          })();
        
        const errorToast = document.createElement('div');
        errorToast.innerHTML = `
          <div class="alert alert-danger alert-dismissible fade show text-white mb-3" role="alert" style="min-width: 350px; font-weight: 500;">
            <strong>Error!</strong> ${msg}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
          </div>
        `;
        container.appendChild(errorToast);
        
        $form.find('button[type="submit"]').prop('disabled', false).text('Sign In');
      }
    }).fail(function() {
      $form.find('button[type="submit"]').prop('disabled', false).text('Sign In');
      
      const container = document.getElementById('customToastContainer') || 
        (() => {
          const c = document.createElement('div');
          c.id = 'customToastContainer';
          c.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
          document.body.appendChild(c);
          return c;
        })();
      
      const errorToast = document.createElement('div');
      errorToast.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show text-white mb-3" role="alert" style="min-width: 350px; font-weight: 500;">
          <strong>Error!</strong> Connection error. Please try again.
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
      `;
      container.appendChild(errorToast);
    });
  });
});
