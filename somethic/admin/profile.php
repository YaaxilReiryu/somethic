<?php
$admin_title = 'Edit Staff Profile & Resume — SOMETHIC';
require_once __DIR__ . '/includes/header.php';

$user_id = (int)($_SESSION['staff_user_id'] ?? 0);
$success = '';
$error = '';

// Fetch current user and staff profile
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role,
               p.position, p.bio, p.skills, p.experience, p.phone, p.profile_image
        FROM users u
        LEFT JOIN staff_profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        // Fallback to first user in system
        $stmt = $pdo->query("
            SELECT u.id, u.name, u.email, u.role,
                   p.position, p.bio, p.skills, p.experience, p.phone, p.profile_image
            FROM users u
            LEFT JOIN staff_profiles p ON u.id = p.user_id
            LIMIT 1
        ");
        $staff = $stmt->fetch();
        $user_id = $staff['id'];
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $profile_image = trim($_POST['profile_image'] ?? 'assets/images/staff_ariana.jpg');

    if (empty($name) || empty($email)) {
        $error = "Name and email address are required.";
    } else {
        try {
            // Update users table
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $email, $hash, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $email, $user_id]);
            }

            // Check if profile exists, if not insert, otherwise update
            $chk = $pdo->prepare("SELECT COUNT(*) FROM staff_profiles WHERE user_id = ?");
            $chk->execute([$user_id]);
            $exists = $chk->fetchColumn() > 0;

            if ($exists) {
                $stmt = $pdo->prepare("
                    UPDATE staff_profiles
                    SET position = ?, phone = ?, bio = ?, skills = ?, experience = ?, profile_image = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $stmt->execute([$position, $phone, $bio, $skills, $experience, $profile_image, $user_id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO staff_profiles (user_id, position, phone, bio, skills, experience, profile_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $position, $phone, $bio, $skills, $experience, $profile_image]);
            }

            $_SESSION['staff_name'] = $name;
            $_SESSION['staff_email'] = $email;

            $success = "Profile and public staff resume updated successfully!";

            // Refresh data
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.role,
                       p.position, p.bio, p.skills, p.experience, p.phone, p.profile_image
                FROM users u
                LEFT JOIN staff_profiles p ON u.id = p.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $staff = $stmt->fetch();

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom" style="border-color: var(--somethic-border) !important;">
  <div>
    <h3 class="fw-bold mb-1" style="font-family: var(--font-serif);">Staff Profile & Resume Settings</h3>
    <p class="text-muted small mb-0">
      Changes saved here are reflected live on your storefront public resume page (<code>staff.php</code>).
    </p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="../staff.php" target="_blank" class="btn btn-somethic-outline btn-sm">
      <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live Resume
    </a>
  </div>
</div>

<!-- Alerts -->
<?php if (!empty($success)): ?>
  <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card-somethic p-4 p-md-5">
      <form method="POST" action="profile.php" class="form-somethic">
        <!-- Account Credentials -->
        <h5 class="fw-bold mb-3" style="font-family: var(--font-serif);">Account Information</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['name'] ?? ''); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Staff Email <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="password" class="form-label">Update Password (Optional)</label>
            <input type="password" id="password" name="password" class="form-control form-control-somethic" placeholder="Leave blank to keep existing password">
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Contact Phone</label>
            <input type="text" id="phone" name="phone" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['phone'] ?? ''); ?>">
          </div>
        </div>

        <hr class="my-4" style="border-color: var(--somethic-border);">

        <!-- Professional Resume Information -->
        <h5 class="fw-bold mb-3" style="font-family: var(--font-serif);">Public Resume & Boutique Bio</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="position" class="form-label">Boutique Job Title / Role</label>
            <input type="text" id="position" name="position" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['position'] ?? 'SOMETHIC Store Manager'); ?>" placeholder="e.g., SOMETHIC Boutique Lead & Merchandiser">
          </div>
          <div class="col-md-6">
            <label for="profile_image" class="form-label">Profile Image Asset URL</label>
            <input type="text" id="profile_image" name="profile_image" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['profile_image'] ?? 'assets/images/staff_ariana.jpg'); ?>" placeholder="assets/images/staff_ariana.jpg">
          </div>
        </div>

        <div class="mb-4">
          <label for="bio" class="form-label">Professional Bio Summary</label>
          <textarea id="bio" name="bio" rows="3" class="form-control form-control-somethic" placeholder="Introduce your background, passion for luxury handbags, and store management philosophy..."><?php echo htmlspecialchars($staff['bio'] ?? ''); ?></textarea>
        </div>

        <div class="mb-4">
          <label for="skills" class="form-label">Core Competencies & Skills <small class="text-muted">(Comma-separated)</small></label>
          <input type="text" id="skills" name="skills" class="form-control form-control-somethic" value="<?php echo htmlspecialchars($staff['skills'] ?? ''); ?>" placeholder="Customer Service, Retail Management, Product Styling, Inventory Management, Store Visuals">
        </div>

        <div class="mb-4">
          <label for="experience" class="form-label">Career Experience Highlights</label>
          <textarea id="experience" name="experience" rows="3" class="form-control form-control-somethic" placeholder="Past boutique experience, merchandising milestones, or customer retention achievements..."><?php echo htmlspecialchars($staff['experience'] ?? ''); ?></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--somethic-border) !important;">
          <button type="submit" class="btn btn-somethic-dark px-4">
            <i class="bi bi-check2-circle me-1"></i> Save Changes & Update Resume
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Profile Card Preview -->
  <div class="col-lg-4">
    <div class="card-somethic p-4 text-center">
      <div class="mb-3">
        <img src="../<?php echo htmlspecialchars($staff['profile_image'] ?? 'assets/images/staff_ariana.jpg'); ?>" alt="" class="rounded-circle shadow-sm" style="width: 110px; height: 110px; object-fit: cover; border: 3px solid var(--somethic-gold);">
      </div>
      <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($staff['name'] ?? 'Ariana Sofea'); ?></h5>
      <p class="badge badge-status-confirmed mb-3"><?php echo htmlspecialchars($staff['position'] ?? 'Store Manager'); ?></p>
      
      <p class="small text-muted mb-3 text-start">
        <?php echo nl2br(htmlspecialchars($staff['bio'] ?? '')); ?>
      </p>

      <hr style="border-color: var(--somethic-border);">

      <div class="text-start">
        <h6 class="small text-uppercase fw-bold text-muted mb-2" style="font-size: 0.7rem;">Verified Skills</h6>
        <div class="d-flex flex-wrap gap-1">
          <?php 
            $skills_list = explode(',', $staff['skills'] ?? '');
            foreach ($skills_list as $sk): 
              $sk = trim($sk);
              if (!empty($sk)):
          ?>
            <span class="badge bg-light text-dark border" style="font-size: 0.72rem;"><?php echo htmlspecialchars($sk); ?></span>
          <?php endif; endforeach; ?>
        </div>
      </div>

      <div class="mt-4 pt-2">
        <a href="../staff.php" target="_blank" class="btn btn-sm btn-somethic-outline w-100">
          <i class="bi bi-box-arrow-up-right me-1"></i> View Live Public Resume
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
