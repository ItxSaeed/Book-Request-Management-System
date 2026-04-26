<?php
/**
 * Admin Login
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';

// Already logged in as admin
if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin') {
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
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND role = 'admin'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role']     = 'admin';
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid admin credentials.';
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Admin Login - BookVault';
?>
<?php include '../includes/header.php'; ?>

<div class="auth-page" style="background: linear-gradient(135deg, #0d1b2a 0%, #1a3a5c 60%, #0d2d50 100%);">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="icon-wrap" style="background: linear-gradient(135deg, #1b2e45, #243b55);">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Panel</h1>
            <p>Sign in with your admin credentials</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= clean($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Admin Username</label>
                <input type="text" name="username" class="form-control"
                    placeholder="Enter admin username"
                    value="<?= clean($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px;">
                <i class="fas fa-sign-in-alt"></i> Admin Sign In
            </button>
        </form>

        <div class="auth-footer mt-2">
            <a href="/book-request-system/user/login.php" style="color:var(--text-light);">← Back to User Login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
