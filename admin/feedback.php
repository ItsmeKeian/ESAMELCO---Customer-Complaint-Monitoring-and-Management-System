<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin/feedback.css">


</head>
<body>

<div id="sidebarOverlay"></div>

<!-- ── Sidebar ── -->
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
            <a href="feedback.php" class="nav-link active">
                <i class="bi bi-star"></i>Feedback
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

<!-- ── Main Wrapper ── -->
<div class="main-wrapper">

    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Feedback</h1>
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
        <div class="mb-3">
            <h5 class="mb-0 fw-bold">Consumer Feedback & Ratings</h5>
            <small class="text-muted">Monitor consumer satisfaction and crew performance</small>
        </div>

        <!-- Overall Summary -->
        <div class="summary-row">
            <div class="overall-score">
                <div class="overall-number" id="overall-avg">—</div>
                <div id="overall-stars"></div>
                <div class="overall-label" id="total-feedback">0 reviews</div>
            </div>
            <div class="divider-v"></div>
            <div id="star-bars">
                <!-- Filled by JS -->
            </div>
        </div>

        <!-- Crew Performance Ranking -->
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-trophy me-2 text-success"></i>Crew Performance Ranking</h6>
            </div>
            <div id="crew-ranking">
                <p class="text-muted text-center py-3" style="font-size:0.85rem;">Loading...</p>
            </div>
        </div>

        <!-- All Feedback -->
        <div class="section-card">
            <div class="section-card-header">
                <h6>
                    <i class="bi bi-chat-square-text me-2 text-success"></i>
                    All Feedback
                    <span id="feedback-count">0</span>
                </h6>
            </div>
            <div id="feedback-list">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                    <p class="mt-2" style="font-size:0.85rem;">Loading feedback...</p>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin/feedback.js"></script>
<script src="../js/admin/badge_counts.js"></script>

</body>
</html>