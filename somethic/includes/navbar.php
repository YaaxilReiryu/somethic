<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = !empty($_SESSION['staff_logged_in']);
?>
<nav class="navbar navbar-expand-lg navbar-somethic sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <span class="brand-logo-mark">S</span>
      <span class="brand-logo-text">SOMETHIC</span>
    </a>

    <!-- Mobile Hamburger Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSomethicNav" aria-controls="navbarSomethicNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Items -->
    <div class="collapse navbar-collapse" id="navbarSomethicNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'shop.php') ? 'active' : ''; ?>" href="shop.php">Shop</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'products.php' || $current_page === 'product_details.php') ? 'active' : ''; ?>" href="products.php">Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'reservation.php' || $current_page === 'reservation_success.php') ? 'active' : ''; ?>" href="reservation.php">Reservation</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'staff.php') ? 'active' : ''; ?>" href="staff.php">Staff</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="shop.php#contact">Contact</a>
        </li>
      </ul>

      <!-- Action Button -->
      <div class="d-flex align-items-center gap-2">
        <?php if ($is_logged_in): ?>
          <a href="admin/dashboard.php" class="btn btn-nav-admin d-inline-flex align-items-center gap-1">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <a href="admin/logout.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.75rem;">
            Logout
          </a>
        <?php else: ?>
          <a href="admin/login.php" class="btn btn-nav-admin d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-lock"></i> Staff Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
