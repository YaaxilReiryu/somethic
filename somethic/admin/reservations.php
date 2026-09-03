<?php
$admin_title = 'Pickup Reservations — SOMETHIC Staff';
require_once __DIR__ . '/includes/header.php';

$success_msg = '';
$error_msg = '';

// Handle Status Updates and Deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $res_id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_status' && $res_id > 0) {
        $new_status = $_POST['status'] ?? 'Pending';
        
        try {
            // Fetch reservation details to check stock deduction status
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
            $stmt->execute([$res_id]);
            $res = $stmt->fetch();

            if ($res) {
                $qty = (int)$res['quantity'];
                $product_id = (int)$res['product_id'];
                $stock_deducted = (int)$res['stock_deducted'];

                // If marking as Completed or Confirmed and stock wasn't deducted, we can handle stock management
                if ($new_status === 'Completed' && $stock_deducted === 0) {
                    // Deduct stock from product
                    $pdo->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?")->execute([$qty, $product_id]);
                    $pdo->prepare("UPDATE reservations SET status = ?, stock_deducted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$new_status, $res_id]);
                    $success_msg = "Reservation #{$res_id} marked as Completed. Inventory stock updated accordingly.";
                } elseif ($new_status === 'Cancelled' && $stock_deducted === 1) {
                    // Restore stock if previously deducted
                    $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$qty, $product_id]);
                    $pdo->prepare("UPDATE reservations SET status = ?, stock_deducted = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$new_status, $res_id]);
                    $success_msg = "Reservation #{$res_id} marked as Cancelled. Restored {$qty} unit(s) back to inventory.";
                } else {
                    $pdo->prepare("UPDATE reservations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$new_status, $res_id]);
                    $success_msg = "Reservation #{$res_id} status updated to {$new_status}.";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    } elseif ($action === 'delete' && $res_id > 0) {
        try {
            $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$res_id]);
            $success_msg = "Reservation #{$res_id} deleted permanently.";
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// Search and Filter
$status_filter = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$res_highlight = (int)($_GET['filter'] ?? 0);

$query = "
    SELECT r.*, p.name AS product_name, p.price AS product_price, p.image AS product_image, p.stock AS current_stock
    FROM reservations r
    JOIN products p ON r.product_id = p.id
    WHERE 1=1
";
$params = [];

if ($status_filter !== 'all' && !empty($status_filter)) {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (r.customer_name LIKE ? OR r.phone LIKE ? OR r.email LIKE ? OR p.name LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$query .= " ORDER BY r.pickup_date ASC, r.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();

    // Counts for tabs
    $count_all = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    $count_pending = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Pending'")->fetchColumn();
    $count_confirmed = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Confirmed'")->fetchColumn();
    $count_completed = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Completed'")->fetchColumn();
    $count_cancelled = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Cancelled'")->fetchColumn();
} catch (PDOException $e) {
    $reservations = [];
    $error_msg = "Error loading reservations: " . $e->getMessage();
}
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Customer Pickup Reservations</h3>
    <p class="text-muted small mb-0">
      Manage boutique handbag reservations, confirm customer appointment slots, and update pickup statuses.
    </p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="../reservation.php" target="_blank" class="btn btn-somethic-outline btn-sm">
      <i class="bi bi-box-arrow-up-right me-1"></i> Customer Booking Form
    </a>
  </div>
</div>

<!-- Alerts -->
<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($success_msg); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error_msg); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!-- Filter & Search Controls -->
<div class="card-somethic p-3 mb-4">
  <div class="row g-3 align-items-center justify-content-between">
    <!-- Status Tabs -->
    <div class="col-md-7">
      <div class="nav nav-pills gap-1" style="font-size: 0.8rem;">
        <a href="reservations.php?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="nav-link px-3 py-1 <?php echo $status_filter === 'all' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
          All (<?php echo (int)$count_all; ?>)
        </a>
        <a href="reservations.php?status=Pending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="nav-link px-3 py-1 <?php echo $status_filter === 'Pending' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
          Pending (<?php echo (int)$count_pending; ?>)
        </a>
        <a href="reservations.php?status=Confirmed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="nav-link px-3 py-1 <?php echo $status_filter === 'Confirmed' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
          Confirmed (<?php echo (int)$count_confirmed; ?>)
        </a>
        <a href="reservations.php?status=Completed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="nav-link px-3 py-1 <?php echo $status_filter === 'Completed' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
          Completed (<?php echo (int)$count_completed; ?>)
        </a>
        <a href="reservations.php?status=Cancelled<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="nav-link px-3 py-1 <?php echo $status_filter === 'Cancelled' ? 'active bg-dark text-white' : 'text-dark bg-light'; ?>" style="border-radius: 20px;">
          Cancelled (<?php echo (int)$count_cancelled; ?>)
        </a>
      </div>
    </div>

    <!-- Search Box -->
    <div class="col-md-5">
      <form method="GET" action="reservations.php" class="d-flex gap-2">
        <?php if ($status_filter !== 'all'): ?>
          <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        <?php endif; ?>
        <div class="input-group input-group-sm">
          <input type="text" name="search" class="form-control form-control-somethic" placeholder="Search customer, phone, bag..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-somethic-dark">
            <i class="bi bi-search"></i>
          </button>
        </div>
        <?php if (!empty($search)): ?>
          <a href="reservations.php?status=<?php echo urlencode($status_filter); ?>" class="btn btn-sm btn-light border" title="Clear Search">
            <i class="bi bi-x-lg"></i>
          </a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- Reservations Table Card -->
<div class="card-somethic p-4">
  <?php if (empty($reservations)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
      <h6 class="fw-semibold mb-1">No reservations found</h6>
      <p class="small text-muted mb-0">Try changing the status filter or search query.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
        <thead>
          <tr class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">
            <th>ID</th>
            <th>Customer Information</th>
            <th>Reserved Bag</th>
            <th>Pickup Schedule</th>
            <th>Status</th>
            <th class="text-end">Update Status / Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservations as $r): ?>
            <?php 
              $is_hl = ($res_highlight === (int)$r['id']);
              $status = $r['status'];
              $badgeClass = 'badge-status-pending';
              if ($status === 'Confirmed') $badgeClass = 'badge-status-confirmed';
              if ($status === 'Completed') $badgeClass = 'badge-status-completed';
              if ($status === 'Cancelled') $badgeClass = 'badge-status-cancelled';
            ?>
            <tr class="<?php echo $is_hl ? 'table-warning' : ''; ?>">
              <td>
                <span class="font-monospace fw-bold text-muted">#<?php echo $r['id']; ?></span>
              </td>
              <td>
                <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['customer_name']); ?></div>
                <div class="text-muted small">
                  <a href="tel:<?php echo htmlspecialchars($r['phone']); ?>" class="text-decoration-none text-muted me-2">
                    <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($r['phone']); ?>
                  </a>
                </div>
                <div class="text-muted small">
                  <a href="mailto:<?php echo htmlspecialchars($r['email']); ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($r['email']); ?>
                  </a>
                </div>
                <?php if (!empty($r['note'])): ?>
                  <div class="mt-1 p-1 px-2 rounded bg-light border text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-chat-left-text me-1"></i> <?php echo htmlspecialchars($r['note']); ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="../<?php echo htmlspecialchars($r['product_image']); ?>" alt="" style="width: 42px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--somethic-border);">
                  <div>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['product_name']); ?></div>
                    <div class="text-muted small">
                      $<?php echo number_format($r['product_price'], 2); ?> &times; <?php echo (int)$r['quantity']; ?> = 
                      <strong class="text-dark">$<?php echo number_format($r['product_price'] * $r['quantity'], 2); ?></strong>
                    </div>
                    <div style="font-size: 0.7rem;" class="<?php echo $r['current_stock'] > 0 ? 'text-success' : 'text-danger'; ?>">
                      Boutique Stock: <?php echo (int)$r['current_stock']; ?> units
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="fw-semibold text-dark">
                  <i class="bi bi-calendar3 me-1 text-muted"></i>
                  <?php echo date('D, M d, Y', strtotime($r['pickup_date'])); ?>
                </div>
                <div class="text-muted small">
                  <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($r['pickup_time']); ?>
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">
                  Booked: <?php echo date('M d, H:i', strtotime($r['created_at'])); ?>
                </div>
              </td>
              <td>
                <span class="badge <?php echo $badgeClass; ?>" style="font-size: 0.75rem;">
                  <?php echo htmlspecialchars($status); ?>
                </span>
                <?php if ((int)$r['stock_deducted'] === 1): ?>
                  <div class="text-muted" style="font-size: 0.65rem; margin-top: 2px;">
                    <i class="bi bi-box-seam me-1"></i>Stock Deducted
                  </div>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <!-- Status Action Dropdown Form -->
                <div class="d-inline-flex align-items-center gap-1">
                  <form method="POST" action="reservations.php" class="d-inline">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                    <select name="status" class="form-select form-select-sm d-inline-block w-auto py-1" style="font-size: 0.78rem;" onchange="this.form.submit()">
                      <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                      <option value="Confirmed" <?php echo $status === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                      <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed (Picked Up)</option>
                      <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </form>

                  <!-- Delete Button -->
                  <form method="POST" action="reservations.php" class="d-inline" onsubmit="return confirm('Delete reservation record #<?php echo $r['id']; ?>?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-light border-0 text-danger p-1 px-2" title="Delete record">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
