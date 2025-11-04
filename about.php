<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';
init_session();
$pageTitle = 'Jeweluxe - About';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- HERO -->
  <header class="text-center text-white py-5 bg-dark" style="background:url(Video/wallpaper.jpg) center/cover no-repeat;">
    <div class="container">
      <h1 class="display-4">Shine Bright with Jeweluxe</h1>
      <p class="lead">Discover timeless jewelry for every occasion!</p>
    </div>
    </header>

  <!-- ABOUT CONTENT -->
  <main class="container py-5">
    <!-- About Jeweluxe Section -->
    <section class="row mb-5">
      <div class="col-lg-6">
        <h2 class="mb-4">About Jeweluxe</h2>
        <p class="lead">Welcome to Jeweluxe, where elegance meets craftsmanship. We are passionate about creating exquisite jewelry pieces that celebrate life's most precious moments.</p>
        <p>Founded with a vision to bring timeless beauty to every jewelry lover, Jeweluxe specializes in handcrafted pieces that combine traditional techniques with contemporary design. Each piece in our collection tells a story of artistry, quality, and attention to detail.</p>
      </div>
      <div class="col-lg-6">
        <img src="Video/wallpaper.jpg" alt="Jeweluxe Collection" class="img-fluid rounded shadow">
      </div>
    </section>

    <!-- Our Product Collections -->
    <section class="mb-5">
      <h2 class="text-center mb-5">Our Exquisite Collections</h2>
      
      <!-- Necklaces Collection -->
      <div class="row mb-5">
        <div class="col-md-4">
          <div class="text-center">
            <img src="Video/Bracelet/necklotus.jpg" alt="Lotus Necklace" class="img-fluid rounded mb-3" style="height: 200px; object-fit: cover; width: 100%;">
          </div>
        </div>
        <div class="col-md-8">
          <h3 class="text-primary">Necklace Collection</h3>
          <p><strong>Our signature necklaces are designed to make every moment special.</strong></p>
          <ul class="list-unstyled">
            <li><strong>🌸 Lotus Necklace (₱2,345.00)</strong> - Inspired by the lotus flower's symbolism of purity and enlightenment, this delicate piece features intricate lotus petal details crafted in premium gold-tone metal.</li>
            <br>
            <li><strong>🤍 Pearl Necklace (₱1,600.00)</strong> - Classic elegance meets modern sophistication. Our pearl necklace features lustrous freshwater pearls perfectly matched for color and size, creating a timeless piece perfect for any occasion.</li>
            <br>
            <li><strong>🔗 Necklace Tied Knot (₱2,499.00)</strong> - A contemporary design representing eternal bonds and infinite love. The tied knot pendant symbolizes unbreakable connections and is perfect for gifting to someone special.</li>
          </ul>
        </div>
      </div>

      <!-- Bracelets Collection -->
      <div class="row mb-5">
        <div class="col-md-8">
          <h3 class="text-primary">Bracelet Collection</h3>
          <p><strong>Elegant wrist accessories that complement your unique style.</strong></p>
          <ul class="list-unstyled">
            <li><strong>🌸 Lotus Bracelet (₱2,345.00)</strong> - Matching our popular lotus necklace, this bracelet brings the same spiritual symbolism and intricate craftsmanship to your wrist. Perfect for stacking or wearing alone.</li>
            <br>
            <li><strong>🤍 Pearl Bracelet (₱1,600.00)</strong> - Sophisticated and versatile, featuring the same premium freshwater pearls as our necklace collection. Ideal for both casual and formal occasions.</li>
            <br>
            <li><strong>🔗 Tied Knot Bracelet (₱1,800.00)</strong> - A delicate interpretation of our signature knot design, this bracelet adds a touch of meaningful elegance to any outfit while representing the bonds that matter most.</li>
          </ul>
        </div>
        <div class="col-md-4">
          <div class="text-center">
            <img src="Video/Bracelet/lotusbrace.jpg" alt="Lotus Bracelet" class="img-fluid rounded mb-3" style="height: 200px; object-fit: cover; width: 100%;">
          </div>
        </div>
      </div>

      <!-- Earrings Collection -->
      <div class="row mb-5">
        <div class="col-md-4">
          <div class="text-center">
            <img src="Video/Bracelet/earlotus.jpg" alt="Lotus Earrings" class="img-fluid rounded mb-3" style="height: 200px; object-fit: cover; width: 100%;">
          </div>
        </div>
        <div class="col-md-8">
          <h3 class="text-primary">Earring Collection</h3>
          <p><strong>Complete your look with our stunning earring designs.</strong></p>
          <ul class="list-unstyled">
            <li><strong>🌸 Lotus Earrings (₱2,345.00)</strong> - Delicate drop earrings featuring our signature lotus motif. These lightweight pieces add elegance without overwhelming your natural beauty.</li>
            <br>
            <li><strong>🤍 Pearl Earrings (₱1,600.00)</strong> - Classic pearl studs that never go out of style. These perfectly matched pearls offer understated elegance suitable for everyday wear or special occasions.</li>
            <br>
            <li><strong>🔗 Tied Earrings (₱1,600.00)</strong> - Unique knot-inspired earrings that add a modern twist to traditional jewelry. Perfect for those who appreciate contemporary design with meaningful symbolism.</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Quality & Craftsmanship -->
    <section class="mb-5">
      <div class="row">
        <div class="col-lg-12">
          <h2 class="text-center mb-4">Quality & Craftsmanship</h2>
          <div class="row">
            <div class="col-md-4 text-center mb-4">
              <div class="p-4 border rounded">
                <h4 class="text-primary">Premium Materials</h4>
                <p>We use only the finest materials including genuine freshwater pearls, high-quality metals, and carefully selected gemstones to ensure lasting beauty and durability.</p>
              </div>
            </div>
            <div class="col-md-4 text-center mb-4">
              <div class="p-4 border rounded">
                <h4 class="text-primary">Artisan Crafted</h4>
                <p>Each piece is carefully handcrafted by skilled artisans who pay attention to every detail, ensuring that your jewelry is not just beautiful, but also unique.</p>
              </div>
            </div>
            <div class="col-md-4 text-center mb-4">
              <div class="p-4 border rounded">
                <h4 class="text-primary">Timeless Design</h4>
                <p>Our designs blend classic elegance with contemporary style, creating pieces that will remain fashionable and cherished for years to come.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Jeweluxe -->
    <section class="mb-5 bg-light p-5 rounded">
      <h2 class="text-center mb-4">Why Choose Jeweluxe?</h2>
      <div class="row">
        <div class="col-lg-6">
          <ul class="list-unstyled">
            <li class="mb-3"><strong>Exceptional Quality:</strong> Every piece undergoes rigorous quality control to meet our high standards.</li>
            <li class="mb-3"><strong>Perfect for Gifting:</strong> Our jewelry comes beautifully packaged, making it ideal for special occasions.</li>
            <li class="mb-3"><strong>Secure Shopping:</strong> Safe and secure online shopping experience with multiple payment options.</li>
          </ul>
        </div>
        <div class="col-lg-6">
          <ul class="list-unstyled">
            <li class="mb-3"><strong>Fast Delivery:</strong> Quick and reliable shipping to get your jewelry to you safely and promptly.</li>
            <li class="mb-3"><strong>Customer Support:</strong> Dedicated customer service team ready to assist you with any questions.</li>
            <li class="mb-3"><strong>Satisfaction Guarantee:</strong> We stand behind our products with a comprehensive satisfaction guarantee.</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="text-center">
      <h2 class="mb-4">Ready to Find Your Perfect Piece?</h2>
      <p class="lead mb-4">Explore our complete collection and discover the jewelry that speaks to your heart.</p>
      <a href="products.php" class="btn btn-primary btn-lg me-3">Shop Our Collection</a>
      <a href="contactus.php" class="btn btn-outline-primary btn-lg">Contact Us</a>
    </section>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
