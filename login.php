<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESAMELCO - Complaint System</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="icon" href="assets/img/logo.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Login CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <!-- ── Login Card ── -->
    <div class="login-card">

        <!-- Logo & Branding -->
        <div class="logo-wrapper">
            <img src="assets/img/logo.jpg" alt="ESAMELCO Logo">
            <h5>Eastern Samar Electric<br>Cooperative, Inc.</h5>
            <p>Complaint Monitoring &amp; Management Systemssss</p>
        </div>

        <div class="divider"></div>

        <!-- Alert Box (shown by JS) -->
        <div id="alert-box"></div>

        <!-- Login Form -->
        <form id="loginForm" novalidate>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        autofocus
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="btn btn-toggle-pass" type="button" id="togglePassword" title="Show/hide password">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-login" id="btn-login">
                <span class="spinner-border spinner-border-sm me-2" id="btn-spinner" role="status" aria-hidden="true"></span>
                <span id="btn-label">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Log In
                </span>
            </button>

        </form>

        <!-- Register Link -->
        <div class="register-link">
            No account yet? <a href="register.php">Register here</a>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer-note">
        &copy; 2025 ESAMELCO &mdash; All rights reserved
    </div>

    <!-- jQuery -->
    <script src="assets/js/jquery-4.0.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!-- Login JS -->
    <script src="js/login.js"></script>

</body>
</html>