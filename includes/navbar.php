<?php
// Navbar include — outputs the site navigation bar
// Assumes includes/auth.php is available and session can be initialized
require_once __DIR__ . '/auth.php';
init_session();

$user = current_user();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand text-start" href="home.php">← Jeweluxe</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contactus.php">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cartModal">🛒 Cart</a></li>
        <?php if (!empty($user)): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              👤 <?= htmlspecialchars($user['first_name'] ?? $user['username'] ?? 'Account') ?></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">👤 Account</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
