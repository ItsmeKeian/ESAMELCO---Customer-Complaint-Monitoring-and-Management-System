<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Crew — ESAMELCO</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Dashboard CSS (shared sidebar/topnav styles) -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <style>
        /* ── Two-panel layout ───────────────────────────────────── */
        .dispatch-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .dispatch-grid { grid-template-columns: 1fr; }
        }

        /* ── Panel card ─────────────────────────────────────────── */
        .panel-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .panel-header {
            padding: 14px 18px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-header h6 {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            color: #212529;
        }

        .panel-body {
            padding: 0;
            max-height: 620px;
            overflow-y: auto;
        }

        /* ── Complaint cards ────────────────────────────────────── */
        .complaint-card {
            padding: 14px 18px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.15s;
        }

        .complaint-card:last-child { border-bottom: none; }

        .complaint-card:hover    { background: #f8fdf9; }
        .complaint-card.selected { background: #e8f5e9; border-left: 3px solid #1a6b2f; }

        .complaint-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .ticket-no {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1a6b2f;
        }

        .complaint-type {
            font-size: 0.92rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
        }

        .complaint-consumer {
            font-size: 0.80rem;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .complaint-desc {
            font-size: 0.82rem;
            color: #495057;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            margin-bottom: 6px;
        }

        .complaint-meta {
            display: flex;
            gap: 12px;
            font-size: 0.76rem;
            color: #adb5bd;
        }

        .gps-ok   { color: #1a6b2f; }
        .gps-none { color: #adb5bd; }

        /* ── Status badges ──────────────────────────────────────── */
        .badge-status {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-pending { background: #fff8e1; color: #b45309; }

        /* ── Pending count badge ────────────────────────────────── */
        #pending-count {
            background: #ffc107;
            color: #212529;
            font-size: 0.70rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 6px;
        }

        /* ── Crew panel placeholder ─────────────────────────────── */
        #crew-panel-placeholder {
            padding: 60px 20px;
            text-align: center;
            color: #adb5bd;
        }

        #crew-panel-placeholder i  { font-size: 2.5rem; }
        #crew-panel-placeholder p  { font-size: 0.85rem; margin-top: 10px; }

        /* ── Selected ticket label ──────────────────────────────── */
        #selected-ticket {
            font-size: 0.78rem;
            color: #6c757d;
            font-weight: 500;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Crew cards ─────────────────────────────────────────── */
        .crew-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.15s;
        }

        .crew-card:last-child  { border-bottom: none; }
        .crew-card:hover       { background: #f8fdf9; }

        .crew-avatar {
            width: 42px;
            height: 42px;
            background: #1a6b2f;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .crew-info  { flex: 1; min-width: 0; }

        .crew-name {
            font-size: 0.90rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .crew-meta {
            font-size: 0.78rem;
            color: #6c757d;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ── Nearest badge ──────────────────────────────────────── */
        .nearest-badge {
            background: #e8f5e9;
            color: #1a6b2f;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            border: 1px solid #a5d6a7;
        }

        /* ── Dispatch button ────────────────────────────────────── */
        .btn-dispatch {
            background: #1a6b2f;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .btn-dispatch:hover    { background: #155724; }
        .btn-dispatch:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── Info banner ────────────────────────────────────────── */
        .info-banner {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: #1a6b2f;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .info-banner i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
    </style>
</head>
<body>

<!-- ── Sidebar Overlay (mobile) ── -->
<div id="sidebarOverlay"></div>

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════════ -->
<nav class="sidebar">

    <a href="dashboard.php" class="sidebar-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO Logo">
        <span class="sidebar-brand-text">ESAMELCO<br>Complaint System</span>
    </a>

    <div class="sidebar-label">Main Menu</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="complaints.php" class="nav-link">
                <i class="bi bi-exclamation-circle"></i>
                Complaints
                <span class="nav-badge" id="nav-badge-complaints">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dispatch_crew.php" class="nav-link active">
                <i class="bi bi-send"></i>
                Dispatch Crew
            </a>
        </li>
        <li class="nav-item">
            <a href="live_tracking.php" class="nav-link">
                <i class="bi bi-geo-alt"></i>
                Live Tracking
            </a>
        </li>
    </ul>

    <div class="sidebar-label">Management</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="crew.php" class="nav-link">
                <i class="bi bi-people"></i>
                Crew Members
            </a>
        </li>
        <li class="nav-item">
            <a href="consumers.php" class="nav-link">
                <i class="bi bi-person-lines-fill"></i>
                Consumers
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>
        </li>
    </ul>

    <div class="sidebar-label">System</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="../php/logout.php">
            <i class="bi bi-box-arrow-left"></i>
            Log Out
        </a>
    </div>

</nav>

<!-- ═══════════════════════════════════════════════════════════
     MAIN WRAPPER
═══════════════════════════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- ── Top Navbar ── -->
    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Dispatch Crew</h1>
        </div>
        <div class="topnav-right">
            <button class="btn-notif" id="btn-refresh" title="Refresh">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button class="btn-notif" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notif-dot"></span>
            </button>
            <div class="admin-pill">
                <div class="admin-avatar" id="admin-initials">A</div>
                <span class="admin-name" id="admin-name-display">Admin</span>
            </div>
        </div>
    </header>

    <!-- ── Content Area ── -->
    <main class="content-area">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0 fw-bold">Dispatch Crew</h5>
                <small class="text-muted">Select a pending complaint then assign the nearest available crew</small>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="info-banner">
            <i class="bi bi-info-circle-fill"></i>
            <span>
                Click any complaint on the left to see available crew sorted by distance and ETA.
                The crew nearest to the complaint location is marked <strong>Nearest</strong>.
            </span>
        </div>

        <!-- Two-panel layout -->
        <div class="dispatch-grid">

            <!-- ── LEFT: Pending Complaints ── -->
            <div class="panel-card">
                <div class="panel-header">
                    <h6>
                        <i class="bi bi-exclamation-circle me-2 text-success"></i>
                        Pending Complaints
                        <span id="pending-count">0</span>
                    </h6>
                </div>
                <div class="panel-body" id="complaints-list">
                    <!-- Filled by dispatch_crew.js -->
                </div>
            </div>

            <!-- ── RIGHT: Available Crew ── -->
            <div class="panel-card">
                <div class="panel-header">
                    <h6><i class="bi bi-people me-2 text-success"></i>Available Crew</h6>
                    <span id="selected-ticket">Select a complaint first</span>
                </div>
                <div class="panel-body">

                    <!-- Placeholder (before a complaint is selected) -->
                    <div id="crew-panel-placeholder">
                        <i class="bi bi-arrow-left-circle"></i>
                        <p>Select a complaint from the left<br>to view available crew and ETA.</p>
                    </div>

                    <!-- Crew list (shown after complaint is selected) -->
                    <div id="crew-panel-content" style="display:none;">
                        <div id="crew-list">
                            <!-- Filled by dispatch_crew.js -->
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/dispatch_crew.js"></script>

</body>
</html>