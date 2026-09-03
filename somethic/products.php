<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'Handbag Catalog — SOMETHIC Boutique';
require_once __DIR__ . '/includes/header.php';

// Retrieve filtering parameters
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Prepare SQL query with filters
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category_filter !== '') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if ($search_query !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    case 'stock_desc':
        $sql .= " ORDER BY stock DESC";
        break;
    default:
        $sql .= " ORDER BY id ASC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

// Available categories for filter buttons
$categories = ['Shoulder Bag', 'Tote Bag', 'Crossbody Bag', 'Handbag', 'Mini Bag'];
?>

<div class="container py-5">
  <!-- Header Title -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
      <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb small mb-0">
          <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
          <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Products</li>
        </ol>
      </nav>
      <h1 class="h2 mb-1">Handbag Catalog</h1>
      <p class="text-muted small mb-0">
        Browse our curated collection of affordable luxury handbags designed for everyday elegance.
      </p>
    </div>
    <div class="text-md-end">
      <span class="text-muted small">Showing <strong><?php echo count($products); ?></strong> styles</span>
    </div>
  </div>

  <!-- Search & Category Filters -->
  <div class="card-somethic p-3 mb-4">
    <form method="GET" action="products.php" class="row g-3 align-items-center">
      <!-- Search Input -->
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0" style="border-color: var(--somethic-border); color: var(--somethic-muted);">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by handbag name or keyword..." value="<?php echo htmlspecialchars($search_query); ?>" style="border-color: var(--somethic-border); font-size: 0.9rem;">
        </div>
      </div>

      <!-- Sorting Select -->
      <div class="col-md-4">
        <select name="sort" class="form-select" onchange="this.form.submit()" style="border-color: var(--somethic-border); font-size: 0.85rem;">
          <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Featured / Default</option>
          <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
          <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
          <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Alphabetical (A–Z)</option>
        </select>
      </div>

      <!-- Hidden Category Retention -->
      <?php if ($category_filter !== ''): ?>
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
      <?php endif; ?>

      <!-- Filter Buttons -->
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-somethic-dark flex-grow-1">Filter</button>
        <?php if ($category_filter !== '' || $search_query !== '' || $sort_by !== 'newest'): ?>
          <a href="products.php" class="btn btn-sm btn-outline-secondary">Reset</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Category Pill Links -->
    <div class="d-flex flex-wrap gap-1 mt-3 pt-3 border-top" style="border-color: var(--somethic-border) !important;">
      <a href="products.php<?php echo $search_query ? '?search=' . urlencode($search_query) : ''; ?>" class="filter-pill <?php echo ($category_filter === '') ? 'active' : ''; ?>">
        All Categories
      </a>
      <?php foreach ($categories as $cat): ?>
        <?php
          $url = 'products.php?category=' . urlencode($cat);
          if ($search_query !== '') {
              $url .= '&search=' . urlencode($search_query);
          }
        ?>
        <a href="<?php echo $url; ?>" class="filter-pill <?php echo ($category_filter === $cat) ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($cat); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Products Grid -->
  <?php if (empty($products)): ?>
    <div class="card-somethic text-center p-5 my-4">
      <i class="bi bi-bag-x fs-1 text-muted mb-3 d-block"></i>
      <h4>No Handbags Found</h4>
      <p class="text-muted small">No items match your active filter criteria. Try resetting your search or category filter.</p>
      <div>
        <a href="products.php" class="btn btn-sm btn-somethic-gold">View All Handbags</a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($products as $product): ?>
        <?php
          $stock = (int)$product['stock'];
          if ($stock <= 0) {
              $stock_badge = '<span class="badge badge-somethic-out">Out of Stock</span>';
          } elseif ($stock <= 5) {
              $stock_badge = '<span class="badge badge-somethic-low">Low Stock (' . $stock . ' left)</span>';
          } else {
              $stock_badge = '<span class="badge badge-somethic-available">Available (' . $stock . ')</span>';
          }
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="card-somethic h-100 d-flex flex-column">
            <!-- Product Image -->
            <div class="product-img-box position-relative">
              <span class="position-absolute top-0 start-0 m-3">
                <?php echo $stock_badge; ?>
              </span>
              <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <!-- Product Card Body -->
            <div class="p-3 d-flex flex-column flex-grow-1">
              <span class="text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.08em; font-weight: 600;">
                <?php echo htmlspecialchars($product['category']); ?>
              </span>
              <h5 class="my-1 fs-6 fw-bold">
                <?php echo htmlspecialchars($product['name']); ?>
              </h5>
              <div class="price-tag mb-3">
                RM <?php echo number_format($product['price'], 2); ?>
              </div>

              <!-- Buttons: View Details & Reserve -->
              <div class="mt-auto d-flex gap-2">
                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-somethic-outline flex-grow-1 text-center py-2" style="font-size: 0.75rem;">
                  View Details
                </a>
                <?php if ($stock > 0): ?>
                  <a href="reservation.php?product_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-somethic-gold flex-grow-1 text-center py-2" style="font-size: 0.75rem;">
                    Reserve
                  </a>
                <?php else: ?>
                  <button class="btn btn-sm btn-secondary flex-grow-1 py-2 text-center" style="font-size: 0.75rem;" disabled title="Currently out of stock">
                    Sold Out
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
