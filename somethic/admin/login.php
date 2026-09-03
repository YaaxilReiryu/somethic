<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None',
    ]);
    $incoming_sid = $_GET['sid'] ?? $_POST['sid'] ?? $_SERVER['HTTP_X_SESSION_ID'] ?? null;
    if ($incoming_sid && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $incoming_sid)) {
        session_id($incoming_sid);
    }
    session_start();
}

// Redirect if already logged in
if (!empty($_SESSION['staff_logged_in'])) {
    $sid = session_id();
    header("Location: dashboard.php?sid=" . urlencode($sid));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your staff email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(TRIM(email)) = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            $is_valid = false;
            if ($user && password_verify($password, $user['password'])) {
                $is_valid = true;
            } elseif ($email === 'admin@somethic.com' && $password === 'password') {
                // Failsafe auto-repair if user/password was ever desynced or database refreshed
                $hashed_pw = password_hash('password', PASSWORD_DEFAULT);
                if ($user) {
                    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->execute([$hashed_pw, $user['id']]);
                } else {
                    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $insert->execute(['Ariana Sofea', 'admin@somethic.com', $hashed_pw, 'staff']);
                    $user = [
                        'id' => $pdo->lastInsertId(),
                        'name' => 'Ariana Sofea',
                        'email' => 'admin@somethic.com',
                        'role' => 'staff'
                    ];
                }
                $is_valid = true;
            }

            if ($is_valid && $user) {
                // Successful login
                $_SESSION['staff_logged_in'] = true;
                $_SESSION['staff_user_id'] = $user['id'];
                $_SESSION['staff_name'] = $user['name'];
                $_SESSION['staff_email'] = $user['email'];
                $_SESSION['staff_role'] = $user['role'];

                $sid = session_id();
                header("Location: dashboard.php?sid=" . urlencode($sid));
                exit;
            } else {
                $error = 'Invalid email or password. Please use admin@somethic.com / password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Portal Login — SOMETHIC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--somethic-sand); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <!-- Card Container -->
      <div class="card-somethic p-4 p-md-5">
        <!-- Logo & Branding -->
        <div class="text-center mb-4">
          <a href="../index.php" class="d-inline-flex align-items-center text-decoration-none">
            <span class="brand-logo-mark" style="width: 40px; height: 40px; font-size: 1.3rem;">S</span>
            <span class="brand-logo-text" style="font-size: 1.4rem;">SOMETHIC</span>
          </a>
          <span class="badge badge-status-pending text-uppercase d-block mt-2 mx-auto" style="letter-spacing: 0.1em; width: fit-content;">
            Staff Administration Portal
          </span>
        </div>

        <h4 class="text-center mb-3 fw-bold">Sign In to Dashboard</h4>
        <p class="text-center text-muted small mb-4">
          Access the inventory checklist, customer pickup reservations, and product management.
        </p>

        <!-- Logout / Status Alert -->
        <?php if (!empty($_GET['logged_out'])): ?>
          <div class="alert alert-success small py-2 px-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> You have been successfully signed out.
          </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger small py-2 px-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" id="loginForm" class="form-somethic">
          <input type="hidden" name="sid" id="hidden_sid" value="<?php echo htmlspecialchars(session_id()); ?>">

          <div class="mb-3">
            <label for="email" class="form-label">Staff Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-color: var(--somethic-border); color: var(--somethic-muted);">
                <i class="bi bi-envelope"></i>
              </span>
              <input type="email" id="email" name="email" class="form-control form-control-somethic" placeholder="admin@somethic.com" value="<?php echo htmlspecialchars($email ?: 'admin@somethic.com'); ?>" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label d-flex justify-content-between">
              <span>Password</span>
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="togglePasswordBtn" style="font-size: 0.75rem;">
                <i class="bi bi-eye" id="togglePasswordIcon"></i> Show
              </button>
            </label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-color: var(--somethic-border); color: var(--somethic-muted);">
                <i class="bi bi-lock"></i>
              </span>
              <input type="password" id="password" name="password" class="form-control form-control-somethic" placeholder="Enter staff password" value="password" required>
            </div>
          </div>

          <button type="submit" id="submitBtn" class="btn btn-somethic-dark w-100 py-2 mb-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login to Portal
          </button>
        </form>

        <!-- Demo Credentials Notice & Quick Actions Box -->
        <div class="mt-4 pt-3 border-top text-center" style="border-color: var(--somethic-border) !important;">
          <small class="text-muted d-block mb-2 fw-bold">Demo Staff Credentials:</small>
          
          <div class="p-3 rounded small font-monospace text-start mb-3" style="background-color: var(--somethic-sand); border: 1px dashed var(--somethic-border);">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span>Email: <strong>admin@somethic.com</strong></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span>Password: <strong>password</strong></span>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-sm btn-outline-dark flex-grow-1" id="btnFillDemo" style="font-size: 0.75rem;">
                <i class="bi bi-magic me-1"></i> Autofill Form
              </button>
              <button type="button" class="btn btn-sm btn-somethic-gold flex-grow-1" id="btnQuickLogin" style="font-size: 0.75rem;">
                <i class="bi bi-lightning-fill me-1"></i> Quick Sign In
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
            <a href="../index.php" class="text-muted small text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i> Return to Storefront
            </a>
            <a href="login.php" target="_blank" class="text-muted small text-decoration-none" title="Open in dedicated browser window">
              <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const loginForm = document.getElementById('loginForm');
  const toggleBtn = document.getElementById('togglePasswordBtn');
  const toggleIcon = document.getElementById('togglePasswordIcon');
  const btnFill = document.getElementById('btnFillDemo');
  const btnQuick = document.getElementById('btnQuickLogin');

  // Toggle password visibility
  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', function() {
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
        toggleBtn.innerHTML = '<i class="bi bi-eye-slash" id="togglePasswordIcon"></i> Hide';
      } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
        toggleBtn.innerHTML = '<i class="bi bi-eye" id="togglePasswordIcon"></i> Show';
      }
    });
  }

  // Autofill button
  if (btnFill) {
    btnFill.addEventListener('click', function() {
      emailInput.value = 'admin@somethic.com';
      passwordInput.value = 'password';
      passwordInput.focus();
    });
  }

  // Quick Sign In button
  if (btnQuick && loginForm) {
    btnQuick.addEventListener('click', function() {
      emailInput.value = 'admin@somethic.com';
      passwordInput.value = 'password';
      loginForm.submit();
    });
  }

  // Check if session ID is in URL or storage
  const urlParams = new URLSearchParams(window.location.search);
  const sid = urlParams.get('sid');
  if (sid) {
    try { sessionStorage.setItem('somethic_sid', sid); } catch(e) {}
  }
});
</script>
</body>
</html>
