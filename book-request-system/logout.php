<?php
/**
 * Logout - Destroys session completely
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capture role before destroying
$role = $_SESSION['role'] ?? 'user';

// Destroy session completely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect based on previous role
if ($role === 'superadmin') {
    header('Location: /book-request-system/superadmin/login.php');
} elseif ($role === 'admin') {
    header('Location: /book-request-system/admin/login.php');
} else {
    header('Location: /book-request-system/user/login.php');
}
exit();
