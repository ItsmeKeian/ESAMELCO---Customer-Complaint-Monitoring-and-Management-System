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
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <style>
        /* ── Spin animation ─────────────────────────────────────── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 0.6s linear; }

        /* ── Filter row ─────────────────────────────────────────── */
        .report-filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .report-filter-bar .form-select {
            font-size: 0.875rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            height: 38px;
            max-width: 150px;
        }

        .btn-print {
            margin-left: auto;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover { background: #e9ecef; }

        /* ── Summary stat cards ─────────────────────────────────── */
        .report-stats {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 992px) { .report-stats { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 576px) { .report-stats { grid-template-columns: repeat(2, 1fr); } }

        .rstat-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px 14px;
            text-align: center;
        }

        .rstat-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #212529;
            line-height: 1;
            margin-bottom: 4px;
        }

        .rstat-label {
            font-size: 0.72rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .rstat-card.highlight .rstat-value { color: #1a6b2f; }

        /* ── Section cards ──────────────────────────────────────── */
        .section-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; }

        .section-card-header {
            padding: 14px 18px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-card-header h6 { margin: 0; font-size: 0.92rem; font-weight: 700; color: #212529; }
        .section-card-body { padding: 18px; }

        /* ── Chart containers ───────────────────────────────────── */
        .chart-wrap { position: relative; width: 100%; height: 260px; }

        /* ── Badge statuses ─────────────────────────────────────── */
        .badge-status {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 0.73rem;
            font-weight: 600;
        }

        .badge-pending   { background: #fff8e1; color: #b45309; }
        .badge-ongoing   { background: #e8f5e9; color: #1a6b2f; }
        .badge-resolved  { background: #e3f2fd; color: #0d6efd; }
        .badge-cancelled { background: #fce4ec; color: #c62828; }

        /* ── Tables ─────────────────────────────────────────────── */
        .table-report th {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
            background: #f8f9fa;
            padding: 10px 14px;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
        }

        .table-report td {
            padding: 11px 14px;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }

        .table-report tbody tr:hover { background: #f8fdf9; }
        .table-report tbody tr:last-child td { border-bottom: none; }

        /* ── Print styles ───────────────────────────────────────── */
        @media print {
            .sidebar, .topnav, .report-filter-bar,
            .btn-print, #btn-refresh, #sidebarOverlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            .content-area { padding: 0 !important; }
            .section-card { break-inside: avoid; }
        }
    </style>
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

</body>
</html>