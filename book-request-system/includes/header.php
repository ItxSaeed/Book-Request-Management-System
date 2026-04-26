<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'BookVault' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --navy:       #0d1b2a;
            --navy-mid:   #1b2e45;
            --navy-light: #243b55;
            --gold:       #c9a84c;
            --gold-light: #f0c96b;
            --gold-pale:  #fdf3d9;
            --cream:      #faf8f2;
            --white:      #ffffff;
            --text-dark:  #0d1b2a;
            --text-mid:   #3a4a5c;
            --text-light: #7a8ea0;
            --danger:     #e74c3c;
            --success:    #27ae60;
            --warning:    #f39c12;
            --info:       #2980b9;
            --shadow-sm:  0 2px 8px rgba(13,27,42,0.10);
            --shadow-md:  0 6px 24px rgba(13,27,42,0.14);
            --shadow-lg:  0 16px 48px rgba(13,27,42,0.18);
            --radius:     14px;
            --radius-sm:  8px;
            --transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ---- Navbar ---- */
        .navbar {
            background: var(--navy);
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gold);
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand i { font-size: 1.4rem; }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }
        .navbar-nav a {
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-nav a:hover, .navbar-nav a.active {
            color: var(--gold);
            background: rgba(201,168,76,0.12);
        }
        .navbar-nav .btn-logout {
            background: rgba(231,76,60,0.15);
            color: #ff6b6b;
            border: 1px solid rgba(231,76,60,0.3);
        }
        .navbar-nav .btn-logout:hover {
            background: #e74c3c;
            color: #fff;
        }
        .badge-role {
            background: var(--gold);
            color: var(--navy);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 6px;
        }

        /* ---- Page Wrapper ---- */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        /* ---- Page Header ---- */
        .page-header {
            margin-bottom: 36px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .page-header p {
            color: var(--text-light);
            font-size: 1rem;
        }
        .page-header .divider {
            width: 60px;
            height: 3px;
            background: var(--gold);
            border-radius: 2px;
            margin-top: 12px;
        }

        /* ---- Cards ---- */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(13,27,42,0.06);
            overflow: hidden;
            transition: var(--transition);
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header {
            background: var(--navy);
            color: var(--white);
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
        }
        .card-header i { color: var(--gold); font-size: 1.2rem; }
        .card-body { padding: 28px; }

        /* ---- Stat Cards ---- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(13,27,42,0.06);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: var(--gold);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-icon {
            width: 56px; height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-icon.blue   { background: rgba(41,128,185,0.12); color: var(--info); }
        .stat-icon.gold   { background: rgba(201,168,76,0.15); color: var(--gold); }
        .stat-icon.green  { background: rgba(39,174,96,0.12);  color: var(--success); }
        .stat-icon.orange { background: rgba(243,156,18,0.12); color: var(--warning); }
        .stat-icon.red    { background: rgba(231,76,60,0.10);  color: var(--danger); }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.82rem;
            color: var(--text-light);
            margin-top: 4px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ---- Forms ---- */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8ecf0;
            border-radius: var(--radius-sm);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--text-dark);
            background: #fafbfc;
            transition: var(--transition);
            outline: none;
        }
        .form-control:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.15);
        }
        .form-control[readonly] {
            background: #f0f3f6;
            color: var(--text-light);
            cursor: not-allowed;
        }
        select.form-control { cursor: pointer; }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: var(--radius-sm);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--navy);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--navy-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        .btn-gold {
            background: var(--gold);
            color: var(--navy);
        }
        .btn-gold:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(201,168,76,0.35);
        }
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        .btn-success {
            background: var(--success);
            color: var(--white);
        }
        .btn-success:hover { background: #219a52; transform: translateY(-1px); }
        .btn-warning {
            background: var(--warning);
            color: var(--white);
        }
        .btn-warning:hover { background: #d68910; transform: translateY(-1px); }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--navy);
            color: var(--navy);
        }
        .btn-outline:hover {
            background: var(--navy);
            color: var(--white);
        }
        .btn-sm { padding: 7px 14px; font-size: 0.82rem; }

        /* ---- Alerts ---- */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.92rem;
            font-weight: 500;
        }
        .alert i { margin-top: 2px; flex-shrink: 0; }
        .alert-danger  { background: #fdecea; color: #c0392b; border-left: 4px solid #e74c3c; }
        .alert-success { background: #eafaf1; color: #1e8449; border-left: 4px solid #27ae60; }
        .alert-warning { background: #fef9e7; color: #b7770d; border-left: 4px solid #f39c12; }
        .alert-info    { background: #eaf4fb; color: #1a5276; border-left: 4px solid #2980b9; }

        /* ---- Tables ---- */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(13,27,42,0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }
        thead { background: var(--navy); }
        thead th {
            color: rgba(255,255,255,0.85);
            padding: 14px 18px;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        thead th:first-child { color: var(--gold); }
        tbody tr {
            border-bottom: 1px solid #f0f3f6;
            transition: var(--transition);
        }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child { border-bottom: none; }
        tbody td {
            padding: 14px 18px;
            font-size: 0.9rem;
            color: var(--text-mid);
        }

        /* ---- Status Badges ---- */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending     { background: #fef3cd; color: #856404; }
        .status-in_progress { background: #cce5ff; color: #004085; }
        .status-completed   { background: #d4edda; color: #155724; }

        /* ---- Auth Pages ---- */
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 50%, #1a3a5c 100%);
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .auth-page::before {
            content: '';
            position: absolute;
            top: -40%; left: -20%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(201,168,76,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .auth-page::after {
            content: '';
            position: absolute;
            bottom: -30%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        .auth-box {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 440px;
            padding: 50px 44px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 36px;
        }
        .auth-logo .icon-wrap {
            width: 70px; height: 70px;
            background: var(--navy);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(13,27,42,0.25);
        }
        .auth-logo .icon-wrap i { font-size: 2rem; color: var(--gold); }
        .auth-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--navy);
            font-weight: 700;
        }
        .auth-logo p { color: var(--text-light); font-size: 0.88rem; margin-top: 4px; }
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.88rem;
            color: var(--text-light);
        }
        .auth-footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover { text-decoration: underline; }

        /* ---- Misc Utilities ---- */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mt-3 { margin-top: 24px; }
        .mt-4 { margin-top: 32px; }
        .mb-3 { margin-bottom: 24px; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 10px; }
        .flex-wrap { flex-wrap: wrap; }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--navy);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(to right, var(--gold), transparent);
        }

        /* ---- Loading Spinner ---- */
        .spinner {
            display: inline-block;
            width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---- Empty State ---- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 16px; color: #d0dae4; }
        .empty-state p { font-size: 1rem; }

        /* ---- Notification Banner ---- */
        .notification-banner {
            background: linear-gradient(135deg, #1b2e45, #243b55);
            color: white;
            padding: 14px 22px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid var(--gold);
            animation: slideIn 0.4s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .notification-banner i { color: var(--gold); }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .page-wrapper { padding: 20px 16px; }
            .auth-box { padding: 36px 24px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
