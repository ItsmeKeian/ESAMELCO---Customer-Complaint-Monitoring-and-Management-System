<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin/reports.css">
    
</head>
<body>

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
            <a href="live_tracking.php" class="nav-link">
                <i class="bi bi-geo-alt"></i>Live Tracking
            </a>
        </li>
    </ul>

    <div class="sidebar-label">Management</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="crew_member.php" class="nav-link">
                <i class="bi bi-people"></i>Crew Members
            </a>
        </li>
        <li class="nav-item">
            <a href="consumers.php" class="nav-link">
                <i class="bi bi-person-lines-fill"></i>Consumers
            </a>
        </li>

        <li class="nav-item">
            <a href="feedback.php" class="nav-link">
                <i class="bi bi-star"></i>Feedback
            </a>
        </li>
        
        <li class="nav-item">
            <a href="reports.php" class="nav-link active">
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

    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Reports</h1>
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

    <main class="content-area">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0 fw-bold">Complaint Reports</h5>
                <small class="text-muted">Analytics and performance overview for ESAMELCO operations</small>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="report-filter-bar">
            <select id="filter-year" class="form-select">
                <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>
            </select>
            <select id="filter-month" class="form-select">
                <option value="0">All Months</option>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <button class="btn-print" id="btn-print">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>

        <!-- ── Row 1: Summary Stats ── -->
        <div class="report-stats">
            <div class="rstat-card">
                <div class="rstat-value" id="stat-total">—</div>
                <div class="rstat-label">Total</div>
            </div>
            <div class="rstat-card">
                <div class="rstat-value" style="color:#f59e0b;" id="stat-pending">—</div>
                <div class="rstat-label">Pending</div>
            </div>
            <div class="rstat-card">
                <div class="rstat-value" style="color:#1a6b2f;" id="stat-ongoing">—</div>
                <div class="rstat-label">Ongoing</div>
            </div>
            <div class="rstat-card">
                <div class="rstat-value" style="color:#0d6efd;" id="stat-resolved">—</div>
                <div class="rstat-label">Resolved</div>
            </div>
            <div class="rstat-card">
                <div class="rstat-value" style="color:#e91e63;" id="stat-cancelled">—</div>
                <div class="rstat-label">Cancelled</div>
            </div>
            <div class="rstat-card highlight">
                <div class="rstat-value" id="stat-rate">—</div>
                <div class="rstat-label">Resolution Rate</div>
            </div>
        </div>

        <!-- ── Row 2: Bar chart + Doughnut ── -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-7">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-bar-chart me-2 text-success"></i>Monthly Complaints</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="chart-wrap">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-pie-chart me-2 text-success"></i>Complaints by Status</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="chart-wrap">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 3: Horizontal bar (by type) + Crew performance ── -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-list-ol me-2 text-success"></i>Complaints by Type</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="chart-wrap">
                            <canvas id="typeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="section-card h-100">
                    <div class="section-card-header">
                        <h6><i class="bi bi-trophy me-2 text-success"></i>Crew Performance</h6>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table table-report mb-0" style="min-width:360px;">
                            <thead>
                                <tr>
                                    <th>Crew Member</th>
                                    <th class="text-center">Assigned</th>
                                    <th class="text-center">Resolved</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody id="crew-tbody">
                                <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 4: Recent resolved complaints ── -->
        <div class="row g-3">
            <div class="col-12">
                <div class="section-card">
                    <div class="section-card-header">
                        <h6><i class="bi bi-check-circle me-2 text-success"></i>Recently Resolved Complaints</h6>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table table-report mb-0" style="min-width:600px;">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Type</th>
                                    <th>Consumer</th>
                                    <th>Crew</th>
                                    <th>Date Filed</th>
                                </tr>
                            </thead>
                            <tbody id="recent-tbody">
                                <tr><td colspan="5" class="text-center text-muted py-3">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../js/admin/reports.js"></script>
<script src="../js/admin/badge_counts.js"></script>
</body>
</html>