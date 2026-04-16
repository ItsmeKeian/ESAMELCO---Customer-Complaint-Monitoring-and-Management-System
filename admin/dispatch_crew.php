<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESAMELCO - Customer Complaint Monitoring and Management System</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Dashboard CSS (shared sidebar/topnav styles) -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_dispatch_crew.css">

    
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
            <a href="crew_member.php" class="nav-link">
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