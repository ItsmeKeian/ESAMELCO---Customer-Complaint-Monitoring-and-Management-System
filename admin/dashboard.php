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
    <!-- Leaflet CSS (map) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <style>
        /* Refresh icon spin animation */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 0.6s linear; }
    </style>
</head>
<body>



<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR OVERLAY (mobile tap-to-close)
═══════════════════════════════════════════════════════════════ -->
<div id="sidebarOverlay"></div>

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════════ -->
<nav class="sidebar">

    <!-- Brand -->
    <a href="dashboard.php" class="sidebar-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO Logo">
        <span class="sidebar-brand-text">ESAMELCO<br>Complaint System</span>
    </a>

    <!-- Main Menu -->
    <div class="sidebar-label">Main Menu</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
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
            <a href="dispatch_crew.php" class="nav-link">
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

    <!-- Management -->
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

    <!-- Settings -->
    <div class="sidebar-label">System</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        </li>
    </ul>

    <!-- Logout -->
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
            <!-- Hamburger (mobile) -->
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Dashboard</h1>
        </div>

        <div class="topnav-right">
            <!-- Refresh -->
            <button class="btn-notif" id="btn-refresh" title="Refresh data">
                <i class="bi bi-arrow-clockwise"></i>
            </button>

            <!-- Notifications -->
            <button class="btn-notif" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notif-dot"></span>
            </button>

            <!-- Admin Info -->
            <div class="admin-pill">
                <div class="admin-avatar" id="admin-initials">A</div>
                <span class="admin-name" id="admin-name-display">Admin</span>
            </div>
        </div>
    </header>

    <!-- ── Content Area ── -->
    <main class="content-area">

        <!-- ── Row 1: Stat Cards ── -->
        <div class="row g-3 mb-4">

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="bi bi-clipboard2-data"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total</div>
                        <div class="stat-value" id="stat-total">—</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value" id="stat-pending">—</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon ongoing">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Ongoing</div>
                        <div class="stat-value" id="stat-ongoing">—</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon resolved">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Resolved</div>
                        <div class="stat-value" id="stat-resolved">—</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Row 2: Line Chart + Doughnut Chart ── -->
        <div class="row g-3 mb-4">

            <!-- Monthly Complaints Line Chart -->
            <div class="col-12 col-lg-7">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-graph-up me-2 text-success"></i>Complaints — Last 6 Months</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaints by Type Doughnut -->
            <div class="col-12 col-lg-5">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-pie-chart me-2 text-success"></i>Complaints by Type</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="chart-container">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Row 3: Live Crew Map ── -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="section-card">
                    <div class="section-card-header">
                        <h6><i class="bi bi-geo-alt me-2 text-success"></i>Live Crew Location Map</h6>
                        <div style="display:flex; gap:12px; font-size:0.78rem; align-items:center;">
                            <span><span style="display:inline-block;width:10px;height:10px;background:#1a6b2f;border-radius:50%;margin-right:4px;"></span>Available</span>
                            <span><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:50%;margin-right:4px;"></span>Busy</span>
                        </div>
                    </div>
                    <div id="crewMap"></div>
                </div>
            </div>
        </div>

        <!-- ── Row 4: Recent Complaints Table ── -->
        <div class="row g-3">
            <div class="col-12">
                <div class="section-card">
                    <div class="section-card-header">
                        <h6><i class="bi bi-table me-2 text-success"></i>Recent Complaints</h6>
                        <a href="complaints.php" style="font-size:0.82rem; color:#1a6b2f; text-decoration:none; font-weight:600;">
                            View all <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table table-complaints mb-0" style="min-width:700px;">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Consumer</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Assigned Crew</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                </tr>
                            </thead>
                            <tbody id="complaints-tbody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<!-- jQuery -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS (map) -->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Dashboard JS -->
<script src="../js/admin/dashboard.js"></script>

</body>
</html>