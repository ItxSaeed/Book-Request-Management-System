<?php
/**
 * User Dashboard - View own book requests
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireUser();

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch all requests for this user
$requests = [];
$notifications = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM book_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $requests = $stmt->fetchAll();

    // Check for unread status-change notifications
    $stmt2 = $pdo->prepare(
        "SELECT * FROM book_requests WHERE user_id = ? AND notified = 0 AND status != 'pending'"
    );
    $stmt2->execute([$userId]);
    $notifications = $stmt2->fetchAll();

    // Mark them as notified
    if ($notifications) {
        $pdo->prepare("UPDATE book_requests SET notified = 1 WHERE user_id = ? AND notified = 0 AND status != 'pending'")
            ->execute([$userId]);
    }
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}

// Status counts
$counts = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
foreach ($requests as $r) {
    $counts[$r['status']] = ($counts[$r['status']] ?? 0) + 1;
}

$pageTitle = 'My Dashboard - BookVault';
?>
<?php include '../includes/header.php'; ?>

<!-- Navbar -->
<nav class="navbar">
    <a class="navbar-brand" href="dashboard.php">
        <i class="fas fa-book-open"></i> BookVault
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a></li>
        <li><a href="request_book.php"><i class="fas fa-plus-circle"></i> Request Book</a></li>
        <li>
            <span style="color:rgba(255,255,255,0.5); font-size:0.85rem; padding:0 8px;">
                <i class="fas fa-user" style="color:var(--gold)"></i>
                <?= clean($username) ?>
                <span class="badge-role">User</span>
            </span>
        </li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <h1>My Dashboard</h1>
        <p>Welcome back, <?= clean($username) ?>! Track all your book requests here.</p>
        <div class="divider"></div>
    </div>

    <!-- Notifications -->
    <?php foreach ($notifications as $notif): ?>
        <div class="notification-banner">
            <i class="fas fa-bell"></i>
            Your request for <strong>"<?= clean($notif['book_title']) ?>"</strong> is now
            <strong><?= str_replace('_', ' ', clean($notif['status'])) ?></strong>.
        </div>
    <?php endforeach; ?>

    <!-- Stats Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-list"></i></div>
            <div>
                <div class="stat-number"><?= count($requests) ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-number"><?= $counts['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-spinner"></i></div>
            <div>
                <div class="stat-number"><?= $counts['in_progress'] ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-number"><?= $counts['completed'] ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="d-flex justify-between align-center mb-3">
        <h2 class="section-title" style="flex:1">My Book Requests</h2>
        <a href="request_book.php" class="btn btn-gold btn-sm" style="margin-left:20px;">
            <i class="fas fa-plus"></i> New Request
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-book"></i>
                                <p>No book requests yet. <a href="request_book.php" style="color:var(--gold);">Request your first book!</a></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $i => $req): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= clean($req['book_title']) ?></strong></td>
                            <td><?= clean($req['category']) ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'pending'     => ['pending', 'fa-clock'],
                                    'in_progress' => ['in_progress', 'fa-spinner'],
                                    'completed'   => ['completed', 'fa-check-circle'],
                                ];
                                $sc = $statusMap[$req['status']] ?? ['pending', 'fa-clock'];
                                ?>
                                <span class="status-badge status-<?= $sc[0] ?>">
                                    <i class="fas <?= $sc[1] ?>"></i>
                                    <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                            <td>
                                <?php if ($req['status'] === 'pending'): ?>
                                    <!-- Cancel button for pending -->
                                    <a href="cancel_request.php?id=<?= $req['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Cancel this request?')">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>

                                <?php elseif ($req['status'] === 'in_progress'): ?>
                                    <!-- In progress indicator -->
                                    <span style="display:inline-flex; align-items:center; gap:6px; color:#2980b9; font-size:0.82rem; font-weight:600;">
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    </span>

                                <?php elseif ($req['status'] === 'completed'): ?>
                                    <!-- Read Book button for completed -->
                                    <?php
                                    $searchQuery = urlencode($req['book_title']);
                                    $googleBooksUrl = "https://www.google.com/search?q=" . $searchQuery . "+book+read+online&btnI=1";
                                    $googleBooksSearchUrl = "https://books.google.com/books?q=" . $searchQuery;
                                    ?>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <a href="<?= $googleBooksSearchUrl ?>"
                                           target="_blank"
                                           class="btn btn-success btn-sm"
                                           title="Open on Google Books">
                                            <i class="fas fa-book-open"></i> Read Book
                                        </a>
                                        <a href="https://www.google.com/search?q=<?= $searchQuery ?>+PDF+free"
                                           target="_blank"
                                           class="btn btn-sm"
                                           style="background:var(--navy); color:#fff;"
                                           title="Search online">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
