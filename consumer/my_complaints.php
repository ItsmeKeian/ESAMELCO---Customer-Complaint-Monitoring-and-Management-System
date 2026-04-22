<?php require_once '../php/auth_check_consumer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
    <link rel="stylesheet" href="../assets/css/consumer/my_complaints.css">
</head>
<body>

<!-- ── Top Navbar ── -->
<nav class="consumer-topnav">
    <a href="dashboard.php" class="topnav-brand">
        <img src="../assets/img/logo.jpg" alt="ESAMELCO">
        <span class="topnav-brand-text">ESAMELCO<br>Complaint System</span>
    </a>
    <div class="topnav-right">
        <div class="consumer-pill">
            <div class="consumer-avatar">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <span class="consumer-name">
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

    <!-- Back link -->
    <a href="dashboard.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold">
                My Complaints
                <span id="complaint-count">0</span>
            </h5>
            <small class="text-muted">Track all your submitted complaints</small>
        </div>
        <a href="submit_complaint.php"
           style="background:#1a6b2f;color:#fff;border-radius:8px;padding:8px 14px;
                  font-size:0.83rem;font-weight:600;text-decoration:none;
                  display:flex;align-items:center;gap:6px;white-space:nowrap;">
            <i class="bi bi-plus-lg"></i> File New
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
            <i class="bi bi-list-ul me-1"></i> All
        </button>
        <button class="filter-tab" data-filter="pending">
            <i class="bi bi-hourglass-split me-1"></i> Pending
        </button>
        <button class="filter-tab" data-filter="ongoing">
            <i class="bi bi-tools me-1"></i> Ongoing
        </button>
        <button class="filter-tab" data-filter="resolved">
            <i class="bi bi-check2-circle me-1"></i> Resolved
        </button>
    </div>

    <!-- Complaints List -->
    <div id="complaints-list">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
            <p style="font-size:0.85rem;">Loading your complaints...</p>
        </div>
    </div>

</div>

<!-- ── Detail Modal (bottom sheet) ── -->
<div id="detailModal" style="display:none;">
    <div id="modal-backdrop" style="position:absolute;inset:0;"></div>
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <button class="modal-close" id="modal-close-btn" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>
        <div id="modal-body">
            <!-- Filled by JS -->
        </div>
    </div>
</div>

<!-- ── Bottom Nav (mobile) ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item">
            <i class="bi bi-house"></i>Home
        </a>
        <a href="submit_complaint.php" class="bottom-nav-item">
            <i class="bi bi-plus-circle"></i>File
        </a>
        <a href="my_complaints.php" class="bottom-nav-item active">
            <i class="bi bi-list-check"></i>My Complaints
        </a>
        <a href="../php/logout.php" class="bottom-nav-item">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </div>
</nav>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script src="../js/consumer/my_complaints.js"></script>

</body>
</html>