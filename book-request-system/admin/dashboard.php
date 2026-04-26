<?php
/**
 * Admin Dashboard - Statistics Overview (Read Only)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireAdmin();

$stats = [
    'total_users'    => 0,
    'total_requests' => 0,
    'in_progress'    => 0,
    'completed'      => 0,
    'pending'        => 0,
];

$recentRequests = [];

try {
    // Total unique users who made requests
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM book_requests");
    $stats['total_users'] = $stmt->fetch()['cnt'];

    // Total requests
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM book_requests");
    $stats['total_requests'] = $stmt->fetch()['cnt'];

    // In progress
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM book_requests WHERE status = 'in_progress'");
    $stats['in_progress'] = $stmt->fetch()['cnt'];

    // Completed
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM book_requests WHERE status = 'completed'");
    $stats['completed'] = $stmt->fetch()['cnt'];

    // Pending
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM book_requests WHERE status = 'pending'");
    $stats['pending'] = $stmt->fetch()['cnt'];

    // Recent 10 requests
    $stmt = $pdo->query(
        "SELECT br.*, u.email as user_email
         FROM book_requests br
         JOIN users u ON br.user_id = u.id
         ORDER BY br.created_at DESC LIMIT 10"
    );
    $recentRequests = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}

$pageTitle = 'Admin Dashboard - BookVault';
?>
<?php include '../includes/header.php'; ?>

<!-- Navbar -->
<nav class="navbar">
    <a class="navbar-brand" href="dashboard.php">
        <i class="fas fa-shield-alt"></i> Admin Panel
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php" class="active"><i class="fas fa-chart-bar"></i> Dashboard</a></li>
        <li>
            <span style="color:rgba(255,255,255,0.5); font-size:0.85rem; padding:0 8px;">
                <i class="fas fa-user-shield" style="color:var(--gold)"></i>
                <?= clean($_SESSION['username']) ?>
                <span class="badge-role">Admin</span>
            </span>
        </li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p>System overview — read-only statistics panel.</p>
        <div class="divider"></div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Unique Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-book"></i></div>
            <div>
                <div class="stat-number"><?= $stats['total_requests'] ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-number"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-spinner"></i></div>
            <div>
                <div class="stat-number"><?= $stats['in_progress'] ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-number"><?= $stats['completed'] ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <h2 class="section-title"><i class="fas fa-list" style="color:var(--gold)"></i> Recent Book Requests</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Book Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentRequests)): ?>
                    <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No requests found.</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($recentRequests as $i => $req): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= clean($req['username']) ?></strong></td>
                            <td><?= clean($req['book_title']) ?></td>
                            <td><?= clean($req['category']) ?></td>
                            <td>
                                <span class="status-badge status-<?= clean($req['status']) ?>">
                                    <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Progress Bar Summary -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i>
            <h2>Request Status Overview</h2>
        </div>
        <div class="card-body">
            <?php
            $total = $stats['total_requests'] ?: 1;
            $pPct  = round(($stats['pending'] / $total) * 100);
            $iPct  = round(($stats['in_progress'] / $total) * 100);
            $cPct  = round(($stats['completed'] / $total) * 100);
            ?>
            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:6px;">
                    <span><i class="fas fa-clock" style="color:var(--warning)"></i> Pending</span>
                    <span><?= $stats['pending'] ?> (<?= $pPct ?>%)</span>
                </div>
                <div style="background:#f0f3f6; border-radius:6px; height:10px; overflow:hidden;">
                    <div style="height:100%; width:<?= $pPct ?>%; background:var(--warning); border-radius:6px; transition:width 1s;"></div>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:6px;">
                    <span><i class="fas fa-spinner" style="color:var(--info)"></i> In Progress</span>
                    <span><?= $stats['in_progress'] ?> (<?= $iPct ?>%)</span>
                </div>
                <div style="background:#f0f3f6; border-radius:6px; height:10px; overflow:hidden;">
                    <div style="height:100%; width:<?= $iPct ?>%; background:var(--info); border-radius:6px;"></div>
                </div>
            </div>
            <div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:6px;">
                    <span><i class="fas fa-check-circle" style="color:var(--success)"></i> Completed</span>
                    <span><?= $stats['completed'] ?> (<?= $cPct ?>%)</span>
                </div>
                <div style="background:#f0f3f6; border-radius:6px; height:10px; overflow:hidden;">
                    <div style="height:100%; width:<?= $cPct ?>%; background:var(--success); border-radius:6px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
