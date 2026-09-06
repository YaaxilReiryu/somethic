<?php
require_once __DIR__ . '/config/database.php';
echo $pdo->getAttribute(PDO::ATTR_DRIVER_NAME); // TEMP DEBUG — remove after checking
$page_title = 'SOMETHIC — Handbag Retail Boutique';
require_once __DIR__ . '/includes/header.php';

// Fetch 4 featured handbags
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC LIMIT 4");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="badge badge-status-pending px-3 py-2 text-uppercase mb-3" style="letter-spacing: 0.12em; font-size: 0.75rem;">
          Boutique Collection 2024
        </span>
        <h1 class="hero-title">Carry Your Style with SOMETHIC</h1>
        <p class="hero-subtitle">
          Discover stylish handbags designed for every occasion. From modern shoulder silhouettes to spacious leather totes, explore curated luxury crafted for modern everyday elegance.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="products.php" class="btn btn-somethic-dark">Shop Handbags</a>
          <a href="reservation.php" class="btn btn-somethic-gold">Reserve Now</a>
          <a href="shop.php" class="btn btn-somethic-outline">Our Boutique Story</a>
        </div>

        <!-- Quick highlights -->
        <div class="row mt-5 pt-4 border-top border-light g-3">
          <div class="col-4">
            <div class="fw-bold fs-5 text-dark">100%</div>
            <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Curated Quality</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-dark">Free</div>
            <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Store Pickup</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-dark">RM 159+</div>
            <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Affordable Luxury</div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card-somethic p-4 text-center position-relative" style="background: linear-gradient(135deg, #FFFFFF 0%, var(--somethic-peach) 100%);">
          <span class="position-absolute top-0 end-0 m-3 badge badge-status-confirmed">Featured Arrival</span>
          <div class="p-3 my-2">
            <img src="assets/images/bag_luna.svg" alt="SOMETHIC Luna Shoulder Bag" class="img-fluid" style="max-height: 280px;">
          </div>
          <h4 class="mb-1">SOMETHIC Luna Bag</h4>
          <p class="text-muted small mb-2">Crescent silhouette with polished champagne hardware</p>
          <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
            <span class="price-tag">RM 249.00</span>
            <a href="reservation.php?product_id=1" class="btn btn-sm btn-somethic-gold px-3">Reserve Pickup</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Handbags Showcase -->
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="text-uppercase small fw-bold" style="letter-spacing: 0.12em; color: var(--somethic-gold);">Handcrafted Collection</span>
        <h2 class="mb-0">Featured Handbags</h2>
      </div>
      <a href="products.php" class="btn btn-sm btn-somethic-outline">
        View All Handbags <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($featured_products as $product): ?>
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
        <div class="col-lg-3 col-md-6">
          <div class="card-somethic h-100 d-flex flex-column">
            <div class="product-img-box position-relative">
              <span class="position-absolute top-0 start-0 m-3">
                <?php echo $stock_badge; ?>
              </span>
              <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
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
              <div class="mt-auto d-flex gap-2">
                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-somethic-outline flex-grow-1 text-center py-2" style="font-size: 0.75rem;">
                  Details
                </a>
                <?php if ($stock > 0): ?>
                  <a href="reservation.php?product_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-somethic-gold flex-grow-1 text-center py-2" style="font-size: 0.75rem;">
                    Reserve
                  </a>
                <?php else: ?>
                  <button class="btn btn-sm btn-secondary flex-grow-1 py-2 text-center" style="font-size: 0.75rem;" disabled>
                    Sold Out
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Boutique Experience & Store Pickup Highlights -->
<section class="py-5" style="background-color: var(--somethic-sand); border-top: 1px solid var(--somethic-border); border-bottom: 1px solid var(--somethic-border);">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <div class="pe-lg-4">
          <span class="text-uppercase small fw-bold" style="letter-spacing: 0.12em; color: var(--somethic-gold);">The SOMETHIC Experience</span>
          <h2 class="mb-3">Reserve Online, Experience in Boutique</h2>
          <p class="text-muted" style="line-height: 1.7;">
            We believe that choosing the right handbag is a tactile, personal experience. With SOMETHIC's simple reservation system, you can hold your favorite piece for 48 hours without any pre-payment or account signup.
          </p>
          <div class="space-y-3 mt-4">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="brand-logo-mark flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.9rem;">1</div>
              <div>
                <h6 class="mb-1 fw-bold">Select Handbag & Pickup Slot</h6>
                <p class="small text-muted mb-0">Browse our catalog and pick a convenient 2-hour pickup window.</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="brand-logo-mark flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.9rem;">2</div>
              <div>
                <h6 class="mb-1 fw-bold">Receive Instant Confirmation</h6>
                <p class="small text-muted mb-0">Get your reservation voucher code immediately on screen.</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-logo-mark flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.9rem;">3</div>
              <div>
                <h6 class="mb-1 fw-bold">Visit Store & Try On</h6>
                <p class="small text-muted mb-0">Inspect the handbag in person with assistance from our store manager.</p>
              </div>
            </div>
          </div>
          <div class="mt-4">
            <a href="reservation.php" class="btn btn-somethic-gold">Make a Reservation</a>
          </div>
        </div>
      </div>

      <!-- Staff Preview Spotlight -->
      <div class="col-lg-6">
        <div class="staff-card-highlight text-center">
          <div class="staff-avatar-box">
            <img src="assets/images/staff_ariana.jpg" alt="Ariana Sofea">
          </div>
          <h4 class="mb-1">Ariana Sofea</h4>
          <p class="text-uppercase small fw-bold mb-2" style="color: var(--somethic-gold); letter-spacing: 0.1em; font-size: 0.75rem;">
            SOMETHIC Store Manager
          </p>
          <p class="small text-muted fst-italic px-lg-4 mb-3">
            "Ariana manages daily store operations and customer service at SOMETHIC with a passion for styling and handbag care."
          </p>
          <div class="mb-3">
            <span class="skill-tag">Customer Service</span>
            <span class="skill-tag">Retail Management</span>
            <span class="skill-tag">Product Styling</span>
            <span class="skill-tag">Inventory</span>
          </div>
          <a href="staff.php" class="btn btn-sm btn-somethic-dark px-4">
            View Manager Profile & Resume
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
