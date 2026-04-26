<?php
/**
 * Super Admin - Manage Users
 * View all users, reset password, delete user
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireSuperAdmin();

$message = '';
$msgType = 'success';
$newPasswordDisplay = '';

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $userId = intval($_POST['user_id'] ?? 0);
    if ($userId > 0) {
        // Generate a random new password
        $newRaw    = 'User@' . rand(10000, 99999);
        $newHashed = password_hash($newRaw, PASSWORD_DEFAULT);
        try {
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHashed, $userId]);
            $message = "Password reset successfully. New temporary password: ";
            $newPasswordDisplay = $newRaw;
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            $message = 'Failed to reset password.';
            $msgType = 'danger';
        }
    }
}

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([intval($_GET['delete'])]);
        $message = 'User deleted successfully.';
    } catch (PDOException $e) {
        error_log("Delete user error: " . $e->getMessage());
        $message = 'Failed to delete user.';
        $msgType = 'danger';
    }
}

// Fetch all users with request count
$users = [];
try {
    $stmt = $pdo->query(
        "SELECT u.*, COUNT(br.id) AS request_count
         FROM users u
         LEFT JOIN book_requests br ON u.id = br.user_id
         GROUP BY u.id
         ORDER BY u.created_at DESC"
    );
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch users error: " . $e->getMessage());
}

$pageTitle = 'Manage Users - Super Admin';
?>
<?php include '../includes/header.php'; ?>

<nav class="navbar" style="background: linear-gradient(90deg, #0d1b2a, #1a0d08, #0d1b2a);">
    <a class="navbar-brand" href="dashboard.php" style="color:#ffd700;">
        <i class="fas fa-crown"></i> Super Admin
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Overview</a></li>
        <li><a href="manage_requests.php"><i class="fas fa-book"></i> Requests</a></li>
        <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Manage Users</h1>
        <p>View all registered users, reset passwords, and remove accounts.</p>
        <div class="divider"></div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?>">
            <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= clean($message) ?>
            <?php if ($newPasswordDisplay): ?>
                <strong style="font-size:1.1rem; background:#fff; padding:4px 12px; border-radius:6px; margin-left:8px; letter-spacing:1px; color:var(--navy);">
                    <?= clean($newPasswordDisplay) ?>
                </strong>
                <span style="font-size:0.82rem; margin-left:6px;">(Share this with the user securely)</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Requests</th>
                    <th>Joined</th>
                    <th>Reset Password</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-users-slash"></i><p>No users found.</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:34px;height:34px; background:var(--navy); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-weight:700; font-size:0.9rem;">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <strong><?= clean($user['username']) ?></strong>
                                </div>
                            </td>
                            <td><?= clean($user['email']) ?></td>
                            <td>
                                <span style="background:rgba(41,128,185,0.12); color:var(--info); padding:3px 10px; border-radius:12px; font-size:0.82rem; font-weight:700;">
                                    <?= $user['request_count'] ?>
                                </span>
                            </td>
                            <td style="font-size:0.83rem;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="reset_password"
                                        class="btn btn-warning btn-sm"
                                        onclick="return confirm('Reset password for <?= clean($user['username']) ?>?')">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="manage_users.php?delete=<?= $user['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Permanently delete user <?= clean($user['username']) ?> and ALL their data?')">
                                    <i class="fas fa-user-times"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
