<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
$pageTitle = 'Jeweluxe - Home';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- HERO -->
  <header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
    <div class="container">
      <h1 class="display-4">Welcome to Jeweluxe</h1>
      <p class="lead">Your journey to exquisite jewelry starts here!</p>
      <div class="mt-4">
        <a href="products.php" class="btn btn-primary btn-lg me-3">Explore Collection</a>
        <a href="about.php" class="btn btn-outline-light btn-lg">Learn More</a>
      </div>
    </div>
  </header>

  <!-- FEATURED PRODUCTS SECTION -->
  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-5">Featured Products</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body p-0">
              <video class="card-img-top" style="height: 500px; object-fit: cover;" autoplay muted loop playsinline>
                <source src="Video/necklace.mp4" type="video/mp4">
                Your browser does not support the video tag.
              </video>
              <div class="p-3 text-center">
                <h5 class="card-title">Elegant Necklaces</h5>
                <p class="card-text">Discover our stunning collection of necklaces crafted with precision and elegance.</p>
                <a href="products.php" class="btn btn-primary">View Collection</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body p-0">
              <video class="card-img-top" style="height: 500px; object-fit: cover;" autoplay muted loop playsinline>
                <source src="Video/earring.mp4" type="video/mp4">
                Your browser does not support the video tag.
              </video>
              <div class="p-3 text-center">
                <h5 class="card-title">Beautiful Earrings</h5>
                <p class="card-text">Adorn yourself with our exquisite earrings designed to complement your style.</p>
                <a href="products.php" class="btn btn-primary">View Collection</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body p-0">
              <video class="card-img-top" style="height: 500px; object-fit: cover;" autoplay muted loop playsinline>
                <source src="Video/bracelet.mp4" type="video/mp4">
                Your browser does not support the video tag.
              </video>
              <div class="p-3 text-center">
                <h5 class="card-title">Stylish Bracelets</h5>
                <p class="card-text">Complete your look with our sophisticated bracelets for every occasion.</p>
                <a href="products.php" class="btn btn-primary">View Collection</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK LINKS SECTION -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-5">Quick Access</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="fas fa-gem fa-3x text-primary mb-3"></i>
              <h5 class="card-title">Shop Jewelry</h5>
              <p class="card-text">Discover our stunning collection of rings, necklaces, earrings, and more.</p>
              <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="fas fa-info-circle fa-3x text-primary mb-3"></i>
              <h5 class="card-title">About Us</h5>
              <p class="card-text">Learn about our story, craftsmanship, and commitment to quality.</p>
              <a href="about.php" class="btn btn-primary">Learn More</a>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
              <h5 class="card-title">Contact Us</h5>
              <p class="card-text">Get in touch with our team for any questions or assistance.</p>
              <a href="contactus.php" class="btn btn-primary">Contact Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
