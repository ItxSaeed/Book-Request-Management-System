<?php
/**
 * Super Admin - Manage All Book Requests
 * Edit status, delete any request
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireSuperAdmin();

$message = '';
$msgType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $requestId = intval($_POST['request_id'] ?? 0);
    $newStatus  = trim($_POST['status'] ?? '');
    $allowed    = ['pending', 'in_progress', 'completed'];

    if ($requestId > 0 && in_array($newStatus, $allowed)) {
        try {
            $pdo->prepare("UPDATE book_requests SET status = ?, notified = 0 WHERE id = ?")
                ->execute([$newStatus, $requestId]);
            $message = 'Request status updated successfully.';
        } catch (PDOException $e) {
            error_log("Update status error: " . $e->getMessage());
            $message = 'Failed to update status.';
            $msgType = 'danger';
        }
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM book_requests WHERE id = ?")->execute([intval($_GET['delete'])]);
        $message = 'Request deleted successfully.';
    } catch (PDOException $e) {
        error_log("Delete request error: " . $e->getMessage());
        $message = 'Failed to delete request.';
        $msgType = 'danger';
    }
}

// Fetch all requests
$requests = [];
try {
    $stmt = $pdo->query(
        "SELECT br.*, u.email as user_email
         FROM book_requests br
         JOIN users u ON br.user_id = u.id
         ORDER BY br.created_at DESC"
    );
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch requests error: " . $e->getMessage());
}

$pageTitle = 'Manage Requests - Super Admin';
?>
<?php include '../includes/header.php'; ?>

<nav class="navbar" style="background: linear-gradient(90deg, #0d1b2a, #1a0d08, #0d1b2a);">
    <a class="navbar-brand" href="dashboard.php" style="color:#ffd700;">
        <i class="fas fa-crown"></i> Super Admin
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Overview</a></li>
        <li><a href="manage_requests.php" class="active"><i class="fas fa-book"></i> Requests</a></li>
        <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Manage Book Requests</h1>
        <p>View, update status, and delete any book request in the system.</p>
        <div class="divider"></div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?>">
            <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= clean($message) ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Book Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i><p>No requests found.</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $i => $req): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= clean($req['username']) ?></strong></td>
                            <td style="font-size:0.83rem;"><?= clean($req['user_email']) ?></td>
                            <td><?= clean($req['book_title']) ?></td>
                            <td>
                                <span style="background:rgba(201,168,76,0.12); color:var(--gold); padding:3px 10px; border-radius:12px; font-size:0.78rem; font-weight:600;">
                                    <?= clean($req['category']) ?>
                                </span>
                            </td>
                            <td>
                                <!-- Inline status update form -->
                                <form method="POST" action="" style="display:inline-flex; gap:6px; align-items:center;">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <select name="status" class="form-control" style="padding:5px 10px; font-size:0.82rem; width:auto;">
                                        <option value="pending"     <?= $req['status']==='pending'     ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= $req['status']==='in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed"   <?= $req['status']==='completed'   ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>
                            <td style="font-size:0.83rem;"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                            <td>
                                <a href="manage_requests.php?delete=<?= $req['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this request permanently?')">
                                    <i class="fas fa-trash"></i>
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
