<?php require_once '../php/auth_check_consumer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Service — ESAMELCO</title>
    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/consumer/feedback.css">
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
            <i class="bi bi-box-arrow-right"></i> <span>Log Out</span>
        </a>
    </div>
</nav>

<!-- ── Content ── -->
<div class="content-area" id="page-content">

    <a href="my_complaints.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to My Complaints
    </a>

    <!-- Complaint Summary -->
    <div class="complaint-summary">
        <div class="summary-icon">
            <i class="bi bi-lightning-charge"></i>
        </div>
        <div>
            <div class="summary-ticket" id="summary-ticket">Loading...</div>
            <div class="summary-type"   id="summary-type"></div>
            <div class="summary-desc"   id="summary-desc"></div>
        </div>
    </div>

    <!-- Crew Card -->
    <div class="crew-card" id="crew-section" style="display:none;">
        <div class="crew-avatar-lg" id="crew-initial">C</div>
        <div>
            <div class="crew-card-label">Maintenance Crew</div>
            <div class="crew-card-name" id="crew-name-display">—</div>
        </div>
    </div>

    <!-- Feedback Form -->
    <div id="feedback-form-section" style="display:none;">
        <div class="feedback-card">

            <div class="feedback-card-header">
                <h5><i class="bi bi-star-fill me-2"></i>Rate This Service</h5>
                <p>How satisfied are you with the service you received?</p>
            </div>

            <div class="feedback-card-body">

                <div id="alert-box" class="alert-box"></div>

                <form id="feedback-form">
                    <input type="hidden" id="complaint-id-input" name="complaint_id">
                    <input type="hidden" id="selected-rating"    name="rating" value="0">

                    <!-- Stars -->
                    <span class="star-rating-label">Tap to rate</span>

                    <div class="stars-wrap" id="stars-wrap">
                        <button type="button" class="star-btn" data-value="5">☆</button>
                        <button type="button" class="star-btn" data-value="4">☆</button>
                        <button type="button" class="star-btn" data-value="3">☆</button>
                        <button type="button" class="star-btn" data-value="2">☆</button>
                        <button type="button" class="star-btn" data-value="1">☆</button>
                    </div>

                    <div class="rating-text" id="rating-text">Tap a star to rate</div>

                    <!-- Comment -->
                    <div class="comment-group">
                        <label for="comment">
                            Comment
                            <span style="color:#adb5bd;font-weight:400;">(optional)</span>
                        </label>
                        <textarea id="comment" name="comment" maxlength="300"
                            placeholder="Tell us more about your experience..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit-feedback" id="btn-submit">
                        <i class="bi bi-send-fill me-2"></i>Submit Feedback
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- ── Bottom Nav ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php"       class="bottom-nav-item"><i class="bi bi-house"></i>Home</a>
        <a href="submit_complaint.php" class="bottom-nav-item"><i class="bi bi-plus-circle"></i>File</a>
        <a href="my_complaints.php"   class="bottom-nav-item active"><i class="bi bi-list-check"></i>My Complaints</a>
        <a href="../php/logout.php"   class="bottom-nav-item"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </div>
</nav>

<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/consumer/feedback.js"></script>

</body>
</html>