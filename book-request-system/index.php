<?php
/**
 * Entry Point - Redirects to User Login
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Location: /book-request-system/user/login.php');
exit();
