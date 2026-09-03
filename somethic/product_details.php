<?php
require_once __DIR__ . '/config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    $product = null;
}

if (!$product) {
    // If not found, redirect to catalog or show not found
    header("Location: products.php");
    exit;
}

$page_title = $product['name'] . ' — SOMETHIC Boutique';
require_once __DIR__ . '/includes/header.php';

$stock = (int)$product['stock'];
if ($stock <= 0) {
    $stock_badge = '<span class="badge badge-somethic-out">Out of Stock</span>';
    $status_text = 'Currently Unavailable for Pickup';
} elseif ($stock <= 5) {
    $stock_badge = '<span class="badge badge-somethic-low">Low Stock</span>';
    $status_text = 'Only ' . $stock . ' unit(s) remaining in store';
} else {
    $stock_badge = '<span class="badge badge-somethic-available">In Stock</span>';
    $status_text = $stock . ' units ready for immediate boutique pickup';
}

// Fetch related products from same category
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 3");
    $stmt->execute([$product['category'], $product['id']]);
    $related = $stmt->fetchAll();
} catch (PDOException $e) {
    $related = [];
}
?>

<div class="container py-5">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
      <li class="breadcrumb-item"><a href="products.php" class="text-muted">Products</a></li>
      <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category']); ?>" class="text-muted"><?php echo htmlspecialchars($product['category']); ?></a></li>
      <li class="breadcrumb-item active text-dark fw-bold" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Left Column: Large Product Image -->
    <div class="col-lg-6">
      <div class="card-somethic p-4 text-center" style="background-color: var(--somethic-sand);">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid" style="max-height: 480px; object-fit: contain;">
      </div>
      <div class="d-flex justify-content-center gap-3 mt-3 text-muted small">
        <span><i class="bi bi-shield-check text-warning me-1"></i> Quality Inspected</span>
        <span><i class="bi bi-box-seam text-warning me-1"></i> Dust Bag Included</span>
        <span><i class="bi bi-shop text-warning me-1"></i> Store Pickup</span>
      </div>
    </div>

    <!-- Right Column: Product Details & Reservation Action -->
    <div class="col-lg-6">
      <div class="ps-lg-3">
        <!-- Category & Stock Badge -->
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 0.1em;">
            <?php echo htmlspecialchars($product['category']); ?>
          </span>
          <span>•</span>
          <?php echo $stock_badge; ?>
        </div>

        <!-- Product Name -->
        <h1 class="h2 mb-3 fw-bold"><?php echo htmlspecialchars($product['name']); ?></h1>

        <!-- Price in RM -->
        <div class="d-flex align-items-baseline gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--somethic-border) !important;">
          <span class="price-tag fs-2">RM <?php echo number_format($product['price'], 2); ?></span>
          <span class="text-muted small">Inclusive of boutique dust packaging</span>
        </div>

        <!-- Stock Status Info -->
        <div class="p-3 rounded-3 mb-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-uppercase small text-muted d-block fw-bold" style="font-size: 0.7rem; letter-spacing: 0.08em;">Boutique Availability</span>
              <span class="fw-semibold text-dark"><?php echo $status_text; ?></span>
            </div>
            <div class="text-end">
              <span class="badge bg-white text-dark border px-3 py-2 fw-bold" style="border-color: var(--somethic-border) !important;">
                Available Stock: <?php echo $stock; ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Full Description -->
        <div class="mb-4">
          <h5 class="fw-bold mb-2">Product Description</h5>
          <p class="text-muted" style="line-height: 1.8;">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
          </p>
        </div>

        <!-- Specifications -->
        <div class="mb-4">
          <h6 class="text-uppercase small fw-bold mb-2" style="letter-spacing: 0.08em; color: var(--somethic-muted);">Craftsmanship & Specs</h6>
          <ul class="list-unstyled small text-muted space-y-1 mb-0">
            <li class="mb-1"><i class="bi bi-check2 text-warning me-2"></i><strong>Material:</strong> Premium micro-grain vegan calfskin leather</li>
            <li class="mb-1"><i class="bi bi-check2 text-warning me-2"></i><strong>Hardware:</strong> Polished 14K champagne gold electroplating</li>
            <li class="mb-1"><i class="bi bi-check2 text-warning me-2"></i><strong>Interior:</strong> Smooth satin twill lining with zip pocket and card slot</li>
            <li class="mb-1"><i class="bi bi-check2 text-warning me-2"></i><strong>Care:</strong> Wipe gently with a microfiber cloth; avoid direct moisture</li>
          </ul>
        </div>

        <!-- Reservation CTA -->
        <div class="pt-3 border-top" style="border-color: var(--somethic-border) !important;">
          <?php if ($stock > 0): ?>
            <div class="d-flex flex-wrap gap-3 align-items-center">
              <a href="reservation.php?product_id=<?php echo $product['id']; ?>" class="btn btn-somethic-gold btn-lg px-4 flex-grow-1 text-center">
                <i class="bi bi-calendar-check me-2"></i> Reserve for Store Pickup
              </a>
              <a href="products.php" class="btn btn-somethic-outline btn-lg px-4">
                Continue Browsing
              </a>
            </div>
            <p class="small text-muted mt-2 mb-0">
              <i class="bi bi-info-circle me-1"></i> No payment required now. Inspect and pay in store during pickup.
            </p>
          <?php else: ?>
            <div class="alert alert-warning mb-3">
              <i class="bi bi-exclamation-triangle me-2"></i>
              This handbag is currently sold out. Please contact our boutique or check back soon for our next shipment restock.
            </div>
            <a href="products.php" class="btn btn-somethic-dark">
              Explore Available Handbags
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Handbags -->
  <?php if (!empty($related)): ?>
    <div class="mt-5 pt-5 border-top" style="border-color: var(--somethic-border) !important;">
      <h3 class="mb-4">You May Also Love</h3>
      <div class="row g-4">
        <?php foreach ($related as $rel): ?>
          <div class="col-md-4">
            <div class="card-somethic h-100 p-3">
              <div class="product-img-box mb-2">
                <img src="<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
              </div>
              <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($rel['name']); ?></h6>
              <div class="d-flex justify-content-between align-items-center">
                <span class="price-tag small">RM <?php echo number_format($rel['price'], 2); ?></span>
                <a href="product_details.php?id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-somethic-outline">Details</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
