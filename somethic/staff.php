<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'Store Staff Profile & Resume — SOMETHIC Boutique';

try {
    $stmt = $pdo->query("
        SELECT sp.*, u.name, u.email
        FROM staff_profiles sp
        JOIN users u ON sp.user_id = u.id
        ORDER BY sp.id ASC
        LIMIT 1
    ");
    $staff = $stmt->fetch();
} catch (PDOException $e) {
    $staff = null;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
      <li class="breadcrumb-item"><a href="shop.php" class="text-muted">About Store</a></li>
      <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Staff Profile</li>
    </ol>
  </nav>

  <?php if (!$staff): ?>
    <div class="alert alert-warning text-center p-5">
      <h4>Staff Profile Not Available</h4>
      <p class="text-muted">No staff profile information was found in the database.</p>
    </div>
  <?php else: ?>
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <!-- Resume Profile Main Card -->
        <div class="card-somethic overflow-hidden mb-4">
          <!-- Top Header Gradient Banner -->
          <div class="p-4 p-md-5" style="background: linear-gradient(135deg, var(--somethic-dark) 0%, #3D352F 100%); color: #FFFFFF;">
            <div class="row align-items-center g-4">
              <div class="col-md-4 text-center">
                <div class="staff-avatar-box mb-2" style="width: 140px; height: 140px; border-width: 4px; border-color: #FFFFFF;">
                  <img src="<?php echo htmlspecialchars($staff['profile_image']); ?>" alt="<?php echo htmlspecialchars($staff['name']); ?>">
                </div>
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                  Verified Staff
                </span>
              </div>
              <div class="col-md-8 text-center text-md-start">
                <span class="text-uppercase small" style="color: var(--somethic-gold); letter-spacing: 0.15em; font-weight: 600;">
                  Boutique Management Team
                </span>
                <h1 class="h2 text-white mt-1 mb-1" style="font-family: var(--font-serif);"><?php echo htmlspecialchars($staff['name']); ?></h1>
                <p class="h6 fw-normal mb-3" style="color: #E2D4C9;"><?php echo htmlspecialchars($staff['position']); ?></p>
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3 small" style="color: #F7F1ED;">
                  <span><i class="bi bi-telephone text-warning me-1"></i> <?php echo htmlspecialchars($staff['phone']); ?></span>
                  <span><i class="bi bi-envelope text-warning me-1"></i> <?php echo htmlspecialchars($staff['email']); ?></span>
                  <span><i class="bi bi-geo-alt text-warning me-1"></i> SOMETHIC Flagship</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Resume Body Content -->
          <div class="p-4 p-md-5">
            <!-- Biography -->
            <div class="mb-5">
              <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-person-lines-fill fs-4 text-warning"></i>
                <h4 class="mb-0">Professional Biography</h4>
              </div>
              <p class="text-muted" style="line-height: 1.8; font-size: 1rem;">
                <?php echo nl2br(htmlspecialchars($staff['bio'])); ?>
              </p>
            </div>

            <div class="row g-4 mb-5">
              <!-- Skills -->
              <div class="col-md-6">
                <div class="h-100 p-4 rounded-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-stars fs-4 text-warning"></i>
                    <h5 class="mb-0">Core Competencies & Skills</h5>
                  </div>
                  <div class="d-flex flex-wrap gap-2 pt-1">
                    <?php 
                      $skills_list = explode(',', $staff['skills']);
                      foreach ($skills_list as $skill):
                        $skill_clean = trim($skill);
                        if (!empty($skill_clean)):
                    ?>
                      <span class="skill-tag px-3 py-2">
                        <i class="bi bi-check-circle-fill text-warning me-1" style="font-size: 0.75rem;"></i>
                        <?php echo htmlspecialchars($skill_clean); ?>
                      </span>
                    <?php endif; endforeach; ?>
                  </div>
                </div>
              </div>

              <!-- Experience -->
              <div class="col-md-6">
                <div class="h-100 p-4 rounded-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-briefcase fs-4 text-warning"></i>
                    <h5 class="mb-0">Industry Experience</h5>
                  </div>
                  <p class="text-muted small mb-0" style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($staff['experience'])); ?>
                  </p>
                </div>
              </div>
            </div>

            <!-- In-Store Responsibilities & Contact -->
            <div class="p-4 rounded-4 border" style="border-color: var(--somethic-border) !important; background-color: #FFFFFF;">
              <h5 class="mb-3">Daily Boutique Responsibilities</h5>
              <div class="row g-3 text-muted small">
                <div class="col-md-4">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bag-check text-warning"></i>
                    <span>Pickup Voucher Fulfillment</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard2-check text-warning"></i>
                    <span>Inventory Checklist Audits</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-palette text-warning"></i>
                    <span>Customer Styling Advice</span>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3" style="border-color: var(--somethic-border) !important;">
                <div class="small text-muted">
                  Looking for styling guidance or custom handbag recommendations?
                </div>
                <a href="mailto:<?php echo htmlspecialchars($staff['email']); ?>" class="btn btn-sm btn-somethic-gold">
                  <i class="bi bi-envelope-paper me-1"></i> Contact Ariana Directly
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
