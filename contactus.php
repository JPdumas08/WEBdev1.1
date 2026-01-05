<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
$pageTitle = 'Jeweluxe - Contact Us';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Custom focus highlighting */
    .form-control:focus {
      border-color: #007bff !important;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
    
    /* Required field validation styling */
    .form-control.is-invalid {
      border-color: #dc3545;
    }
    
    .invalid-feedback {
      display: block;
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
    }
    
    /* Remove browser validation styling */
    .form-control:invalid {
      box-shadow: none !important;
    }
    
    .form-control:valid {
      box-shadow: none !important;
    }
    
    /* Hide browser validation messages */
    .form-control::-webkit-validation-bubble {
      display: none !important;
    }
    
    /* Contact info styling */
    .contact-info-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    
    .contact-info-card i {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #ffd700;
    }
    
    .contact-form-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
  </style>
  <div class="main-content">

    <header class="text-center text-white py-5 bg-dark" style="background:url('Video/wallpaper.jpg') center/cover no-repeat;">
    <div class="container">
      <h1 class="display-4">Contact Jeweluxe</h1>
      <p class="lead">Get in touch with us for any inquiries or support!</p>
    </div>
  </header>

  <section class="container py-5">
    <div class="row">
    
      <div class="col-lg-4 mb-4">
        <div class="contact-info-card text-center">
          <i class="fas fa-envelope"></i>
          <h4>Email Us</h4>
          <p class="mb-3">Send us an email and we'll respond within 24 hours</p>
          <a href="mailto:jeweluxe@gmail.com" class="btn btn-warning">
            <i class="fas fa-envelope me-2"></i>jeweluxe@gmail.com
          </a>
        </div>
        
        <div class="contact-info-card text-center">
          <i class="fas fa-clock"></i>
          <h4>Business Hours</h4>
          <p class="mb-2"><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
          <p class="mb-2"><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p>
          <p class="mb-0"><strong>Sunday:</strong> Closed</p>
        </div>
        
        <div class="contact-info-card text-center">
          <i class="fas fa-headset"></i>
          <h4>Customer Support</h4>
          <p class="mb-3">Our dedicated team is here to help you with any questions about our jewelry collection</p>
          <p class="mb-0"><strong>Response Time:</strong> Within 24 hours</p>
        </div>
      </div>
      
 
      <div class="col-lg-8">
        <div class="contact-form-card">
          <h3 class="mb-4 text-center">
            <i class="fas fa-paper-plane me-2"></i>Send us a Message
          </h3>
          
          <form id="contactForm" novalidate>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="contactFirstName" class="form-label">First Name</label>
                <input type="text" id="contactFirstName" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="contactLastName" class="form-label">Last Name</label>
                <input type="text" id="contactLastName" class="form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="contactEmail" class="form-label">Email</label>
              <input type="email" id="contactEmail" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="contactSubject" class="form-label">Subject</label>
              <select id="contactSubject" class="form-select" required>
                <option value="">Choose...</option>
                <option value="general">General Inquiry</option>
                <option value="order">Order Issue</option>
                <option value="returns">Returns</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="contactMessage" class="form-label">Message</label>
              <textarea id="contactMessage" class="form-control" rows="6" required></textarea>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" id="agreeContact" class="form-check-input" required>
              <label for="agreeContact" class="form-check-label">I agree to the privacy policy</label>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-primary">Send Message</button>
            </div>
          </form>

          <script>
          $(document).ready(function() {
            // Contact Form Validation and Submission
            $('#contactForm').on('submit', function(e) {
              e.preventDefault();

              // Mark form as submitted for validation tracking
              $(this).data('submitted', true);

              let isValid = true;
              const firstName = $('#contactFirstName').val().trim();
              const lastName = $('#contactLastName').val().trim();
              const email = $('#contactEmail').val().trim();
              const subject = $('#contactSubject').val();
              const message = $('#contactMessage').val().trim();

              // Clear previous validation
              $('.is-invalid').removeClass('is-invalid');
              $('.invalid-feedback').remove();

              // Validate First Name
              if (!firstName) {
                $('#contactFirstName').addClass('is-invalid');
                $('#contactFirstName').after('<div class="invalid-feedback">This field is required</div>');
                isValid = false;
              }

              // Validate Last Name
              if (!lastName) {
                $('#contactLastName').addClass('is-invalid');
                $('#contactLastName').after('<div class="invalid-feedback">This field is required</div>');
                isValid = false;
              }

              // Validate Email
              if (!email) {
                $('#contactEmail').addClass('is-invalid');
                $('#contactEmail').after('<div class="invalid-feedback">This field is required</div>');
                isValid = false;
              } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                  $('#contactEmail').addClass('is-invalid');
                  $('#contactEmail').after('<div class="invalid-feedback">Please enter a valid email address</div>');
                  isValid = false;
                }
              }

              // Validate Subject
              if (!subject) {
                $('#contactSubject').addClass('is-invalid');
                $('#contactSubject').after('<div class="invalid-feedback">Please select a subject</div>');
                isValid = false;
              }

              // Validate Message
              if (!message) {
                $('#contactMessage').addClass('is-invalid');
                $('#contactMessage').after('<div class="invalid-feedback">This field is required</div>');
                isValid = false;
              } else if (message.length < 10) {
                $('#contactMessage').addClass('is-invalid');
                $('#contactMessage').after('<div class="invalid-feedback">Message must be at least 10 characters long</div>');
                isValid = false;
              }

              // Validate Privacy Policy Agreement
              if (!$('#agreeContact').is(':checked')) {
                $('#agreeContact').addClass('is-invalid');
                if (!$('#agreeContact').siblings('.invalid-feedback').length) {
                  $('#agreeContact').after('<div class="invalid-feedback">You must agree to the privacy policy</div>');
                }
                isValid = false;
              } else {
                $('#agreeContact').removeClass('is-invalid');
                $('#agreeContact').siblings('.invalid-feedback').remove();
              }

              if (isValid) {
                // Simulate sending message
                $('#contactForm button[type="submit"]').text('Sending...').prop('disabled', true);

                setTimeout(() => {
                  ToastNotification.success('Message sent successfully! We will get back to you within 24 hours.');
                  $('#contactForm')[0].reset();
                  $('#contactForm button[type="submit"]').text('Send Message').prop('disabled', false);
                  $('.is-invalid').removeClass('is-invalid');
                  $('.invalid-feedback').remove();
                  // Reset form submission tracking
                  $('#contactForm').removeData('submitted');
                }, 1200);
              }
            });

            // Auto-trim spaces on input
            $('input[type="text"], input[type="email"], textarea').on('blur', function() {
              $(this).val($(this).val().trim());
            });

            // Disable browser validation messages
            $('input[required]').on('invalid', function(e) {
              e.preventDefault();
            });
          });
          </script>

        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
