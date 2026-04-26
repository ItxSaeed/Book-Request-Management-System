#  BookVault — Book Request Management System

A complete multi-role PHP & MySQL web application built with:
- **Google Books API** integration
- **3 User Roles**: User, Admin, Super Admin
- **PDO** with prepared statements (secure)
- **Beautiful UI** with Playfair Display + DM Sans typography

---

##  Default Login Credentials

| Role        | Username     | Password        |
|-------------|--------------|-----------------|
| Super Admin | `superadmin` | `superadmin123` |
| Admin       | `admin`      | `admin123`      |
| User        | Register via | `/user/register.php` |

---

##  Setup Instructions (XAMPP)

### Step 1 — Place the Project
Copy the `book-request-system` folder into:
```
C:\xampp\htdocs\
```
So the path looks like:
```
C:\xampp\htdocs\book-request-system\
```

### Step 2 — Start XAMPP Services
Open XAMPP Control Panel and start:
-  **Apache**
-  **MySQL**

### Step 3 — Import the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Create a database named: `book_request_system`
4. Click on the new database, then go to **"Import"** tab
5. Click **"Choose File"** and select `database.sql` from the project folder
6. Click **"Go"** — the tables and default accounts will be created

### Step 4 — Configure Database (if needed)
Open `config/db.php` and update if your MySQL password is different:
```php
define('DB_USER', 'root');
define('DB_PASS', '');   // Add your MySQL password here if set
```

### Step 5 — Run the Project
Open your browser and go to:
```
http://localhost/book-request-system/
```
This will redirect you to the user login page.

---

##  Page URLs

| Page                    | URL                                                         |
|-------------------------|-------------------------------------------------------------|
| User Login              | `http://localhost/book-request-system/user/login.php`       |
| User Register           | `http://localhost/book-request-system/user/register.php`    |
| User Dashboard          | `http://localhost/book-request-system/user/dashboard.php`   |
| Request a Book          | `http://localhost/book-request-system/user/request_book.php`|
| Admin Login             | `http://localhost/book-request-system/admin/login.php`      |
| Admin Dashboard         | `http://localhost/book-request-system/admin/dashboard.php`  |
| Super Admin Login       | `http://localhost/book-request-system/superadmin/login.php` |
| Super Admin Dashboard   | `http://localhost/book-request-system/superadmin/dashboard.php` |
| Manage Requests (SA)    | `http://localhost/book-request-system/superadmin/manage_requests.php` |
| Manage Users (SA)       | `http://localhost/book-request-system/superadmin/manage_users.php`    |
| Manage Admins (SA)      | `http://localhost/book-request-system/superadmin/manage_admins.php`   |

---

##  Project Structure

```
book-request-system/
├── config/
│   └── db.php                   ← PDO database connection
├── includes/
│   ├── header.php               ← Common HTML head + CSS
│   ├── footer.php               ← Common footer
│   └── auth.php                 ← Session check helpers
├── user/
│   ├── register.php             ← User registration
│   ├── login.php                ← User login
│   ├── dashboard.php            ← User dashboard (view requests)
│   ├── request_book.php         ← Browse & request books
│   └── cancel_request.php       ← Cancel pending request
├── admin/
│   ├── login.php                ← Admin login
│   └── dashboard.php            ← Admin stats dashboard
├── superadmin/
│   ├── login.php                ← Super Admin login
│   ├── dashboard.php            ← Super Admin overview
│   ├── manage_requests.php      ← Edit/delete all requests
│   ├── manage_users.php         ← Reset passwords, delete users
│   └── manage_admins.php        ← Add/delete admins
├── api/
│   └── fetch_books.php          ← Google Books API handler
├── database.sql                 ← Database schema + seed data
├── index.php                    ← Entry point (redirects to login)
├── logout.php                   ← Session destroy + redirect
└── README.md                    ← This file
```

---

##  Features

### User Side
- Register & login with hashed passwords
- Browse books by category (App Dev, Mobile Dev, AI)
- Books fetched from **Google Books API** via AJAX (FormData)
- Rate limit: max **5 API calls per 24 hours** per user
- Submit book requests → stored in database
- View own requests with status updates
- **Notification** when request status changes
- Cancel pending requests

### Admin Panel
- Login with admin credentials
- View statistics: total users, total requests, in-progress, completed
- Progress bar visualization
- Read-only access (no edit/delete)

### Super Admin Panel
- View full system stats
- **Manage Requests**: Edit status (Pending → In Progress → Completed), delete
- **Manage Users**: View all users, reset passwords (auto-generated), delete accounts
- **Manage Admins**: Add new admin accounts, delete admins (cannot delete superadmin)

---

##  Security Features
- All passwords hashed with `password_hash()` (bcrypt)
- Login verified with `password_verify()`
- All inputs sanitized with `htmlspecialchars()`
- PDO prepared statements (no SQL injection)
- Role-based session checks on every protected page
- Errors logged (never shown to users)

---

##  Troubleshooting

**Books not loading?**
- Make sure Apache can reach the internet (Google Books API is external)
- Check `C:\xampp\php\php.ini` → ensure `allow_url_fopen = On`
- Also enable `extension=openssl` and `extension=curl`

**Login not working?**
- Make sure you imported `database.sql` correctly
- Default super admin password hash is for `superadmin123`
- Default admin password hash is for `admin123`

**Database connection error?**
- Check that MySQL is running in XAMPP
- Verify `config/db.php` credentials match your setup

---
