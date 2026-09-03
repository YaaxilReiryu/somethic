<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None',
    ]);
    $incoming_sid = $_GET['sid'] ?? $_POST['sid'] ?? null;
    if ($incoming_sid && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $incoming_sid)) {
        session_id($incoming_sid);
    }
    session_start();
}

// Clear all staff session variables
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Logging Out...</title>
  <script>
    try { sessionStorage.removeItem('somethic_sid'); } catch(e) {}
    try { localStorage.removeItem('somethic_sid'); } catch(e) {}
    window.location.href = 'login.php?logged_out=1';
  </script>
</head>
<body style="font-family: sans-serif; text-align: center; padding: 50px;">
  <p>Logging out, please wait...</p>
  <a href="login.php?logged_out=1">Click here if you are not redirected automatically.</a>
</body>
</html>
