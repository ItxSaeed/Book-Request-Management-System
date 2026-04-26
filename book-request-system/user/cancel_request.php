<?php
/**
 * Cancel a pending book request (user only)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireUser();

$userId    = $_SESSION['user_id'];
$requestId = intval($_GET['id'] ?? 0);

if ($requestId > 0) {
    try {
        // Only cancel if it belongs to this user AND is still pending
        $stmt = $pdo->prepare(
            "DELETE FROM book_requests WHERE id = ? AND user_id = ? AND status = 'pending'"
        );
        $stmt->execute([$requestId, $userId]);

        if ($stmt->rowCount() > 0) {
            // Success: redirect with message
            header('Location: dashboard.php?msg=cancelled');
        } else {
            header('Location: dashboard.php?msg=error');
        }
    } catch (PDOException $e) {
        error_log("Cancel request error: " . $e->getMessage());
        header('Location: dashboard.php?msg=error');
    }
} else {
    header('Location: dashboard.php');
}
exit();
