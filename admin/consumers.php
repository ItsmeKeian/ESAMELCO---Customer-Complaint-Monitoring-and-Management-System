<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumers — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <style>
        /* ── Filter bar ─────────────────────────────────────────── */
        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .filter-bar .form-select,
        .filter-bar .form-control {
            font-size: 0.875rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            height: 38px;
        }

        .filter-bar .form-select  { max-width: 150px; }
        .filter-bar .form-control { max-width: 260px; }

        /* ── Consumer count badge ───────────────────────────────── */
        #consumer-count {
            background: #1a6b2f;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* ── Consumer avatar in table ───────────────────────────── */
        .consumer-avatar {
            width: 36px;
            height: 36px;
            background: #0d6efd;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── Status badges ──────────────────────────────────────── */
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .badge-active    { background: #e8f5e9; color: #1a6b2f; }
        .badge-inactive  { background: #fce4ec; color: #c62828; }
        .badge-pending   { background: #fff8e1; color: #b45309; }
        .badge-ongoing   { background: #e8f5e9; color: #1a6b2f; }
        .badge-resolved  { background: #e3f2fd; color: #0d6efd; }
        .badge-cancelled { background: #fce4ec; color: #c62828; }

        /* ── Action buttons ─────────────────────────────────────── */
        .btn-action {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 3px;
            transition: background 0.15s;
        }

        .btn-view-consumer   { background: #e8f5e9; color: #1a6b2f; }
        .btn-delete-consumer { background: #fce4ec; color: #c62828; }
        .btn-view-consumer:hover   { background: #c8e6c9; }
        .btn-delete-consumer:hover { background: #f8bbd0; }

        /* ── View modal ─────────────────────────────────────────── */
        #viewModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        #viewModal[style*="display: block"],
        #viewModal[style*="display:block"] {
            display: flex !important;
        }

        .modal-card {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 500px;
            max-height: 88vh;
            overflow-y: auto;
            margin: 16px;
            padding: 28px;
            position: relative;
            z-index: 1;
        }

        .modal-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-card-header h6 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #1a6b2f;
        }

        #modal-close {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #6c757d;
            cursor: pointer;
            line-height: 1;
        }

        #modal-close:hover { color: #212529; }

        /* ── Detail rows ────────────────────────────────────────── */
        .detail-grid { display: flex; flex-direction: column; gap: 10px; }

        .detail-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .detail-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            min-width: 90px;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .detail-value {
            font-size: 0.88rem;
            color: #212529;
            flex: 1;
        }

        /* ── Complaints summary (P/O/R legend) ──────────────────── */
        .complaint-legend {
            font-size: 0.74rem;
            color: #6c757d;
            margin-bottom: 4px;
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
            <a href="consumers.php" class="nav-link active">
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

    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Consumers</h1>
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
                <h5 class="mb-0 fw-bold">
                    Consumers
                    <span id="consumer-count">0</span>
                </h5>
                <small class="text-muted">View and manage all registered consumer accounts</small>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <select id="filter-status" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <input type="text" id="search-input" class="form-control"
                placeholder="Search name, email, phone...">
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-person-lines-fill me-2 text-success"></i>Consumer List</h6>
                <div class="complaint-legend">
                    Complaints: <span style="color:#f59e0b;font-weight:700;">P</span>ending /
                    <span style="color:#1a6b2f;font-weight:700;">O</span>ngoing /
                    <span style="color:#0d6efd;font-weight:700;">R</span>esolved
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="table table-complaints mb-0" style="min-width:780px;">
                    <thead>
                        <tr>
                            <th>Consumer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">P / O / R</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consumer-tbody">
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════
     VIEW CONSUMER MODAL
═══════════════════════════════════════════════════════════════ -->
<div id="viewModal">
    <div id="viewModal-backdrop" style="position:absolute;inset:0;"></div>
    <div class="modal-card">
        <div class="modal-card-header">
            <h6><i class="bi bi-person-circle me-2"></i>Consumer Details</h6>
            <button id="modal-close" title="Close">&times;</button>
        </div>
        <div id="modal-body-content">
            <!-- Filled by consumers.js -->
        </div>
    </div>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/consumers.js"></script>

</body>
</html>