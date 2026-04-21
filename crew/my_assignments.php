<?php require_once '../php/auth_check_crew.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/crew/dashboard.css">
    <link rel="stylesheet" href="../assets/css/crew/my_assignment.css">

    
</head>
<body>

<!-- ── Top Navbar ── -->
<nav class="crew-topnav">
    <a href="dashboard.php" class="topnav-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO">
        <span class="topnav-brand-text">ESAMELCO<br>Field Crew</span>
    </a>
    <div class="topnav-right">
        <div class="crew-pill">
            <div class="crew-avatar">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <span class="crew-name">
                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
            </span>
        </div>
        <a href="../php/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Log Out</span>
        </a>
    </div>
</nav>

<!-- ── Content ── -->
<div class="content-area">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="dashboard.php" style="display:inline-flex;align-items:center;gap:6px;
                color:#1a6b2f;font-size:0.85rem;font-weight:600;text-decoration:none;margin-bottom:6px;">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <h5 class="mb-0 fw-bold">
                My Assignments
                <span id="assign-count">0</span>
            </h5>
            <small class="text-muted">View and update your assigned complaints</small>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
            <i class="bi bi-list-ul me-1"></i> All
        </button>
        <button class="filter-tab" data-filter="active">
            <i class="bi bi-tools me-1"></i> Active
        </button>
        <button class="filter-tab" data-filter="completed">
            <i class="bi bi-check2-circle me-1"></i> Completed
        </button>
    </div>

    <!-- Assignments List -->
    <div id="assignments-list">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
            <p style="font-size:0.85rem;">Loading...</p>
        </div>
    </div>

</div>

<!-- ── Bottom Nav (mobile) ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item">
            <i class="bi bi-house"></i>Home
        </a>
        <a href="my_assignments.php" class="bottom-nav-item active">
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
<script src="../js/crew/my_assignments.js"></script>

</body>
</html>