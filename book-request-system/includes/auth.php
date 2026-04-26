<?php
/**
 * Authentication Helper
 * Checks session validity and role-based access
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require user to be logged in as regular user
 */
function requireUser() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        header('Location: /book-request-system/user/login.php');
        exit();
    }
}

/**
 * Require user to be logged in as admin
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /book-request-system/admin/login.php');
        exit();
    }
}

/**
 * Require user to be logged in as superadmin
 */
function requireSuperAdmin() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
        header('Location: /book-request-system/superadmin/login.php');
        exit();
    }
}

/**
 * Sanitize output to prevent XSS
 */
function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
