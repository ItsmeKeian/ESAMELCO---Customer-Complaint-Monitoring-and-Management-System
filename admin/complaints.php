<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints — ESAMELCO</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Dashboard CSS (shared sidebar/topnav styles) -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <style>
        /* ── Filter bar ─────────────────────────────────────────── */
        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-bar .form-select,
        .filter-bar .form-control {
            font-size: 0.875rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            height: 38px;
        }

        .filter-bar .form-select  { max-width: 160px; }
        .filter-bar .form-control { max-width: 260px; }

        /* ── Action buttons in table ────────────────────────────── */
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
            margin-right: 4px;
            transition: background 0.15s;
        }

        .btn-view   { background: #e8f5e9; color: #1a6b2f; }
        .btn-delete { background: #fce4ec; color: #c62828; }
        .btn-view:hover   { background: #c8e6c9; }
        .btn-delete:hover { background: #f8bbd0; }

        /* ── Save button in modal ───────────────────────────────── */
        .btn-save {
            background: #1a6b2f;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-save:hover    { background: #155724; }
        .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── View modal overlay ─────────────────────────────────── */
        #viewModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
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
            max-width: 540px;
            max-height: 88vh;
            overflow-y: auto;
            margin: 16px;
            padding: 28px;
            position: relative;
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
            padding: 0;
        }

        #modal-close:hover { color: #212529; }

        /* ── Detail rows inside modal ───────────────────────────── */
        .detail-grid { display: flex; flex-direction: column; gap: 12px; }

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
            min-width: 110px;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .detail-value {
            font-size: 0.88rem;
            color: #212529;
            flex: 1;
        }

        /* ── Complaint count badge ──────────────────────────────── */
        #complaints-count {
            background: #1a6b2f;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
            vertical-align: middle;
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
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="complaints.php" class="nav-link active">
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
            <a href="tracking.php" class="nav-link">
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
            <h1 class="page-title">Complaints</h1>
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
                <h5 class="mb-0 fw-bold">
                    All Complaints
                    <span id="complaints-count">0</span>
                </h5>
                <small class="text-muted">Manage and monitor all submitted complaints</small>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <select id="filter-status" class="form-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="ongoing">Ongoing</option>
                <option value="resolved">Resolved</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <input
                type="text"
                id="search-input"
                class="form-control"
                placeholder="Search ticket, consumer, type..."
            >
        </div>

        <!-- Complaints Table -->
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-table me-2 text-success"></i>Complaints List</h6>
            </div>
            <div style="overflow-x: auto;">
                <table class="table table-complaints mb-0" style="min-width: 750px;">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Consumer</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Assigned Crew</th>
                            <th>Status</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="complaints-tbody">
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
     VIEW COMPLAINT MODAL
═══════════════════════════════════════════════════════════════ -->
<div id="viewModal">
    <div id="viewModal-backdrop" style="position: absolute; inset: 0;"></div>
    <div class="modal-card">
        <div class="modal-card-header">
            <h6><i class="bi bi-clipboard2-data me-2"></i>Complaint Details</h6>
            <button id="modal-close" title="Close">&times;</button>
        </div>
        <div id="modal-body-content">
            <!-- Filled dynamically by complaints.js -->
        </div>
    </div>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/complaints.js"></script>

</body>
</html>