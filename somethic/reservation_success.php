<?php
require_once __DIR__ . '/config/database.php';

$res_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("
        SELECT r.*, p.name AS product_name, p.price, p.image, p.category
        FROM reservations r
        JOIN products p ON r.product_id = p.id
        WHERE r.id = ?
    ");
    $stmt->execute([$res_id]);
    $reservation = $stmt->fetch();
} catch (PDOException $e) {
    $reservation = null;
}

if (!$reservation) {
    header("Location: reservation.php");
    exit;
}

$page_title = 'Reservation Confirmed — SOMETHIC Boutique';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <!-- Success Header -->
      <div class="card-somethic p-4 p-md-5 text-center mb-4" style="background: linear-gradient(180deg, #FFFFFF 0%, var(--somethic-sand) 100%);">
        <div class="brand-logo-mark mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.8rem; background-color: #2E7D32;">
          <i class="bi bi-check-lg text-white"></i>
        </div>
        <span class="badge badge-somethic-available px-3 py-2 text-uppercase mb-2" style="letter-spacing: 0.1em;">
          Reservation Received
        </span>
        <h1 class="h2 mb-2">Thank You, <?php echo htmlspecialchars($reservation['customer_name']); ?>!</h1>
        <p class="text-muted small mb-4" style="max-width: 520px; margin: 0 auto;">
          Your handbag reservation has been registered in our boutique system. Please present your reservation reference code when you visit for pickup.
        </p>

        <!-- Voucher Code Box -->
        <div class="p-3 rounded-3 d-inline-block mx-auto mb-3" style="background-color: #FFFFFF; border: 2px dashed var(--somethic-gold);">
          <span class="text-uppercase small text-muted d-block fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">Reservation Reference Code</span>
          <span class="fs-4 fw-bold text-dark font-monospace">#SOM-<?php echo str_pad($reservation['id'], 5, '0', STR_PAD_LEFT); ?></span>
        </div>
      </div>

      <!-- Reservation Details Card -->
      <div class="card-somethic p-4 mb-4">
        <h4 class="mb-3 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">Reservation Summary</h4>
        
        <div class="row g-4 align-items-center mb-4">
          <div class="col-sm-3 text-center">
            <div class="product-img-box rounded-3 p-2">
              <img src="<?php echo htmlspecialchars($reservation['image']); ?>" alt="<?php echo htmlspecialchars($reservation['product_name']); ?>" class="img-fluid" style="max-height: 120px;">
            </div>
          </div>
          <div class="col-sm-9">
            <span class="text-uppercase small text-muted fw-bold"><?php echo htmlspecialchars($reservation['category']); ?></span>
            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($reservation['product_name']); ?></h5>
            <div class="price-tag mb-1">RM <?php echo number_format($reservation['price'], 2); ?> each</div>
            <div class="small text-muted">Quantity Reserved: <strong><?php echo $reservation['quantity']; ?> unit(s)</strong> • Total: <strong>RM <?php echo number_format($reservation['price'] * $reservation['quantity'], 2); ?></strong></div>
          </div>
        </div>

        <div class="row g-3 py-3 border-top border-bottom" style="border-color: var(--somethic-border) !important;">
          <div class="col-sm-6">
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem;">Customer Contact</span>
            <span class="text-dark fw-semibold"><?php echo htmlspecialchars($reservation['customer_name']); ?></span>
            <div class="small text-muted"><?php echo htmlspecialchars($reservation['phone']); ?></div>
            <div class="small text-muted"><?php echo htmlspecialchars($reservation['email']); ?></div>
          </div>
          <div class="col-sm-6">
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem;">Pickup Schedule</span>
            <div class="text-dark fw-semibold">
              <i class="bi bi-calendar-event me-1 text-warning"></i>
              <?php echo date('l, d F Y', strtotime($reservation['pickup_date'])); ?>
            </div>
            <div class="text-dark fw-semibold">
              <i class="bi bi-clock me-1 text-warning"></i>
              <?php echo htmlspecialchars($reservation['pickup_time']); ?>
            </div>
            <div class="mt-1">
              <span class="badge badge-status-pending">Status: <?php echo htmlspecialchars($reservation['status']); ?></span>
            </div>
          </div>
        </div>

        <?php if (!empty($reservation['note'])): ?>
          <div class="mt-3 pt-2">
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem;">Your Special Note</span>
            <p class="small text-muted mb-0 fst-italic">"<?php echo htmlspecialchars($reservation['note']); ?>"</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pickup Instructions -->
      <div class="p-4 rounded-4 mb-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
        <h5 class="mb-3">In-Store Pickup Instructions</h5>
        <ol class="small text-muted mb-0 ps-3 space-y-2">
          <li class="mb-2"><strong>Show Your Reference Code:</strong> Present reference code <strong>#SOM-<?php echo str_pad($reservation['id'], 5, '0', STR_PAD_LEFT); ?></strong> or your registered phone number at the counter.</li>
          <li class="mb-2"><strong>Inspect Your Handbag:</strong> Our staff will unbox the handbag for you to examine the leather finish, hardware closures, and inner compartments.</li>
          <li class="mb-2"><strong>Pay Upon Pickup:</strong> We accept Cash, Debit/Credit Card, and DuitNow QR upon collection in store.</li>
          <li class="mb-0"><strong>Location:</strong> SOMETHIC Flagship, 123 Fashion Walk, Bukit Bintang, Kuala Lumpur. Open daily 10:00 AM – 10:00 PM.</li>
        </ol>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex flex-wrap gap-3 justify-content-center">
        <a href="products.php" class="btn btn-somethic-dark px-4">Browse More Handbags</a>
        <button onclick="window.print()" class="btn btn-somethic-outline px-4">
          <i class="bi bi-printer me-1"></i> Print / Save Voucher
        </button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
