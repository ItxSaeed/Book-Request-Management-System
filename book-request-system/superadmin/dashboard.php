<?php
/**
 * Super Admin Dashboard - Full Control Overview
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireSuperAdmin();

$stats = ['users' => 0, 'admins' => 0, 'requests' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

try {
    $stats['users']       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['admins']      = $pdo->query("SELECT COUNT(*) FROM admins WHERE role='admin'")->fetchColumn();
    $stats['requests']    = $pdo->query("SELECT COUNT(*) FROM book_requests")->fetchColumn();
    $stats['pending']     = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status='pending'")->fetchColumn();
    $stats['in_progress'] = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status='in_progress'")->fetchColumn();
    $stats['completed']   = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status='completed'")->fetchColumn();
} catch (PDOException $e) {
    error_log("SA dashboard error: " . $e->getMessage());
}

$pageTitle = 'Super Admin Dashboard - BookVault';
?>
<?php include '../includes/header.php'; ?>

<!-- Super Admin Navbar -->
<nav class="navbar" style="background: linear-gradient(90deg, #0d1b2a, #1a0d08, #0d1b2a);">
    <a class="navbar-brand" href="dashboard.php" style="color:#ffd700;">
        <i class="fas fa-crown"></i> Super Admin
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Overview</a></li>
        <li><a href="manage_requests.php"><i class="fas fa-book"></i> Requests</a></li>
        <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Admins</a></li>
        <li>
            <span style="color:rgba(255,255,255,0.5); font-size:0.85rem; padding:0 8px;">
                <i class="fas fa-crown" style="color:#ffd700;"></i>
                <?= clean($_SESSION['username']) ?>
                <span class="badge-role" style="background:#ffd700; color:#0d1b2a;">SuperAdmin</span>
            </span>
        </li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Super Admin Overview</h1>
        <p>Complete system control — manage all requests, users, and admins.</p>
        <div class="divider"></div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div><div class="stat-number"><?= $stats['users'] ?></div><div class="stat-label">Total Users</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-user-shield"></i></div>
            <div><div class="stat-number"><?= $stats['admins'] ?></div><div class="stat-label">Admins</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-book"></i></div>
            <div><div class="stat-number"><?= $stats['requests'] ?></div><div class="stat-label">Total Requests</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div><div class="stat-number"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-spinner"></i></div>
            <div><div class="stat-number"><?= $stats['in_progress'] ?></div><div class="stat-label">In Progress</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-number"><?= $stats['completed'] ?></div><div class="stat-label">Completed</div></div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <h2 class="section-title">Quick Management</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px;">
        <a href="manage_requests.php" class="card" style="text-decoration:none; padding:28px; display:flex; align-items:center; gap:18px;">
            <div class="stat-icon gold" style="width:60px;height:60px;font-size:1.6rem;"><i class="fas fa-book-open"></i></div>
            <div>
                <div style="font-family:'Playfair Display',serif; font-size:1.1rem; color:var(--navy); font-weight:700;">Manage Requests</div>
                <div style="color:var(--text-light); font-size:0.85rem; margin-top:4px;">Edit status, delete any request</div>
            </div>
            <i class="fas fa-chevron-right" style="color:var(--gold); margin-left:auto;"></i>
        </a>
        <a href="manage_users.php" class="card" style="text-decoration:none; padding:28px; display:flex; align-items:center; gap:18px;">
            <div class="stat-icon blue" style="width:60px;height:60px;font-size:1.6rem;"><i class="fas fa-users-cog"></i></div>
            <div>
                <div style="font-family:'Playfair Display',serif; font-size:1.1rem; color:var(--navy); font-weight:700;">Manage Users</div>
                <div style="color:var(--text-light); font-size:0.85rem; margin-top:4px;">Reset passwords, remove users</div>
            </div>
            <i class="fas fa-chevron-right" style="color:var(--gold); margin-left:auto;"></i>
        </a>
        <a href="manage_admins.php" class="card" style="text-decoration:none; padding:28px; display:flex; align-items:center; gap:18px;">
            <div class="stat-icon red" style="width:60px;height:60px;font-size:1.6rem;"><i class="fas fa-user-shield"></i></div>
            <div>
                <div style="font-family:'Playfair Display',serif; font-size:1.1rem; color:var(--navy); font-weight:700;">Manage Admins</div>
                <div style="color:var(--text-light); font-size:0.85rem; margin-top:4px;">Add or remove admin accounts</div>
            </div>
            <i class="fas fa-chevron-right" style="color:var(--gold); margin-left:auto;"></i>
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
