<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — ESAMELCO Complaint System</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Reuse login CSS + extra register styles -->
    <link rel="stylesheet" href="assets/css/login.css">

    <style>
        /* ── Password strength bar ──────────────────────────────── */
        .strength-wrap {
            margin-top: 6px;
            height: 4px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        #strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
        }

        #strength-label {
            font-size: 0.74rem;
            font-weight: 600;
            margin-top: 3px;
            display: block;
            min-height: 16px;
        }

        /* ── Slightly taller card for more fields ───────────────── */
        .login-card {
            max-width: 460px;
        }

        /* ── Two-column row ─────────────────────────────────────── */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 480px) {
            .form-row-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Logo & Branding -->
    <div class="logo-wrapper">
        <img src="assets/img/logo.jpg" alt="ESAMELCO Logo">
        <h5>Eastern Samar Electric<br>Cooperative, Inc.</h5>
        <p>Create your consumer account</p>
    </div>

    <div class="divider"></div>

    <!-- Alert Box -->
    <div id="alert-box" class="alert-error" style="display:none;"></div>

    <!-- Registration Form -->
    <form id="registerForm" novalidate>

        <!-- Full Name + Phone (2 columns) -->
        <div class="form-row-2 mb-3">
            <div>
                <label class="form-label">
                    Full Name <span style="color:#dc3545;">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                          style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                        <i class="bi bi-person text-secondary"></i>
                    </span>
                    <input type="text" class="form-control border-start-0"
                        id="full_name" name="full_name"
                        placeholder="e.g. Juan dela Cruz"
                        style="border-radius:0 8px 8px 0;"
                        required autofocus>
                </div>
            </div>
            <div>
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                          style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                        <i class="bi bi-telephone text-secondary"></i>
                    </span>
                    <input type="text" class="form-control border-start-0"
                        id="phone" name="phone"
                        placeholder="09XX XXX XXXX"
                        style="border-radius:0 8px 8px 0;">
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">
                Email Address <span style="color:#dc3545;">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"
                      style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                    <i class="bi bi-envelope text-secondary"></i>
                </span>
                <input type="email" class="form-control border-start-0"
                    id="email" name="email"
                    placeholder="Enter your email address"
                    style="border-radius:0 8px 8px 0;"
                    required>
            </div>
        </div>

        <!-- Address -->
        <div class="mb-3">
            <label class="form-label">Address</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"
                      style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                    <i class="bi bi-geo-alt text-secondary"></i>
                </span>
                <input type="text" class="form-control border-start-0"
                    id="address" name="address"
                    placeholder="e.g. Brgy. Alang-alang, Borongan City"
                    style="border-radius:0 8px 8px 0;">
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">
                Password <span style="color:#dc3545;">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"
                      style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                    <i class="bi bi-lock text-secondary"></i>
                </span>
                <input type="password" class="form-control border-start-0"
                    id="password" name="password"
                    placeholder="Minimum 6 characters"
                    style="border-radius:0;"
                    required>
                <button class="btn btn-outline-secondary" type="button"
                    id="togglePassword" title="Show/hide password">
                    <i class="bi bi-eye" id="eyeIcon1"></i>
                </button>
            </div>
            <!-- Strength bar -->
            <div class="strength-wrap mt-2">
                <div id="strength-bar"></div>
            </div>
            <span id="strength-label"></span>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label">
                Confirm Password <span style="color:#dc3545;">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"
                      style="border-radius:8px 0 0 8px;border-color:#ced4da;">
                    <i class="bi bi-lock-fill text-secondary"></i>
                </span>
                <input type="password" class="form-control border-start-0"
                    id="confirm_password" name="confirm_password"
                    placeholder="Re-enter your password"
                    style="border-radius:0;"
                    required>
                <button class="btn btn-outline-secondary" type="button"
                    id="toggleConfirm" title="Show/hide password">
                    <i class="bi bi-eye" id="eyeIcon2"></i>
                </button>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login" id="btn-register">
            <span class="spinner-border spinner-border-sm me-2"
                  id="btn-spinner" role="status" style="display:none;"></span>
            <span id="btn-label">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </span>
        </button>

    </form>

    <!-- Login Link -->
    <div class="register-link">
        Already have an account?
        <a href="login.php">Log in here</a>
    </div>

</div>

<!-- Footer -->
<div class="footer-note">
    &copy; <?php echo date('Y'); ?> ESAMELCO &mdash; All rights reserved
</div>

<!-- Scripts -->
<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="js/register.js"></script>

</body>
</html>