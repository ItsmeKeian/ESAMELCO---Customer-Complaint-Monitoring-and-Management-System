<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_live_tracking.css">

   
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
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="complaints.php" class="nav-link">
                <i class="bi bi-exclamation-circle"></i>Complaints
                <span class="nav-badge" id="nav-badge-complaints">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dispatch_crew.php" class="nav-link">
                <i class="bi bi-send"></i>Dispatch Crew
            </a>
        </li>
        <li class="nav-item">
            <a href="live_tracking.php" class="nav-link active">
                <i class="bi bi-geo-alt"></i>Live Tracking
            </a>
        </li>
    </ul>

    <div class="sidebar-label">Management</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="crew.php" class="nav-link">
                <i class="bi bi-people"></i>Crew Members
            </a>
        </li>
        <li class="nav-item">
            <a href="consumers.php" class="nav-link">
                <i class="bi bi-person-lines-fill"></i>Consumers
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>Reports
            </a>
        </li>
    </ul>

    <div class="sidebar-label">System</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear"></i>Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="../php/logout.php">
            <i class="bi bi-box-arrow-left"></i>Log Out
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
            <h1 class="page-title">Live Tracking</h1>
        </div>
        <div class="topnav-right">
            <span class="last-updated">
                Updated: <span id="last-updated">—</span>
            </span>
            <button class="btn-notif" id="btn-refresh" title="Refresh now">
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
                <h5 class="mb-0 fw-bold">Live Crew Tracking</h5>
                <small class="text-muted">Real-time location of all active maintenance crews — auto-refreshes every 10 seconds</small>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="track-stat-row">
            <div class="track-stat">
                <div class="track-stat-icon total">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="track-stat-label">Total Crew</div>
                    <div class="track-stat-value" id="count-total">—</div>
                </div>
            </div>
            <div class="track-stat">
                <div class="track-stat-icon busy">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <div class="track-stat-label">On Duty</div>
                    <div class="track-stat-value" id="count-busy">—</div>
                </div>
            </div>
            <div class="track-stat">
                <div class="track-stat-icon available">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="track-stat-label">Available</div>
                    <div class="track-stat-value" id="count-available">—</div>
                </div>
            </div>
        </div>

        <!-- Map + Crew Sidebar -->
        <div class="tracking-layout">

            <!-- Map -->
            <div class="map-card">
                <div class="map-card-header">
                    <h6><i class="bi bi-map me-2 text-success"></i>Crew Location Map</h6>
                    <div class="map-legend">
                        <span><span class="legend-dot" style="background:#1a6b2f;"></span>Available</span>
                        <span><span class="legend-dot" style="background:#f59e0b;"></span>On Duty</span>
                        <span><span class="legend-dot" style="background:#dc3545;"></span>Complaint</span>
                    </div>
                </div>
                <div id="trackingMap"></div>
            </div>

            <!-- Crew List Sidebar -->
            <div class="crew-sidebar">
                <div class="crew-sidebar-header">
                    <h6><i class="bi bi-person-lines-fill me-2 text-success"></i>Active Crew</h6>
                </div>
                <div id="crew-sidebar-list">
                    <p class="text-muted text-center p-4" style="font-size:0.85rem;">Loading crew...</p>
                </div>
            </div>

        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<!-- Live Tracking JS -->
<script src="../js/admin_live_tracking.js"></script>

</body>
</html>