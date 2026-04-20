<?php require_once '../php/auth_check_consumer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/consumer/dashboard.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     TOP NAVBAR
═══════════════════════════════════════════════════════════════ -->
<nav class="consumer-topnav">

    <!-- Brand -->
    <a href="dashboard.php" class="topnav-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO">
        <span class="topnav-brand-text">ESAMELCO<br>Complaint System</span>
    </a>

    <!-- Right side -->
    <div class="topnav-right">

        <!-- Notification bell -->
        <button class="btn-bell" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="bell-badge" id="bell-badge" style="display:none;">0</span>
        </button>

        <!-- Consumer name pill -->
        <div class="consumer-pill">
            <div class="consumer-avatar" id="consumer-initials">C</div>
            <span class="consumer-name" id="consumer-name-display">Consumer</span>
        </div>

        <!-- Logout -->
        <a href="../php/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Log Out</span>
        </a>

    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     PAGE CONTENT
═══════════════════════════════════════════════════════════════ -->
<div class="content-area">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h4>Welcome back, <span id="welcome-name">there</span>!</h4>
            <p>Track your complaints and get real-time updates from ESAMELCO.</p>
        </div>
        <a href="submit_complaint.php" class="btn-file-complaint">
            <i class="bi bi-plus-circle-fill"></i>
            File a Complaint
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="bi bi-clipboard2-data"></i>
            </div>
            <div class="stat-value" id="stat-total">—</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-value" id="stat-pending">—</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ongoing">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-value" id="stat-ongoing">—</div>
            <div class="stat-label">Ongoing</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon resolved">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value" id="stat-resolved">—</div>
            <div class="stat-label">Resolved</div>
        </div>
    </div>

    <!-- Recent Complaints -->
    <div class="section-card">
        <div class="section-card-header">
            <h6><i class="bi bi-clock-history me-2 text-success"></i>Recent Complaints</h6>
            <a href="my_complaints.php">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div id="recent-list">
            <div class="empty-state">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <p class="mt-2">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="section-card">
        <div class="section-card-header">
            <h6><i class="bi bi-bell me-2 text-success"></i>Notifications</h6>
        </div>
        <div id="notif-list">
            <div class="empty-state">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <p class="mt-2">Loading...</p>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     BOTTOM NAV (mobile only)
═══════════════════════════════════════════════════════════════ -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item active">
            <i class="bi bi-house"></i>
            Home
        </a>
        <a href="submit_complaint.php" class="bottom-nav-item">
            <i class="bi bi-plus-circle"></i>
            File
        </a>
        <a href="my_complaints.php" class="bottom-nav-item">
            <i class="bi bi-list-check"></i>
            My Complaints
        </a>
        <a href="../php/logout.php" class="bottom-nav-item">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</nav>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/consumer/dashboard.js"></script>

</body>
</html>