<?php
// Redirect to admin dashboard (which checks session and redirects to login if needed)
$sid_param = !empty($_GET['sid']) ? '?sid=' . urlencode($_GET['sid']) : '';
header("Location: dashboard.php" . $sid_param);
exit;
