<?php
/**
 * User Login
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Fetch user by username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = 'user';

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Login - BookVault';
?>
<?php include '../includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="icon-wrap">
                <i class="fas fa-book-open"></i>
            </div>
            <h1>BookVault</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= clean($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                    placeholder="Enter your username"
                    value="<?= clean($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center; padding:14px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register Now</a>
        </div>
        <div class="auth-footer mt-1">
            <a href="/book-request-system/admin/login.php" style="color:var(--text-light);">Admin Login</a>
            &nbsp;|&nbsp;
            <a href="/book-request-system/superadmin/login.php" style="color:var(--text-light);">Super Admin</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
