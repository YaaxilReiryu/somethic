<?php
$admin_current = basename($_SERVER['PHP_SELF']);
?>
<div class="card-somethic p-3">
  <div class="text-uppercase small text-muted fw-bold px-3 py-2" style="font-size: 0.68rem; letter-spacing: 0.1em;">
    Store Operations
  </div>
  <nav class="nav flex-column">
    <a href="dashboard.php" class="admin-nav-link <?php echo ($admin_current === 'dashboard.php') ? 'active' : ''; ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="tasks.php" class="admin-nav-link <?php echo ($admin_current === 'tasks.php') ? 'active' : ''; ?>">
      <i class="bi bi-check2-square"></i> Inventory Tasks (To-Do)
    </a>
    <a href="reservations.php" class="admin-nav-link <?php echo ($admin_current === 'reservations.php') ? 'active' : ''; ?>">
      <i class="bi bi-calendar-event"></i> Pickup Reservations
    </a>
  </nav>

  <div class="text-uppercase small text-muted fw-bold px-3 py-2 mt-3" style="font-size: 0.68rem; letter-spacing: 0.1em;">
    Product Catalog
  </div>
  <nav class="nav flex-column">
    <a href="products.php" class="admin-nav-link <?php echo ($admin_current === 'products.php' || $admin_current === 'edit_product.php') ? 'active' : ''; ?>">
      <i class="bi bi-bag"></i> Handbag Stock
    </a>
    <a href="add_product.php" class="admin-nav-link <?php echo ($admin_current === 'add_product.php') ? 'active' : ''; ?>">
      <i class="bi bi-plus-circle"></i> Add New Handbag
    </a>
  </nav>

  <div class="text-uppercase small text-muted fw-bold px-3 py-2 mt-3" style="font-size: 0.68rem; letter-spacing: 0.1em;">
    Account & Resume
  </div>
  <nav class="nav flex-column">
    <a href="profile.php" class="admin-nav-link <?php echo ($admin_current === 'profile.php') ? 'active' : ''; ?>">
      <i class="bi bi-person-badge"></i> Edit Staff Profile
    </a>
    <a href="logout.php" class="admin-nav-link text-danger mt-2">
      <i class="bi bi-box-arrow-right"></i> Sign Out
    </a>
  </nav>
</div>
