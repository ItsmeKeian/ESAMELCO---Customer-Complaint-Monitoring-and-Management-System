<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crew Members — ESAMELCO</title>

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
        .filter-bar .form-control { max-width: 240px; }

        /* ── Add button ─────────────────────────────────────────── */
        .btn-add {
            background: #1a6b2f;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            transition: background 0.2s;
        }

        .btn-add:hover { background: #155724; }

        /* ── Crew count badge ───────────────────────────────────── */
        #crew-count {
            background: #1a6b2f;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* ── Small avatar in table ──────────────────────────────── */
        .crew-avatar-sm {
            width: 36px;
            height: 36px;
            background: #1a6b2f;
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

        .badge-active   { background: #e8f5e9; color: #1a6b2f; }
        .badge-inactive { background: #fce4ec; color: #c62828; }

        .badge-avail {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .badge-avail.available { background: #e3f2fd; color: #0d6efd; }
        .badge-avail.busy      { background: #fff8e1; color: #b45309; }

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

        .btn-edit   { background: #e3f2fd; color: #0d6efd; }
        .btn-delete { background: #fce4ec; color: #c62828; }
        .btn-edit:hover   { background: #bbdefb; }
        .btn-delete:hover { background: #f8bbd0; }

        /* ── Modal overlay ──────────────────────────────────────── */
        .crew-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 2000;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.45);
        }

        .crew-modal[style*="display: block"],
        .crew-modal[style*="display:block"] {
            display: flex !important;
        }

        .modal-backdrop {
            position: absolute;
            inset: 0;
        }

        .modal-card {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
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

        .btn-modal-close {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #6c757d;
            cursor: pointer;
            line-height: 1;
        }

        .btn-modal-close:hover { color: #212529; }

        /* ── Form fields inside modal ───────────────────────────── */
        .modal-form-group {
            margin-bottom: 14px;
        }

        .modal-form-group label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 5px;
            display: block;
        }

        .modal-form-group .form-control,
        .modal-form-group .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 0.88rem;
            padding: 9px 12px;
        }

        .modal-form-group .form-control:focus,
        .modal-form-group .form-select:focus {
            border-color: #1a6b2f;
            box-shadow: 0 0 0 3px rgba(26,107,47,0.10);
        }

        /* ── Form row (two columns) ─────────────────────────────── */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 480px) { .form-row-2 { grid-template-columns: 1fr; } }

        /* ── Modal footer buttons ───────────────────────────────── */
        .modal-footer-btns {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }

        .btn-cancel {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel:hover { background: #e9ecef; }

        .btn-save {
            background: #1a6b2f;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 22px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-save:hover    { background: #155724; }
        .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── Error box inside modal ─────────────────────────────── */
        .modal-error {
            display: none;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.82rem;
            color: #856404;
            margin-bottom: 12px;
        }

        /* ── Password hint ──────────────────────────────────────── */
        .field-hint {
            font-size: 0.75rem;
            color: #adb5bd;
            margin-top: 3px;
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
            <a href="crew_member.php" class="nav-link active">
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

    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Crew Members</h1>
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
                    Crew Members
                    <span id="crew-count">0</span>
                </h5>
                <small class="text-muted">Manage maintenance crew accounts and availability</small>
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
            <button class="btn-add" id="btn-add-crew">
                <i class="bi bi-plus-lg"></i> Add Crew
            </button>
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-people me-2 text-success"></i>Crew List</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="table table-complaints mb-0" style="min-width:700px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Availability</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="crew-tbody">
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════
     ADD CREW MODAL
═══════════════════════════════════════════════════════════════ -->
<div id="addModal" class="crew-modal">
    <div class="modal-backdrop"></div>
    <div class="modal-card">
        <div class="modal-card-header">
            <h6><i class="bi bi-person-plus me-2"></i>Add Crew Member</h6>
            <button class="btn-modal-close" id="close-add-modal">&times;</button>
        </div>

        <div id="add-error" class="modal-error"></div>

        <form id="add-form">
            <div class="form-row-2">
                <div class="modal-form-group">
                    <label>Full Name <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="e.g. Juan dela Cruz" required>
                </div>
                <div class="modal-form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. 09XX XXX XXXX">
                </div>
            </div>

            <div class="modal-form-group">
                <label>Email Address <span style="color:#dc3545;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="e.g. juan@email.com" required>
            </div>

            <div class="modal-form-group">
                <label>Password <span style="color:#dc3545;">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="modal-form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" placeholder="e.g. Brgy. Alang-Alang, Borongan City">
            </div>

            <div class="modal-footer-btns">
                <button type="button" class="btn-cancel" id="cancel-add">Cancel</button>
                <button type="submit" class="btn-save" id="btn-save-add">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     EDIT CREW MODAL
═══════════════════════════════════════════════════════════════ -->
<div id="editModal" class="crew-modal">
    <div class="modal-backdrop"></div>
    <div class="modal-card">
        <div class="modal-card-header">
            <h6><i class="bi bi-pencil-square me-2"></i>Edit Crew Member</h6>
            <button class="btn-modal-close" id="close-edit-modal">&times;</button>
        </div>

        <div id="edit-error" class="modal-error"></div>

        <form id="edit-form">
            <input type="hidden" name="id" id="edit-id">

            <div class="form-row-2">
                <div class="modal-form-group">
                    <label>Full Name <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="full_name" id="edit-full-name" class="form-control" required>
                </div>
                <div class="modal-form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="edit-phone" class="form-control">
                </div>
            </div>

            <div class="modal-form-group">
                <label>Email Address <span style="color:#dc3545;">*</span></label>
                <input type="email" name="email" id="edit-email" class="form-control" required>
            </div>

            <div class="modal-form-group">
                <label>New Password</label>
                <input type="password" name="password" id="edit-password" class="form-control"
                    placeholder="Leave blank to keep current password">
                <div class="field-hint">Only fill this if you want to change the password.</div>
            </div>

            <div class="modal-form-group">
                <label>Address</label>
                <input type="text" name="address" id="edit-address" class="form-control">
            </div>

            <div class="modal-form-group">
                <label>Account Status</label>
                <select name="status" id="edit-status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="modal-footer-btns">
                <button type="button" class="btn-cancel" id="cancel-edit">Cancel</button>
                <button type="submit" class="btn-save" id="btn-save-edit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin_crew_member.js"></script>

</body>
</html>