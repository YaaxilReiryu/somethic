<?php
$admin_title = 'Store Operations Dashboard — SOMETHIC Staff';
require_once __DIR__ . '/includes/header.php';

// Fetch statistics
try {
    // Total Products
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Low Stock Products (stock <= 5)
    $low_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();
    $low_stock_items = $pdo->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 6")->fetchAll();

    // Total and Pending Reservations
    $total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    $pending_reservations_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Pending'")->fetchColumn();

    // Pending Inventory Tasks
    $pending_tasks_count = $pdo->query("SELECT COUNT(*) FROM inventory_tasks WHERE status = 'Pending'")->fetchColumn();

    // Recent 5 Reservations
    $recent_reservations = $pdo->query("
        SELECT r.*, p.name AS product_name, p.image AS product_image, p.price AS product_price
        FROM reservations r
        JOIN products p ON r.product_id = p.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Recent 5 Tasks
    $recent_tasks = $pdo->query("
        SELECT * FROM inventory_tasks
        ORDER BY status DESC, created_at DESC
        LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    $error = "Error fetching dashboard data: " . $e->getMessage();
}
?>

<!-- Dashboard Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Store Operations Dashboard</h3>
    <p class="text-muted small mb-0">
      Welcome back, <strong><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'Staff'); ?></strong>. Here is today's handbag boutique overview.
    </p>
  </div>
  <div class="mt-3 mt-md-0 d-flex gap-2">
    <a href="add_product.php" class="btn btn-somethic-dark btn-sm px-3 py-2">
      <i class="bi bi-plus-circle me-1"></i> Add Handbag
    </a>
    <a href="tasks.php" class="btn btn-somethic-outline btn-sm px-3 py-2">
      <i class="bi bi-check2-square me-1"></i> Manage Tasks
    </a>
    <a href="reservations.php" class="btn btn-somethic-outline btn-sm px-3 py-2">
      <i class="bi bi-calendar-check me-1"></i> Reservations
    </a>
  </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card-somethic p-3 d-flex flex-row align-items-center justify-content-between">
      <div>
        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Handbags</span>
        <h3 class="fw-bold mb-0 mt-1"><?php echo (int)$total_products; ?></h3>
        <small class="text-muted" style="font-size: 0.75rem;">In catalog</small>
      </div>
      <div class="rounded-3 p-3 bg-light text-dark">
        <i class="bi bi-bag" style="font-size: 1.5rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card-somethic p-3 d-flex flex-row align-items-center justify-content-between">
      <div>
        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Pending Reservations</span>
        <h3 class="fw-bold mb-0 mt-1 <?php echo $pending_reservations_count > 0 ? 'text-warning' : 'text-dark'; ?>">
          <?php echo (int)$pending_reservations_count; ?>
        </h3>
        <small class="text-muted" style="font-size: 0.75rem;">Awaiting store confirmation</small>
      </div>
      <div class="rounded-3 p-3 bg-warning-subtle text-warning">
        <i class="bi bi-clock-history" style="font-size: 1.5rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card-somethic p-3 d-flex flex-row align-items-center justify-content-between">
      <div>
        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Low Stock Alerts</span>
        <h3 class="fw-bold mb-0 mt-1 <?php echo $low_stock_count > 0 ? 'text-danger' : 'text-success'; ?>">
          <?php echo (int)$low_stock_count; ?>
        </h3>
        <small class="text-muted" style="font-size: 0.75rem;">Items with &le; 5 in stock</small>
      </div>
      <div class="rounded-3 p-3 bg-danger-subtle text-danger">
        <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card-somethic p-3 d-flex flex-row align-items-center justify-content-between">
      <div>
        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Pending Tasks</span>
        <h3 class="fw-bold mb-0 mt-1"><?php echo (int)$pending_tasks_count; ?></h3>
        <small class="text-muted" style="font-size: 0.75rem;">To-do checklist items</small>
      </div>
      <div class="rounded-3 p-3 bg-info-subtle text-primary">
        <i class="bi bi-list-check" style="font-size: 1.5rem;"></i>
      </div>
    </div>
  </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
  <!-- Recent Customer Reservations -->
  <div class="col-lg-8">
    <div class="card-somethic p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-bold mb-0" style="font-family: var(--font-serif);">Recent Pickup Reservations</h5>
          <small class="text-muted">Customers reserving bags for in-store boutique viewing & pickup</small>
        </div>
        <a href="reservations.php" class="btn btn-sm btn-somethic-outline" style="font-size: 0.75rem;">
          View All (<?php echo (int)$total_reservations; ?>)
        </a>
      </div>

      <?php if (empty($recent_reservations)): ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-inbox fs-2 d-block mb-2"></i>
          <p class="mb-0 small">No reservations registered yet.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle table-hover mb-0" style="font-size: 0.85rem;">
            <thead>
              <tr class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">
                <th>Customer</th>
                <th>Reserved Bag</th>
                <th>Pickup Date</th>
                <th>Status</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_reservations as $r): ?>
                <tr>
                  <td>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['customer_name']); ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($r['phone']); ?></div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img src="../<?php echo htmlspecialchars($r['product_image']); ?>" alt="" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--somethic-border);">
                      <div>
                        <div class="fw-semibold text-truncate" style="max-width: 140px;"><?php echo htmlspecialchars($r['product_name']); ?></div>
                        <span class="text-muted" style="font-size: 0.72rem;">Qty: <?php echo (int)$r['quantity']; ?></span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div><?php echo date('M d, Y', strtotime($r['pickup_date'])); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($r['pickup_time']); ?></small>
                  </td>
                  <td>
                    <?php
                      $status = $r['status'];
                      $badgeClass = 'badge-status-pending';
                      if ($status === 'Confirmed') $badgeClass = 'badge-status-confirmed';
                      if ($status === 'Completed') $badgeClass = 'badge-status-completed';
                      if ($status === 'Cancelled') $badgeClass = 'badge-status-cancelled';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                  </td>
                  <td class="text-end">
                    <a href="reservations.php?filter=<?php echo urlencode($r['id']); ?>" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 0.75rem;">
                      Manage
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Inventory Tasks Checklist -->
    <div class="card-somethic p-4 mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-bold mb-0" style="font-family: var(--font-serif);">Store Daily Checklist</h5>
          <small class="text-muted">Inventory verification, shelf replenishment, and boutique maintenance</small>
        </div>
        <a href="tasks.php" class="btn btn-sm btn-somethic-outline" style="font-size: 0.75rem;">
          Open To-Do List
        </a>
      </div>

      <div class="list-group list-group-flush">
        <?php if (empty($recent_tasks)): ?>
          <div class="text-center py-3 text-muted small">No tasks created yet. Click "Open To-Do List" to add tasks!</div>
        <?php else: ?>
          <?php foreach ($recent_tasks as $task): ?>
            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom" style="border-color: var(--somethic-border) !important;">
              <div class="d-flex align-items-center gap-2">
                <i class="bi <?php echo $task['status'] === 'Completed' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'; ?>"></i>
                <div>
                  <span class="<?php echo $task['status'] === 'Completed' ? 'text-decoration-line-through text-muted' : 'fw-semibold text-dark'; ?>" style="font-size: 0.88rem;">
                    <?php echo htmlspecialchars($task['task']); ?>
                  </span>
                  <?php if (!empty($task['description'])): ?>
                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($task['description']); ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <span class="badge <?php echo $task['status'] === 'Completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>" style="font-size: 0.7rem;">
                <?php echo htmlspecialchars($task['status']); ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar Column: Low Stock Warnings & Quick Links -->
  <div class="col-lg-4">
    <!-- Low Stock Alert Card -->
    <div class="card-somethic p-4 mb-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0 text-danger" style="font-family: var(--font-serif);">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock Alerts
        </h6>
        <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;"><?php echo count($low_stock_items); ?></span>
      </div>

      <?php if (empty($low_stock_items)): ?>
        <div class="text-center py-3 text-muted small">
          <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
          All handbags currently have healthy inventory levels (&gt; 5 units).
        </div>
      <?php else: ?>
        <p class="small text-muted mb-3">
          The following handbags require shelf restocking or supplier replenishment:
        </p>
        <div class="list-group list-group-flush mb-3">
          <?php foreach ($low_stock_items as $item): ?>
            <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between" style="border-color: var(--somethic-border) !important;">
              <div class="d-flex align-items-center gap-2">
                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                <div>
                  <div class="fw-bold small text-dark"><?php echo htmlspecialchars($item['name']); ?></div>
                  <small class="text-muted"><?php echo htmlspecialchars($item['category']); ?></small>
                </div>
              </div>
              <div class="text-end">
                <?php if ($item['stock'] == 0): ?>
                  <span class="badge bg-danger" style="font-size: 0.7rem;">Out of Stock</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><?php echo (int)$item['stock']; ?> left</span>
                <?php endif; ?>
                <div>
                  <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="text-primary text-decoration-none" style="font-size: 0.72rem;">
                    Restock
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <a href="products.php" class="btn btn-sm btn-somethic-outline w-100 mt-2" style="font-size: 0.78rem;">
        View Full Stock Inventory
      </a>
    </div>

    <!-- Store Profile & Fast Info -->
    <div class="card-somethic p-4">
      <h6 class="fw-bold mb-3" style="font-family: var(--font-serif);">Staff On Duty</h6>
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; font-size: 1.1rem;">
          <?php echo strtoupper(substr($_SESSION['staff_name'] ?? 'A', 0, 1)); ?>
        </div>
        <div>
          <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'Staff Member'); ?></h6>
          <small class="text-muted"><?php echo htmlspecialchars($_SESSION['staff_role'] ?? 'Store Staff'); ?> &bull; <?php echo htmlspecialchars($_SESSION['staff_email'] ?? ''); ?></small>
        </div>
      </div>
      <p class="small text-muted mb-3">
        Your public resume and credentials are live on the customer-facing storefront to demonstrate verified boutique expertise.
      </p>
      <div class="d-grid gap-2">
        <a href="profile.php" class="btn btn-sm btn-somethic-outline" style="font-size: 0.78rem;">
          <i class="bi bi-pencil-square me-1"></i> Edit Staff Profile
        </a>
        <a href="../staff.php" target="_blank" class="btn btn-sm btn-light border" style="font-size: 0.78rem;">
          <i class="bi bi-box-arrow-up-right me-1"></i> Preview Public Resume
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
