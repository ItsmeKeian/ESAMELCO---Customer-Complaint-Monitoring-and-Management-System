<?php require_once '../php/auth_check_crew.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ratings — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/crew/dashboard.css">
    <link rel="stylesheet" href="../assets/css/crew/my_ratings.css">
  
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

    <!-- Back link -->
    <a href="dashboard.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Page Header -->
    <div class="mb-3">
        <h5 class="mb-0 fw-bold">My Ratings</h5>
        <small class="text-muted">See how consumers rated your service</small>
    </div>

    <!-- Summary Card -->
    <div class="summary-card">
        <div class="avg-score">
            <div class="avg-number" id="avg-rating">—</div>
            <div class="avg-stars" id="avg-stars"></div>
            <div class="avg-label" id="total-ratings">0 ratings</div>
        </div>

        <div class="divider-v"></div>

        <div class="star-breakdown" id="star-breakdown">
            <!-- Filled by JS -->
        </div>
    </div>

    <!-- Feedback List -->
    <div class="section-card">
        <div class="section-card-header">
            <h6><i class="bi bi-chat-square-text me-2 text-success"></i>Consumer Feedback</h6>
        </div>
        <div id="feedback-list">
            <div class="empty-state">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <p class="mt-2">Loading ratings...</p>
            </div>
        </div>
    </div>

</div>

<!-- ── Bottom Nav ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item">
            <i class="bi bi-house"></i>Home
        </a>
        <a href="my_assignments.php" class="bottom-nav-item">
            <i class="bi bi-list-check"></i>Assignments
        </a>
        <a href="my_ratings.php" class="bottom-nav-item active">
            <i class="bi bi-star"></i>Ratings
        </a>
        <a href="../php/logout.php" class="bottom-nav-item">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </div>
</nav>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/crew/my_ratings.js"></script>

</body>
</html>