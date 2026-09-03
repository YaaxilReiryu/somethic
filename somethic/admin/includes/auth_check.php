<?php
// Session check for staff administration with cross-origin iframe resilience
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None',
    ]);
    
    // Check if session ID was provided in GET/POST/Header
    $incoming_sid = $_GET['sid'] ?? $_POST['sid'] ?? $_SERVER['HTTP_X_SESSION_ID'] ?? null;
    if ($incoming_sid && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $incoming_sid)) {
        session_id($incoming_sid);
    }
    session_start();
}

// If session is empty but incoming_sid was provided, re-bind if needed
if (empty($_SESSION['staff_logged_in'])) {
    $incoming_sid = $_GET['sid'] ?? $_POST['sid'] ?? null;
    if ($incoming_sid && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $incoming_sid)) {
        if (session_id() !== $incoming_sid) {
            session_write_close();
            session_id($incoming_sid);
            session_start();
        }
    }
}

if (empty($_SESSION['staff_logged_in'])) {
    $sid_param = !empty($_GET['sid']) ? '?sid=' . urlencode($_GET['sid']) : '';
    header("Location: login.php" . $sid_param);
    exit;
}
