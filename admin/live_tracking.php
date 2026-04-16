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

    <style>
        /* ── Refresh spin ───────────────────────────────────────── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 0.6s linear; }

        /* ── Summary stat cards ─────────────────────────────────── */
        .track-stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .track-stat {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .track-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .track-stat-icon.total     { background: #e8f4fd; color: #0d6efd; }
        .track-stat-icon.busy      { background: #fff8e1; color: #f59e0b; }
        .track-stat-icon.available { background: #e8f5e9; color: #1a6b2f; }

        .track-stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .track-stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #212529;
            line-height: 1;
        }

        @media (max-width: 600px) {
            .track-stat-row { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .track-stat { padding: 12px; gap: 8px; }
            .track-stat-value { font-size: 1.3rem; }
        }

        /* ── Main tracking layout: map + sidebar ────────────────── */
        .tracking-layout {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 16px;
            height: calc(100vh - 240px);
            min-height: 460px;
        }

        @media (max-width: 900px) {
            .tracking-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
        }

        /* ── Map container ──────────────────────────────────────── */
        .map-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .map-card-header {
            padding: 12px 18px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .map-card-header h6 {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            color: #212529;
        }

        #trackingMap {
            flex: 1;
            min-height: 400px;
            z-index: 1;
        }

        /* ── Map legend ─────────────────────────────────────────── */
        .map-legend {
            display: flex;
            gap: 16px;
            align-items: center;
            font-size: 0.76rem;
            color: #6c757d;
        }

        .legend-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.15);
        }

        /* ── Crew sidebar ───────────────────────────────────────── */
        .crew-sidebar {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .crew-sidebar-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            flex-shrink: 0;
        }

        .crew-sidebar-header h6 {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 700;
            color: #212529;
        }

        #crew-sidebar-list {
            flex: 1;
            overflow-y: auto;
        }

        /* ── Crew list items ────────────────────────────────────── */
        .crew-list-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: background 0.15s;
        }

        .crew-list-item:last-child { border-bottom: none; }
        .crew-list-item:hover      { background: #f8fdf9; }
        .crew-list-item.active     { background: #e8f5e9; }

        .crew-list-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .crew-list-info  { flex: 1; min-width: 0; }

        .crew-list-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .crew-list-meta {
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
        }

        .crew-list-seen {
            font-size: 0.70rem;
            color: #adb5bd;
            margin-top: 1px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Last updated label ─────────────────────────────────── */
        .last-updated {
            font-size: 0.76rem;
            color: #adb5bd;
        }
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
<script src="../js/live_tracking.js"></script>

</body>
</html>