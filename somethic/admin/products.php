<?php
$admin_title = 'Handbag Stock Catalog — SOMETHIC Staff';
require_once __DIR__ . '/includes/header.php';

$success_msg = '';
$error_msg = '';

// Handle Actions: Quick Stock Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['id'] ?? 0);

    if ($action === 'quick_stock' && $product_id > 0) {
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        try {
            $stmt = $pdo->prepare("UPDATE products SET stock = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$stock, $product_id]);
            $success_msg = "Stock level updated to {$stock} units.";
        } catch (PDOException $e) {
            $error_msg = "Error updating stock: " . $e->getMessage();
        }
    } elseif ($action === 'delete' && $product_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $success_msg = "Handbag removed from catalog.";
        } catch (PDOException $e) {
            $error_msg = "Error deleting product: " . $e->getMessage();
        }
    }
}

// Search and Category Filter
$category = $_GET['category'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category !== 'all' && !empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get categories for filter dropdown
    $categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
    $total_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $low_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();
    $out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn();
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $error_msg = "Error loading products: " . $e->getMessage();
}
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Handbag Inventory Stock</h3>
    <p class="text-muted small mb-0">
      Manage pricing, stock counts, luxury categories, and availability across the retail boutique.
    </p>
  </div>
  <div class="mt-3 mt-md-0 d-flex gap-2">
    <a href="add_product.php" class="btn btn-somethic-dark btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Add New Handbag
    </a>
    <a href="../shop.php" target="_blank" class="btn btn-somethic-outline btn-sm">
      <i class="bi bi-box-arrow-up-right me-1"></i> Customer Shop
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

<!-- Filter & Search Bar -->
<div class="card-somethic p-3 mb-4">
  <div class="row g-3 align-items-center justify-content-between">
    <div class="col-md-7 d-flex flex-wrap align-items-center gap-2">
      <div class="small fw-semibold text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Categories:</div>
      <a href="products.php?category=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
         class="btn btn-sm py-1 px-3 rounded-pill <?php echo $category === 'all' ? 'btn-dark' : 'btn-light border'; ?>" style="font-size: 0.75rem;">
        All (<?php echo (int)$total_count; ?>)
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="products.php?category=<?php echo urlencode($cat); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="btn btn-sm py-1 px-3 rounded-pill <?php echo $category === $cat ? 'btn-dark' : 'btn-light border'; ?>" style="font-size: 0.75rem;">
          <?php echo htmlspecialchars($cat); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="col-md-5">
      <form method="GET" action="products.php" class="d-flex gap-2">
        <?php if ($category !== 'all'): ?>
          <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
        <?php endif; ?>
        <div class="input-group input-group-sm">
          <input type="text" name="search" class="form-control form-control-somethic" placeholder="Search bag name or description..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-somethic-dark">
            <i class="bi bi-search"></i>
          </button>
        </div>
        <?php if (!empty($search)): ?>
          <a href="products.php?category=<?php echo urlencode($category); ?>" class="btn btn-sm btn-light border" title="Clear Search">
            <i class="bi bi-x-lg"></i>
          </a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- Products Table -->
<div class="card-somethic p-4">
  <?php if (empty($products)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-bag-x fs-1 d-block mb-2 text-muted"></i>
      <h6 class="fw-semibold mb-1">No handbags found</h6>
      <p class="small text-muted mb-0">Try clearing the search or category filter, or add a new handbag.</p>
      <a href="add_product.php" class="btn btn-somethic-dark btn-sm mt-3">Add New Handbag</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
        <thead>
          <tr class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">
            <th>Product</th>
            <th>Category</th>
            <th>Retail Price</th>
            <th>Stock Level</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <?php 
              $stock = (int)$p['stock'];
              $is_low = ($stock > 0 && $stock <= 5);
              $is_out = ($stock === 0);
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid var(--somethic-border);">
                  <div>
                    <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($p['name']); ?></div>
                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                      <?php echo htmlspecialchars($p['description']); ?>
                    </small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                  <?php echo htmlspecialchars($p['category']); ?>
                </span>
              </td>
              <td>
                <span class="fw-bold text-dark">$<?php echo number_format($p['price'], 2); ?></span>
              </td>
              <td>
                <!-- Quick Stock Update Form -->
                <form method="POST" action="products.php" class="d-inline-flex align-items-center gap-1">
                  <input type="hidden" name="action" value="quick_stock">
                  <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                  <input type="number" name="stock" value="<?php echo $stock; ?>" min="0" max="999" class="form-control form-control-sm text-center" style="width: 65px; font-size: 0.8rem;">
                  <button type="submit" class="btn btn-sm btn-light border py-1 px-2" title="Save Stock Count" style="font-size: 0.75rem;">
                    <i class="bi bi-check2"></i>
                  </button>
                </form>
              </td>
              <td>
                <?php if ($is_out): ?>
                  <span class="badge bg-danger text-white" style="font-size: 0.75rem;">Out of Stock</span>
                <?php elseif ($is_low): ?>
                  <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">Low Stock (<?php echo $stock; ?>)</span>
                <?php else: ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.75rem;">In Stock (<?php echo $stock; ?>)</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="../product_details.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-light border py-1 px-2" title="Preview on Storefront" style="font-size: 0.75rem;">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-somethic-outline py-1 px-2" title="Edit Bag Details" style="font-size: 0.75rem;">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  <form method="POST" action="products.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($p['name'])); ?>?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-light text-danger border py-1 px-2" title="Delete Handbag" style="font-size: 0.75rem;">
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
