<?php
/**
 * Super Admin Login
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';

if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND role = 'superadmin'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role']     = 'superadmin';
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid super admin credentials.';
            }
        } catch (PDOException $e) {
            error_log("Superadmin login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Super Admin Login - BookVault';
?>
<?php include '../includes/header.php'; ?>

<div class="auth-page" style="background: linear-gradient(135deg, #0d1b2a 0%, #2c1810 60%, #1a0d08 100%);">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="icon-wrap" style="background: linear-gradient(135deg, #7b341e, #9c4221);">
                <i class="fas fa-crown" style="color:#ffd700;"></i>
            </div>
            <h1 style="color:#0d1b2a;">Super Admin</h1>
            <p>Restricted access — authorized personnel only</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= clean($error) ?></div>
        <?php endif; ?>

        <div class="alert alert-warning" style="margin-bottom:20px; font-size:0.85rem;">
            <i class="fas fa-lock"></i> <strong>High-privilege access.</strong> All actions are logged.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Super Admin Username</label>
                <input type="text" name="username" class="form-control"
                    placeholder="Enter super admin username"
                    value="<?= clean($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn" style="width:100%; justify-content:center; padding:14px; background:linear-gradient(135deg,#7b341e,#9c4221); color:#fff; border-radius:8px; font-size:0.95rem;">
                <i class="fas fa-crown"></i> Super Admin Access
            </button>
        </form>

        <div class="auth-footer mt-2">
            <a href="/book-request-system/user/login.php" style="color:var(--text-light);">← Back to User Login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
