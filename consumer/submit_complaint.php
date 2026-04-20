<?php require_once '../php/auth_check_consumer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File a Complaint — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/consumer/submit_complaint.css">
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
            <div class="consumer-avatar" id="consumer-initials">
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

    <!-- Form Card -->
    <div class="form-card">

        <!-- Header -->
        <div class="form-card-header">
            <h5><i class="bi bi-exclamation-circle me-2"></i>File a Complaint</h5>
            <p>Describe your electrical issue and we'll dispatch a crew as soon as possible.</p>
        </div>

        <!-- Body -->
        <div class="form-card-body">

            <!-- Alert -->
            <div id="alert-box" class="alert-box"></div>

            <form id="complaint-form" enctype="multipart/form-data">

                <!-- Complaint Type -->
                <div class="form-group">
                    <label for="complaint_type">
                        Complaint Type <span class="required">*</span>
                    </label>
                    <select name="complaint_type" id="complaint_type" class="form-select" required>
                        <option value="">— Select complaint type —</option>
                        <option value="Power Interruption">Power Interruption</option>
                        <option value="Flickering Lights">Flickering Lights</option>
                        <option value="Broken Power Lines">Broken Power Lines</option>
                        <option value="No Power">No Power</option>
                        <option value="Electric Meter Issue">Electric Meter Issue</option>
                        <option value="Billing Concern">Billing Concern</option>
                        <option value="Transformer Issue">Transformer Issue</option>
                        <option value="Street Light Issue">Street Light Issue</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">
                        Description <span class="required">*</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        maxlength="500"
                        placeholder="Describe the issue in detail — location, what happened, when it started..."
                        required
                    ></textarea>
                    <div class="char-count" id="char-count">0 / 500</div>
                    <div class="field-hint">Minimum 10 characters. Be as specific as possible.</div>
                </div>

                <!-- Photo Upload -->
                <div class="form-group">
                    <label>Photo <span style="color:#adb5bd;font-weight:400;">(optional)</span></label>

                    <div class="photo-upload-area" id="photo-upload-area">
                        <input type="file" name="photo" id="photo" accept="image/*">
                        <div id="upload-placeholder">
                            <div class="photo-upload-icon">
                                <i class="bi bi-camera"></i>
                            </div>
                            <p class="photo-upload-text">Tap to take a photo or upload from gallery</p>
                            <p class="photo-upload-hint">JPG, PNG, WEBP — max 5MB</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="photo-preview-wrap" style="display:none; margin-top:12px; position:relative;">
                        <img id="photo-preview" src="" alt="Preview">
                        <button type="button" id="remove-photo" title="Remove photo">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                <!-- GPS Location -->
                <div class="form-group">
                    <label>Location <span style="color:#adb5bd;font-weight:400;">(recommended)</span></label>
                    <div class="gps-box">
                        <div class="gps-status">
                            <div class="gps-dot" id="gps-dot"></div>
                            <span class="gps-label" id="gps-label">Location not captured yet</span>
                        </div>
                        <div class="gps-coords" id="gps-coords"></div>
                        <button type="button" class="btn-gps" id="btn-get-location">
                            <i class="bi bi-geo-alt-fill"></i>
                            Get My Location
                        </button>
                    </div>
                    <div class="field-hint">
                        Your location helps us dispatch the nearest crew faster.
                    </div>

                    <!-- Hidden GPS fields -->
                    <input type="hidden" name="latitude"  id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit" id="btn-submit">
                    <i class="bi bi-send-fill"></i>
                    Submit Complaint
                </button>

            </form>
        </div>
    </div>
</div>

<!-- ── Success Modal ── -->
<div id="successModal">
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h5 style="margin:0 0 6px;font-weight:700;color:#212529;">Complaint Submitted!</h5>
        <p style="font-size:0.85rem;color:#6c757d;margin:0;">
            Your complaint has been received. Keep your ticket number for tracking.
        </p>
        <div class="success-ticket" id="success-ticket">TKT-0000-00000</div>
        <button class="btn-go-dashboard" id="btn-go-dashboard">
            <i class="bi bi-house me-2"></i>Go to Dashboard
        </button>
        <button class="btn-go-dashboard" id="btn-view-complaints"
            style="background:#f8f9fa;color:#1a6b2f;border:1px solid #dee2e6;margin-top:8px;">
            <i class="bi bi-list-check me-2"></i>View My Complaints
        </button>
    </div>
</div>

<!-- ── Bottom Nav (mobile) ── -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="dashboard.php" class="bottom-nav-item">
            <i class="bi bi-house"></i>Home
        </a>
        <a href="submit_complaint.php" class="bottom-nav-item active">
            <i class="bi bi-plus-circle"></i>File
        </a>
        <a href="my_complaints.php" class="bottom-nav-item">
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
<script src="../js/consumer/submit_complaint.js"></script>

</body>
</html>