$(document).ready(function () {

    // ── Sidebar toggle (mobile) ──────────────────────────────────
    $('#sidebarToggle').on('click', function () {
        $('.sidebar').addClass('open');
        $('#sidebarOverlay').addClass('open');
    });
    $('#sidebarOverlay').on('click', function () {
        $('.sidebar').removeClass('open');
        $('#sidebarOverlay').removeClass('open');
    });

    // ── Tab switching ────────────────────────────────────────────
    $('.settings-tab').on('click', function () {
        const target = $(this).data('tab');
        $('.settings-tab').removeClass('active');
        $(this).addClass('active');
        $('.tab-panel').removeClass('active');
        $('#tab-' + target).addClass('active');
    });

    // ── Toast helper ─────────────────────────────────────────────
    function showToast(message, type) {
        const bg = type === 'success' ? '#1a6b2f' : '#dc3545';
        const toast = $(`
            <div style="position:fixed;bottom:24px;right:24px;z-index:9999;
                background:${bg};color:#fff;padding:12px 20px;border-radius:10px;
                font-size:0.88rem;font-weight:600;
                box-shadow:0 4px 16px rgba(0,0,0,0.2);max-width:320px;">
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3500);
    }

    // ── Alert box helper ─────────────────────────────────────────
    function showAlert(boxId, message, type) {
        const box = $(boxId);
        box.removeClass('alert-danger alert-success')
           .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
           .html(`<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>${message}`)
           .slideDown(200);
    }

    function hideAlert(boxId) { $(boxId).slideUp(200); }

    // ── Load settings data ───────────────────────────────────────
    function loadSettings() {
        $.ajax({
            url: '../php/settings.php',
            type: 'GET',
            data: { action: 'load' },
            dataType: 'json',
            success: function (res) {
                if (!res.success) { showToast(res.message, 'danger'); return; }

                // Populate profile form
                const p = res.profile;
                $('#profile-name').val(p.full_name);
                $('#profile-email').val(p.email);
                $('#profile-phone').val(p.phone ?? '');
                $('#profile-address').val(p.address ?? '');

                // Profile avatar initials
                const initials = p.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                $('#profile-avatar').text(initials);
                $('#admin-initials').text(initials);
                $('#admin-name-display').text(p.full_name);

                // Populate system settings
                const s = res.settings;
                $('#sys-site-name').val(s.site_name         ?? 'ESAMELCO Complaint System');
                $('#sys-site-tagline').val(s.site_tagline   ?? 'Customer Complaint Monitoring and Management System');
                $('#sys-contact-email').val(s.contact_email ?? '');
                $('#sys-contact-phone').val(s.contact_phone ?? '');
                $('#sys-office-address').val(s.office_address ?? '');
                $('#sys-dispatch-speed').val(s.dispatch_speed_kmh ?? '40');
            },
            error: function () { showToast('Failed to load settings.', 'danger'); }
        });
    }

    // ── Update profile ───────────────────────────────────────────
    $('#profile-form').on('submit', function (e) {
        e.preventDefault();
        hideAlert('#profile-alert');

        const btn = $('#btn-save-profile').prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/settings.php?action=update_profile',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert('#profile-alert', res.message, 'success');
                    // Update topnav name live
                    const name     = $('#profile-name').val().trim();
                    const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    $('#admin-name-display').text(name);
                    $('#admin-initials, #profile-avatar').text(initials);
                } else {
                    showAlert('#profile-alert', res.message, 'danger');
                }
            },
            error: function () { showAlert('#profile-alert', 'Server error.', 'danger'); },
            complete: function () { btn.prop('disabled', false).text('Save Profile'); }
        });
    });

    // ── Change password ──────────────────────────────────────────
    $('#password-form').on('submit', function (e) {
        e.preventDefault();
        hideAlert('#password-alert');

        const btn = $('#btn-save-password').prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/settings.php?action=change_password',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert('#password-alert', res.message, 'success');
                    $('#password-form')[0].reset();
                } else {
                    showAlert('#password-alert', res.message, 'danger');
                }
            },
            error: function () { showAlert('#password-alert', 'Server error.', 'danger'); },
            complete: function () { btn.prop('disabled', false).text('Change Password'); }
        });
    });

    // ── Password show/hide toggles ───────────────────────────────
    $('.btn-eye').on('click', function () {
        const target = $(this).data('target');
        const input  = $(target);
        const icon   = $(this).find('i');
        const isPass = input.attr('type') === 'password';
        input.attr('type', isPass ? 'text' : 'password');
        icon.toggleClass('bi-eye', !isPass).toggleClass('bi-eye-slash', isPass);
    });

    // ── Save system settings ─────────────────────────────────────
    $('#system-form').on('submit', function (e) {
        e.preventDefault();
        hideAlert('#system-alert');

        const btn = $('#btn-save-system').prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/settings.php?action=update_system',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert('#system-alert', res.message, 'success');
                } else {
                    showAlert('#system-alert', res.message, 'danger');
                }
            },
            error: function () { showAlert('#system-alert', 'Server error.', 'danger'); },
            complete: function () { btn.prop('disabled', false).text('Save Settings'); }
        });
    });

    // ── Init ─────────────────────────────────────────────────────
    loadSettings();
});