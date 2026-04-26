<?php
/**
 * Super Admin - Manage Admins
 * View, add, delete admins (cannot delete superadmin)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireSuperAdmin();

$message = '';
$msgType = 'success';

// Handle add new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $newUsername = trim($_POST['new_username'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');

    if (empty($newUsername) || empty($newPassword)) {
        $message = 'Username and password are required.';
        $msgType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters.';
        $msgType = 'danger';
    } else {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'admin')");
            $stmt->execute([$newUsername, $hashed]);
            $message = "Admin '{$newUsername}' added successfully.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'That username is already taken.';
            } else {
                $message = 'Failed to add admin.';
            }
            $msgType = 'danger';
            error_log("Add admin error: " . $e->getMessage());
        }
    }
}

// Handle delete admin (cannot delete superadmin)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->execute([$deleteId]);
        $target = $stmt->fetch();

        if ($target && $target['role'] === 'superadmin') {
            $message = 'Cannot delete the Super Admin account.';
            $msgType = 'danger';
        } else {
            $pdo->prepare("DELETE FROM admins WHERE id = ? AND role = 'admin'")->execute([$deleteId]);
            $message = 'Admin deleted successfully.';
        }
    } catch (PDOException $e) {
        error_log("Delete admin error: " . $e->getMessage());
        $message = 'Failed to delete admin.';
        $msgType = 'danger';
    }
}

// Fetch all admins
$admins = [];
try {
    $stmt = $pdo->query("SELECT * FROM admins WHERE role = 'admin' ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch admins error: " . $e->getMessage());
}

$pageTitle = 'Manage Admins - Super Admin';
?>
<?php include '../includes/header.php'; ?>

<nav class="navbar" style="background: linear-gradient(90deg, #0d1b2a, #1a0d08, #0d1b2a);">
    <a class="navbar-brand" href="dashboard.php" style="color:#ffd700;">
        <i class="fas fa-crown"></i> Super Admin
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Overview</a></li>
        <li><a href="manage_requests.php"><i class="fas fa-book"></i> Requests</a></li>
        <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="manage_admins.php" class="active"><i class="fas fa-user-shield"></i> Admins</a></li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Manage Admins</h1>
        <p>Add new admin accounts or remove existing ones. You cannot delete the Super Admin.</p>
        <div class="divider"></div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?>">
            <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= clean($message) ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 2fr; gap:28px; align-items:start;">

        <!-- Add Admin Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus"></i>
                <h2>Add New Admin</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="new_username" class="form-control"
                            placeholder="Admin username"
                            value="<?= clean($_POST['new_username'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="new_password" class="form-control"
                            placeholder="Min. 6 characters" required>
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-gold" style="width:100%; justify-content:center;">
                        <i class="fas fa-plus-circle"></i> Add Admin
                    </button>
                </form>
            </div>
        </div>

        <!-- Admins List -->
        <div>
            <h2 class="section-title">Current Admins</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-user-shield"></i>
                                        <p>No admins found. Add one using the form.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $i => $admin): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div style="width:34px;height:34px; background:var(--navy-light); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-weight:700;">
                                                <?= strtoupper(substr($admin['username'], 0, 1)) ?>
                                            </div>
                                            <strong><?= clean($admin['username']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-role">Admin</span>
                                    </td>
                                    <td style="font-size:0.83rem;"><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                                    <td>
                                        <a href="manage_admins.php?delete=<?= $admin['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete admin <?= clean($admin['username']) ?>?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media(max-width:768px) {
    .page-wrapper > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>

<?php include '../includes/footer.php'; ?>
