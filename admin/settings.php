<?php require_once '../php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — ESAMELCO</title>

    <link rel="icon" href="../assets/img/logo.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin/settings.css">
   
</head>
<body>

<div id="sidebarOverlay"></div>

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════════ -->
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
            <a href="reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>Reports
            </a>
        </li>
    </ul>

    <div class="sidebar-label">System</div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="settings.php" class="nav-link active">
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

<!-- ═══════════════════════════════════════════════════════════
     MAIN WRAPPER
═══════════════════════════════════════════════════════════════ -->
<div class="main-wrapper">

    <header class="topnav">
        <div class="topnav-left">
            <button id="sidebarToggle" title="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Settings</h1>
        </div>
        <div class="topnav-right">
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0 fw-bold">Settings</h5>
                <small class="text-muted">Manage your profile, password, and system configuration</small>
            </div>
        </div>

        <div class="settings-layout">

            <!-- ── LEFT: Profile card + Tab nav ── -->
            <div class="settings-left">

                <!-- Profile card -->
                <div class="profile-card">
                    <div id="profile-avatar">A</div>
                    <div class="profile-card-name" id="profile-card-name">Admin</div>
                    <div class="profile-card-role">Administrator</div>
                </div>

                <!-- Tab navigation -->
                <div class="settings-tabs">
                    <button class="settings-tab active" data-tab="profile">
                        <i class="bi bi-person"></i> My Profile
                    </button>
                    <button class="settings-tab" data-tab="password">
                        <i class="bi bi-lock"></i> Change Password
                    </button>
                    <button class="settings-tab" data-tab="system">
                        <i class="bi bi-sliders"></i> System Settings
                    </button>
                </div>

            </div>

            <!-- ── RIGHT: Tab panels ── -->
            <div class="settings-right">

                <!-- ══ TAB 1: Profile ══ -->
                <div class="tab-panel active" id="tab-profile">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h6><i class="bi bi-person-circle me-2 text-success"></i>Profile Information</h6>
                            <p>Update your name, email, and contact details</p>
                        </div>
                        <div class="settings-card-body">

                            <div id="profile-alert" class="settings-alert"></div>

                            <form id="profile-form">
                                <div class="form-row-2">
                                    <div class="settings-form-group">
                                        <label>Full Name <span style="color:#dc3545;">*</span></label>
                                        <input type="text" id="profile-name" name="full_name"
                                            class="form-control" required>
                                    </div>
                                    <div class="settings-form-group">
                                        <label>Phone Number</label>
                                        <input type="text" id="profile-phone" name="phone"
                                            class="form-control" placeholder="09XX XXX XXXX">
                                    </div>
                                </div>

                                <div class="settings-form-group">
                                    <label>Email Address <span style="color:#dc3545;">*</span></label>
                                    <input type="email" id="profile-email" name="email"
                                        class="form-control" required>
                                </div>

                                <div class="settings-form-group">
                                    <label>Office Address</label>
                                    <input type="text" id="profile-address" name="address"
                                        class="form-control" placeholder="e.g. ESAMELCO Main Office, Borongan City">
                                </div>

                                <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                                    <button type="submit" class="btn-save-settings" id="btn-save-profile">
                                        Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB 2: Password ══ -->
                <div class="tab-panel" id="tab-password">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h6><i class="bi bi-shield-lock me-2 text-success"></i>Change Password</h6>
                            <p>Keep your account secure with a strong password</p>
                        </div>
                        <div class="settings-card-body">

                            <div id="password-alert" class="settings-alert"></div>

                            <form id="password-form">

                                <div class="settings-form-group">
                                    <label>Current Password <span style="color:#dc3545;">*</span></label>
                                    <div class="pass-group">
                                        <input type="password" name="current_password" id="current-password"
                                            class="form-control" placeholder="Enter current password" required>
                                        <button type="button" class="btn-eye" data-target="#current-password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-row-2">
                                    <div class="settings-form-group">
                                        <label>New Password <span style="color:#dc3545;">*</span></label>
                                        <div class="pass-group">
                                            <input type="password" name="new_password" id="new-password"
                                                class="form-control" placeholder="Min. 6 characters" required>
                                            <button type="button" class="btn-eye" data-target="#new-password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="settings-form-group">
                                        <label>Confirm New Password <span style="color:#dc3545;">*</span></label>
                                        <div class="pass-group">
                                            <input type="password" name="confirm_password" id="confirm-password"
                                                class="form-control" placeholder="Re-enter new password" required>
                                            <button type="button" class="btn-eye" data-target="#confirm-password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                                    <button type="submit" class="btn-save-settings" id="btn-save-password">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB 3: System Settings ══ -->
                <div class="tab-panel" id="tab-system">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h6><i class="bi bi-sliders me-2 text-success"></i>System Configuration</h6>
                            <p>General settings for the ESAMELCO complaint system</p>
                        </div>
                        <div class="settings-card-body">

                            <div class="info-note">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>These settings are used throughout the system. Changes take effect immediately after saving.</span>
                            </div>

                            <div id="system-alert" class="settings-alert"></div>

                            <form id="system-form">

                                <!-- Site info -->
                                <div class="settings-form-group">
                                    <label>System Name</label>
                                    <input type="text" name="site_name" id="sys-site-name"
                                        class="form-control" placeholder="e.g. ESAMELCO Complaint System">
                                </div>

                                <div class="settings-form-group">
                                    <label>System Tagline</label>
                                    <input type="text" name="site_tagline" id="sys-site-tagline"
                                        class="form-control"
                                        placeholder="e.g. Customer Complaint Monitoring and Management System">
                                </div>

                                <div class="form-row-2">
                                    <div class="settings-form-group">
                                        <label>Contact Email</label>
                                        <input type="email" name="contact_email" id="sys-contact-email"
                                            class="form-control" placeholder="e.g. info@esamelco.com">
                                    </div>
                                    <div class="settings-form-group">
                                        <label>Contact Phone</label>
                                        <input type="text" name="contact_phone" id="sys-contact-phone"
                                            class="form-control" placeholder="e.g. (055) 123-4567">
                                    </div>
                                </div>

                                <div class="settings-form-group">
                                    <label>Office Address</label>
                                    <input type="text" name="office_address" id="sys-office-address"
                                        class="form-control"
                                        placeholder="e.g. ESAMELCO Main Office, Borongan City, Eastern Samar">
                                </div>

                                <!-- Dispatch speed -->
                                <div class="settings-form-group">
                                    <label>Dispatch Speed (for ETA calculation)</label>
                                    <div class="input-with-unit">
                                        <input type="number" name="dispatch_speed_kmh" id="sys-dispatch-speed"
                                            class="form-control" value="40" min="10" max="120">
                                        <span class="unit-label">km/h</span>
                                    </div>
                                    <div class="field-hint">
                                        Average speed used to calculate crew ETA during dispatch. Default is 40 km/h.
                                    </div>
                                </div>

                                <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                                    <button type="submit" class="btn-save-settings" id="btn-save-system">
                                        Save Settings
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>
</div>

<!-- ── Scripts ── -->
<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin/settings.js"></script>

</body>
</html>