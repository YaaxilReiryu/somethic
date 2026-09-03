<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($admin_title)) {
    $admin_title = 'Staff Dashboard — SOMETHIC';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($admin_title); ?></title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom Sleek Theme -->
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--somethic-sand);">

<!-- Admin Top Navbar -->
<nav class="navbar navbar-expand-lg bg-white border-bottom py-2" style="border-color: var(--somethic-border) !important;">
  <div class="container-fluid px-4">
    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <span class="brand-logo-mark" style="width: 32px; height: 32px; font-size: 1.1rem;">S</span>
      <span class="brand-logo-text" style="font-size: 1.25rem;">SOMETHIC</span>
      <span class="badge badge-status-pending ms-2" style="font-size: 0.65rem;">STAFF PORTAL</span>
    </a>

    <!-- Top Bar User Info & Front Link -->
    <div class="d-flex align-items-center gap-3">
      <a href="../index.php" target="_blank" class="btn btn-sm btn-somethic-outline py-1 px-3 d-none d-sm-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
        <i class="bi bi-box-arrow-up-right"></i> View Storefront
      </a>

      <div class="dropdown">
        <button class="btn btn-sm d-flex align-items-center gap-2 border rounded-pill px-3 py-1 bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-color: var(--somethic-border) !important;">
          <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.75rem;">
            <?php echo strtoupper(substr($_SESSION['staff_name'] ?? 'A', 0, 1)); ?>
          </div>
          <span class="small fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'Staff'); ?></span>
          <i class="bi bi-chevron-down small text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem;">
          <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2 text-warning"></i> My Staff Profile</a></li>
          <li><a class="dropdown-item py-2" href="../staff.php" target="_blank"><i class="bi bi-file-earmark-person me-2 text-warning"></i> Public Resume Page</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid px-4 py-4">
  <div class="row g-4">
    <!-- Sidebar Column -->
    <div class="col-lg-2 col-md-3">
      <?php require_once __DIR__ . '/sidebar.php'; ?>
    </div>

    <!-- Main Content Column -->
    <div class="col-lg-10 col-md-9">
