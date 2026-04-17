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