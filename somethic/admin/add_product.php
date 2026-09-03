<?php
$admin_title = 'Add New Handbag — SOMETHIC Staff';
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

$name = '';
$category = 'Shoulder Bag';
$price = '';
$stock = '10';
$description = '';
$image = 'assets/images/bag_luna.svg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $image_choice = $_POST['image_choice'] ?? 'preset';
    $custom_image_url = trim($_POST['custom_image_url'] ?? '');
    $preset_image = $_POST['preset_image'] ?? 'assets/images/bag_luna.svg';

    $image = ($image_choice === 'custom' && !empty($custom_image_url)) ? $custom_image_url : $preset_image;

    // Validation
    if (empty($name)) {
        $error = 'Please provide a handbag model name.';
    } elseif ($price <= 0) {
        $error = 'Please enter a valid retail price greater than $0.00.';
    } elseif ($stock < 0) {
        $error = 'Stock cannot be negative.';
    } elseif (empty($description)) {
        $error = 'Please enter a description for the handbag.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $stock, $description, $image]);
            $new_id = $pdo->lastInsertId();

            header("Location: products.php?added=" . $new_id);
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$preset_images = [
    'assets/images/bag_luna.svg' => 'Luna Crescent Shoulder Bag (Beige/Gold)',
    'assets/images/bag_mila.svg' => 'Mila Everyday Shopper Tote (Cognac Brown)',
    'assets/images/bag_ava.svg' => 'Ava Flap Crossbody Bag (Black Noir)',
    'assets/images/bag_bella.svg' => 'Bella Micro Mini Bag (Blush Rose)',
    'assets/images/bag_sofia.svg' => 'Sofia Structured Satchel (Olive Forest)',
    'assets/images/bag_emma.svg' => 'Emma Baguette Shoulder (Cream White)',
    'assets/images/bag_chloe.svg' => 'Chloe Relaxed Slouchy Tote (Warm Tan)',
    'assets/images/bag_lily.svg' => 'Lily Evening Box Clutch (Champagne)',
];
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Add New Handbag to Catalog</h3>
    <p class="text-muted small mb-0">
      Expand the boutique collection with high-end craftsmanship details, pricing, and initial stock quantities.
    </p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="products.php" class="btn btn-somethic-outline btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
  </div>
</div>

<!-- Error Alert -->
<?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="row g-4 justify-content-center">
  <div class="col-lg-8">
    <div class="card-somethic p-4 p-md-5">
      <form method="POST" action="add_product.php" class="form-somethic">
        <!-- Bag Name -->
        <div class="mb-4">
          <label for="name" class="form-label">Handbag Name <span class="text-danger">*</span></label>
          <input type="text" id="name" name="name" class="form-control form-control-somethic" placeholder="e.g., SOMETHIC Vivienne Satchel" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div class="row g-3 mb-4">
          <!-- Category -->
          <div class="col-md-4">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <select id="category" name="category" class="form-select form-control-somethic" required>
              <option value="Shoulder Bag" <?php echo $category === 'Shoulder Bag' ? 'selected' : ''; ?>>Shoulder Bag</option>
              <option value="Tote Bag" <?php echo $category === 'Tote Bag' ? 'selected' : ''; ?>>Tote Bag</option>
              <option value="Crossbody Bag" <?php echo $category === 'Crossbody Bag' ? 'selected' : ''; ?>>Crossbody Bag</option>
              <option value="Mini Bag" <?php echo $category === 'Mini Bag' ? 'selected' : ''; ?>>Mini Bag</option>
              <option value="Handbag" <?php echo $category === 'Handbag' ? 'selected' : ''; ?>>Handbag / Satchel</option>
              <option value="Clutch" <?php echo $category === 'Clutch' ? 'selected' : ''; ?>>Clutch / Evening</option>
              <option value="Backpack" <?php echo $category === 'Backpack' ? 'selected' : ''; ?>>Luxury Backpack</option>
            </select>
          </div>

          <!-- Price -->
          <div class="col-md-4">
            <label for="price" class="form-label">Retail Price (USD $) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-color: var(--somethic-border);">$</span>
              <input type="number" step="0.01" min="1" id="price" name="price" class="form-control form-control-somethic" placeholder="249.00" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
          </div>

          <!-- Stock Level -->
          <div class="col-md-4">
            <label for="stock" class="form-label">Initial Stock Units <span class="text-danger">*</span></label>
            <input type="number" min="0" max="9999" id="stock" name="stock" class="form-control form-control-somethic" placeholder="10" value="<?php echo htmlspecialchars($stock); ?>" required>
          </div>
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label for="description" class="form-label">Boutique Description & Specifications <span class="text-danger">*</span></label>
          <textarea id="description" name="description" rows="4" class="form-control form-control-somethic" placeholder="Describe materials (e.g. Italian vegan leather, gold hardware), interior compartments, dimensions, and styling notes..." required><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <!-- Visual / Image Selection -->
        <div class="mb-4">
          <label class="form-label d-block">Handbag Artwork / Image Asset</label>
          
          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="image_choice" id="choice_preset" value="preset" checked onchange="toggleImageInputs()">
                <label class="form-check-label fw-semibold" for="choice_preset">
                  Choose SOMETHIC Luxury Vector Artwork
                </label>
              </div>

              <select name="preset_image" id="preset_image_select" class="form-select form-control-somethic" onchange="previewPresetImage()">
                <?php foreach ($preset_images as $path => $label): ?>
                  <option value="<?php echo $path; ?>"><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="image_choice" id="choice_custom" value="custom" onchange="toggleImageInputs()">
                <label class="form-check-label fw-semibold" for="choice_custom">
                  Or External Image URL
                </label>
              </div>

              <input type="url" name="custom_image_url" id="custom_image_url" class="form-control form-control-somethic" placeholder="https://images.unsplash.com/..." disabled>
            </div>
          </div>

          <!-- Preview box -->
          <div class="mt-3 p-3 rounded text-center" style="background-color: var(--somethic-sand); border: 1px dashed var(--somethic-border);">
            <small class="text-muted d-block mb-2 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Visual Preview</small>
            <img id="image_preview_box" src="../assets/images/bag_luna.svg" alt="Preview" style="max-height: 120px; object-fit: contain; border-radius: 6px;">
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--somethic-border) !important;">
          <a href="products.php" class="btn btn-somethic-outline px-4">Cancel</a>
          <button type="submit" class="btn btn-somethic-dark px-4">
            <i class="bi bi-check-lg me-1"></i> Save Handbag to Catalog
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleImageInputs() {
  const isCustom = document.getElementById('choice_custom').checked;
  document.getElementById('custom_image_url').disabled = !isCustom;
  document.getElementById('preset_image_select').disabled = isCustom;
  if (!isCustom) {
    previewPresetImage();
  } else {
    const url = document.getElementById('custom_image_url').value;
    if (url) document.getElementById('image_preview_box').src = url;
  }
}

function previewPresetImage() {
  const val = document.getElementById('preset_image_select').value;
  document.getElementById('image_preview_box').src = '../' + val;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
