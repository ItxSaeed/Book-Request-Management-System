<?php
/**
 * Request a Book - Category selection + book form
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
require_once '../includes/auth.php';
requireUser();

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email    = $_SESSION['email'];

$error   = '';
$success = '';

// Handle book request form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $category   = trim($_POST['category'] ?? '');
    $book_title = trim($_POST['book_title'] ?? '');

    if (empty($category) || empty($book_title)) {
        $error = 'Please select a category and book title.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO book_requests (user_id, username, email, category, book_title, status)
                 VALUES (?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->execute([$userId, $username, $email, $category, $book_title]);
            $success = 'Your book request has been submitted successfully!';
        } catch (PDOException $e) {
            error_log("Request error: " . $e->getMessage());
            $error = 'Failed to submit request. Please try again.';
        }
    }
}

// Fetch books from DB for a given category (for book title dropdown after JS loads)
$selectedCategory = clean($_GET['cat'] ?? $_POST['category'] ?? '');
$booksInCategory  = [];
if ($selectedCategory) {
    try {
        $stmt = $pdo->prepare("SELECT title, author FROM books WHERE category = ? ORDER BY title ASC");
        $stmt->execute([$selectedCategory]);
        $booksInCategory = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Book fetch error: " . $e->getMessage());
    }
}

$pageTitle = 'Request a Book - BookVault';
?>
<?php include '../includes/header.php'; ?>

<!-- Navbar -->
<nav class="navbar">
    <a class="navbar-brand" href="dashboard.php">
        <i class="fas fa-book-open"></i> BookVault
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
        <li><a href="request_book.php" class="active"><i class="fas fa-plus-circle"></i> Request Book</a></li>
        <li>
            <span style="color:rgba(255,255,255,0.5); font-size:0.85rem; padding:0 8px;">
                <i class="fas fa-user" style="color:var(--gold)"></i>
                <?= clean($username) ?>
            </span>
        </li>
        <li><a href="/book-request-system/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Request a Book</h1>
        <p>Browse books from our library categories and submit your request.</p>
        <div class="divider"></div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= clean($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= clean($success) ?>
            <a href="dashboard.php" style="color:inherit; font-weight:700; margin-left:8px;">View Dashboard →</a>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:28px; align-items:start;" class="request-grid">

        <!-- Step 1: Category & Load Books -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-layer-group"></i>
                <h2>Step 1 — Select Category</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Book Category</label>
                    <select id="categorySelect" class="form-control">
                        <option value="">— Choose a category —</option>
                        <option value="App Development">App Development</option>
                        <option value="Mobile Development">Mobile Development</option>
                        <option value="AI">Artificial Intelligence (AI)</option>
                    </select>
                </div>

                <button id="loadBooksBtn" class="btn btn-primary" onclick="loadBooks()" disabled>
                    <i class="fas fa-download"></i> Load Books
                </button>

                <div id="apiStatus" style="margin-top:14px; display:none;"></div>

                <!-- Rate limit info -->
                <div id="rateLimitInfo" style="margin-top:14px; font-size:0.82rem; color:var(--text-light);">
                    <i class="fas fa-info-circle" style="color:var(--gold)"></i>
                    You can load books up to <strong>5 times per 24 hours</strong>.
                </div>
            </div>
        </div>

        <!-- Step 2: Request Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-paper-plane"></i>
                <h2>Step 2 — Submit Request</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="requestForm">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= clean($username) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= clean($email) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" id="formCategory" class="form-control" required>
                            <option value="">— Select Category —</option>
                            <option value="App Development">App Development</option>
                            <option value="Mobile Development">Mobile Development</option>
                            <option value="AI">Artificial Intelligence (AI)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Book Title</label>
                        <select name="book_title" id="bookTitleSelect" class="form-control" required>
                            <option value="">— Load books first —</option>
                        </select>
                    </div>
                    <button type="submit" name="submit_request" class="btn btn-gold" style="width:100%; justify-content:center;">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Books Preview Table -->
    <div id="booksPreview" style="display:none; margin-top:28px;">
        <h2 class="section-title"><i class="fas fa-books" style="color:var(--gold)"></i> Available Books</h2>
        <div class="table-wrapper">
            <table id="booksTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media(max-width:768px) {
    .request-grid { grid-template-columns:1fr !important; }
}
</style>

<script>
// Enable Load Books button when category is selected
document.getElementById('categorySelect').addEventListener('change', function() {
    document.getElementById('loadBooksBtn').disabled = !this.value;
    // Sync with form category
    document.getElementById('formCategory').value = this.value;
});

// Sync form category back
document.getElementById('formCategory').addEventListener('change', function() {
    document.getElementById('categorySelect').value = this.value;
    document.getElementById('loadBooksBtn').disabled = !this.value;
});

/**
 * Load books from server via FormData/fetch
 * PHP handler calls Google Books API and stores results
 */
async function loadBooks() {
    const category = document.getElementById('categorySelect').value;
    if (!category) return;

    const btn = document.getElementById('loadBooksBtn');
    const statusDiv = document.getElementById('apiStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Loading...';
    statusDiv.style.display = 'none';

    const formData = new FormData();
    formData.append('category', category);

    try {
        const response = await fetch('/book-request-system/api/fetch_books.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.error) {
            statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
            statusDiv.style.display = 'block';
        } else {
            // Populate books table and title dropdown
            populateBooksUI(data.books, category);
            statusDiv.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> Loaded ${data.books.length} books successfully!</div>`;
            statusDiv.style.display = 'block';
        }
    } catch (err) {
        statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Failed to load books. Please try again.</div>`;
        statusDiv.style.display = 'block';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sync"></i> Reload Books';
}

function populateBooksUI(books, category) {
    if (!books || books.length === 0) return;

    // Update book title select
    const select = document.getElementById('bookTitleSelect');
    select.innerHTML = '<option value="">— Select a book —</option>';

    // Update preview table
    const tbody = document.getElementById('booksTableBody');
    tbody.innerHTML = '';

    books.forEach((book, i) => {
        // Dropdown option
        const opt = document.createElement('option');
        opt.value = book.title;
        opt.textContent = book.title;
        select.appendChild(opt);

        // Table row
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i + 1}</td>
            <td><strong>${escHtml(book.title)}</strong></td>
            <td>${escHtml(book.author)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-primary" onclick="selectBook('${escHtml(book.title).replace(/'/g,"\\'")}', '${escHtml(category)}')">
                    <i class="fas fa-hand-pointer"></i> Select
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('booksPreview').style.display = 'block';
}

function selectBook(title, category) {
    document.getElementById('bookTitleSelect').value = title;
    document.getElementById('formCategory').value = category;
    // Scroll to form
    document.getElementById('requestForm').scrollIntoView({ behavior: 'smooth' });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>

<?php include '../includes/footer.php'; ?>
