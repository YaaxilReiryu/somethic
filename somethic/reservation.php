<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'Reserve Handbag for Store Pickup — SOMETHIC Boutique';

// Fetch all products with stock for selection dropdown
try {
    $stmt = $pdo->query("SELECT id, name, price, stock FROM products ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

// Check if pre-selected product_id from GET
$preselected_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$errors = [];
$customer_name = '';
$phone = '';
$email = '';
$product_id = $preselected_id;
$quantity = 1;
$pickup_date = '';
$pickup_time = '';
$note = '';

// Handle POST Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // 1. Validate Customer Name
    if (empty($customer_name)) {
        $errors[] = 'Customer name is required.';
    }

    // 2. Validate Phone
    if (empty($phone)) {
        $errors[] = 'Phone number is required so we can notify you when ready.';
    }

    // 3. Validate Email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    // 4. Validate Handbag & Stock
    if ($product_id <= 0) {
        $errors[] = 'Please select a handbag to reserve.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $selected_product = $stmt->fetch();

        if (!$selected_product) {
            $errors[] = 'The selected handbag does not exist.';
        } else {
            $available_stock = (int)$selected_product['stock'];
            if ($available_stock <= 0) {
                $errors[] = 'Sorry, "' . htmlspecialchars($selected_product['name']) . '" is currently out of stock.';
            } elseif ($quantity < 1) {
                $errors[] = 'Reservation quantity must be at least 1.';
            } elseif ($quantity > $available_stock) {
                $errors[] = 'Quantity requested (' . $quantity . ') exceeds available store stock (' . $available_stock . ').';
            }
        }
    }

    // 5. Validate Pickup Date
    if (empty($pickup_date)) {
        $errors[] = 'Pickup date is required.';
    } else {
        $today = date('Y-m-d');
        if ($pickup_date < $today) {
            $errors[] = 'Pickup date cannot be in the past.';
        }
    }

    // 6. Validate Pickup Time Slot
    $valid_slots = [
        '10:00 AM - 12:00 PM',
        '12:00 PM - 2:00 PM',
        '2:00 PM - 4:00 PM',
        '4:00 PM - 6:00 PM'
    ];
    if (empty($pickup_time) || !in_array($pickup_time, $valid_slots)) {
        $errors[] = 'Please choose an available 2-hour pickup window.';
    }

    // If valid, save reservation
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reservations (customer_name, phone, email, product_id, quantity, pickup_date, pickup_time, note, status, stock_deducted)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 0)
            ");
            $stmt->execute([
                $customer_name,
                $phone,
                $email,
                $product_id,
                $quantity,
                $pickup_date,
                $pickup_time,
                $note
            ]);

            $reservation_id = $pdo->lastInsertId();
            header("Location: reservation_success.php?id=" . $reservation_id);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to submit reservation: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
      <li class="breadcrumb-item"><a href="products.php" class="text-muted">Products</a></li>
      <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Store Pickup Reservation</li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Left Column: Form -->
    <div class="col-lg-7">
      <div class="card-somethic p-4 p-md-5">
        <span class="text-uppercase small fw-bold" style="letter-spacing: 0.12em; color: var(--somethic-gold);">Simple Booking System</span>
        <h1 class="h3 mt-1 mb-2">Reserve Your Handbag</h1>
        <p class="text-muted small mb-4">
          Reserve your desired handbag for in-store pickup at our Bukit Bintang boutique. We will hold your reserved item for 48 hours without upfront payment.
        </p>

        <!-- Display validation errors -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger mb-4" role="alert">
            <h6 class="alert-heading fw-bold mb-2"><i class="bi bi-exclamation-circle me-1"></i> Please correct the following:</h6>
            <ul class="mb-0 small ps-3">
              <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="reservation.php" class="form-somethic">
          <!-- Customer Full Name -->
          <div class="mb-3">
            <label for="customer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="customer_name" name="customer_name" class="form-control form-control-somethic" placeholder="e.g. Nurul Huda" value="<?php echo htmlspecialchars($customer_name); ?>" required>
          </div>

          <!-- Phone & Email -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" id="phone" name="phone" class="form-control form-control-somethic" placeholder="e.g. +60 17-987 6543" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" id="email" name="email" class="form-control form-control-somethic" placeholder="e.g. nurul@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
          </div>

          <!-- Product Selection -->
          <div class="mb-3">
            <label for="reservation_product_id" class="form-label">Select Handbag <span class="text-danger">*</span></label>
            <select id="reservation_product_id" name="product_id" class="form-select form-control-somethic" required>
              <option value="">-- Choose a Handbag from Collection --</option>
              <?php foreach ($products as $p): ?>
                <?php
                  $p_stock = (int)$p['stock'];
                  $is_out = ($p_stock <= 0);
                  $selected = ($product_id == $p['id']) ? 'selected' : '';
                ?>
                <option value="<?php echo $p['id']; ?>" data-stock="<?php echo $p_stock; ?>" <?php echo $selected; ?> <?php echo $is_out ? 'disabled' : ''; ?>>
                  <?php echo htmlspecialchars($p['name']); ?> (RM <?php echo number_format($p['price'], 2); ?>) — <?php echo $is_out ? 'OUT OF STOCK' : 'Stock: ' . $p_stock; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div id="stock_feedback" class="text-muted small mt-1"></div>
          </div>

          <!-- Quantity -->
          <div class="mb-3">
            <label for="reservation_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" id="reservation_quantity" name="quantity" class="form-control form-control-somethic" min="1" max="10" value="<?php echo htmlspecialchars((string)$quantity); ?>" required style="max-width: 140px;">
            <small class="text-muted">Maximum limit is constrained by current available stock.</small>
          </div>

          <!-- Date & Time Slot -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="reservation_pickup_date" class="form-label">Pickup Date <span class="text-danger">*</span></label>
              <input type="date" id="reservation_pickup_date" name="pickup_date" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($pickup_date); ?>" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6">
              <label for="pickup_time" class="form-label">Pickup Time Slot <span class="text-danger">*</span></label>
              <select id="pickup_time" name="pickup_time" class="form-select form-control-somethic" required>
                <option value="">-- Select Preferred Slot --</option>
                <option value="10:00 AM - 12:00 PM" <?php echo ($pickup_time === '10:00 AM - 12:00 PM') ? 'selected' : ''; ?>>10:00 AM - 12:00 PM</option>
                <option value="12:00 PM - 2:00 PM" <?php echo ($pickup_time === '12:00 PM - 2:00 PM') ? 'selected' : ''; ?>>12:00 PM - 2:00 PM</option>
                <option value="2:00 PM - 4:00 PM" <?php echo ($pickup_time === '2:00 PM - 4:00 PM') ? 'selected' : ''; ?>>2:00 PM - 4:00 PM</option>
                <option value="4:00 PM - 6:00 PM" <?php echo ($pickup_time === '4:00 PM - 6:00 PM') ? 'selected' : ''; ?>>4:00 PM - 6:00 PM</option>
              </select>
            </div>
          </div>

          <!-- Special Note -->
          <div class="mb-4">
            <label for="note" class="form-label">Special Request / Note (Optional)</label>
            <textarea id="note" name="note" rows="3" class="form-control form-control-somethic" placeholder="e.g. Gift wrapping requested with silk ribbon, or note on who will collect."><?php echo htmlspecialchars($note); ?></textarea>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-somethic-gold w-100 py-3">
            <i class="bi bi-bag-check me-2"></i> Confirm Handbag Reservation
          </button>
        </form>
      </div>
    </div>

    <!-- Right Column: Store Pickup Guide -->
    <div class="col-lg-5">
      <div class="card-somethic p-4 mb-4">
        <h4 class="mb-3">Pickup Policy</h4>
        <div class="d-flex align-items-start gap-3 mb-3">
          <i class="bi bi-clock-history fs-4 text-warning"></i>
          <div>
            <h6 class="fw-bold mb-1">48-Hour Guarantee</h6>
            <p class="small text-muted mb-0">Your reservation is held securely for 48 hours starting from your selected pickup slot date.</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-3">
          <i class="bi bi-cash-coin fs-4 text-warning"></i>
          <div>
            <h6 class="fw-bold mb-1">No Advance Payment</h6>
            <p class="small text-muted mb-0">You do not need to enter credit cards. Inspect the handbag in boutique and pay upon collection (Cash, Card, DuitNow QR accepted).</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-3">
          <i class="bi bi-gift fs-4 text-warning"></i>
          <div>
            <h6 class="fw-bold mb-1">Complimentary Packaging</h6>
            <p class="small text-muted mb-0">Every reserved handbag is prepared in our signature SOMETHIC dust bag and gift carry bag.</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3">
          <i class="bi bi-person-check fs-4 text-warning"></i>
          <div>
            <h6 class="fw-bold mb-1">Personal Styling</h6>
            <p class="small text-muted mb-0">Our store manager Ariana Sofea will assist you with strap adjustment and care guidance.</p>
          </div>
        </div>
      </div>

      <!-- Store Address Quick Card -->
      <div class="p-4 rounded-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
        <h6 class="text-uppercase fw-bold small text-muted" style="letter-spacing: 0.1em;">Pickup Location</h6>
        <p class="fw-bold text-dark mb-1">SOMETHIC Boutique</p>
        <p class="small text-muted mb-2">123 Fashion Walk, Bukit Bintang, 50200 Kuala Lumpur</p>
        <p class="small text-muted mb-0"><i class="bi bi-telephone me-1 text-warning"></i> +60 3-8888 1234</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
