<?php require_once '../php/auth_check_crew.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crew Dashboard — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/crew/dashboard.css">
</head>
<body>

<!-- ── Top Navbar ── -->
<nav class="crew-topnav">
    <a href="dashboard.php" class="topnav-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO">
        <span class="topnav-brand-text">ESAMELCO<br>Field Crew</span>
    </a>
    <div class="topnav-right">
        <button class="btn-bell" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="bell-badge" id="bell-badge" style="display:none;">0</span>
        </button>
        <div class="crew-pill">
            <div class="crew-avatar" id="crew-initials">C</div>
            <span class="crew-name" id="crew-name-display">Crew</span>
        </div>
        <a href="../php/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Log Out</span>
        </a>
    </div>
</nav>

<!-- ── Content ── -->
<div class="content-area">

    <!-- GPS Share Bar -->
    <div class="gps-share-bar">
        <div class="gps-share-text">
            <i class="bi bi-geo-alt-fill"></i>
            <span id="gps-status-text">Share your location so admin can track you</span>
        </div>
        <button class="btn-share-gps" id="btn-share-gps">
            <i class="bi bi-geo-alt-fill"></i> Share My Location
        </button>
    </div>

    <!-- Active Job -->
    <div id="active-job-container">
        <div class="no-job-card">
            <div class="spinner-border text-success spinner-border-sm" role="status"></div>
            <p class="mt-2" style="font-size:0.85rem;">Loading...</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="bi bi-clipboard2-data"></i>
            </div>
            <div class="stat-value" id="stat-total">—</div>
            <div class="stat-label">Total Jobs</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value" id="stat-completed">—</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-value" id="stat-active">—</div>
            <div class="stat-label">Active</div>
        </div>
    </div>

    <!-- Recent Completed Jobs -->
    <div class="section-card">
        <div class="section-card-header">
            <h6><i class="bi bi-clock-history me-2 text-success"></i>Recent Completed Jobs</h6>
            <a href="my_assignments.php"
               style="font-size:0.80rem;color:#1a6b2f;text-decoration:none;font-weight:600;">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div id="recent-jobs-list">
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

<!-- ── Bottom Nav (mobile) ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item active">
            <i class="bi bi-house"></i>Home
        </a>
        <a href="my_assignments.php" class="bottom-nav-item">
            <i class="bi bi-list-check"></i>Assignments
        </a>
        <a href="../php/logout.php" class="bottom-nav-item">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </div>
</nav>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/crew/dashboard.js"></script>

</body>
</html>