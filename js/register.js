$(document).ready(function () {

    // ── Password show/hide toggles ───────────────────────────────
    $('#togglePassword').on('click', function () {
        togglePass('#password', '#eyeIcon1');
    });

    $('#toggleConfirm').on('click', function () {
        togglePass('#confirm_password', '#eyeIcon2');
    });

    function togglePass(inputId, iconId) {
        const input  = $(inputId);
        const icon   = $(iconId);
        const isPass = input.attr('type') === 'password';
        input.attr('type', isPass ? 'text' : 'password');
        icon.toggleClass('bi-eye', !isPass).toggleClass('bi-eye-slash', isPass);
    }

    // ── Password strength indicator ──────────────────────────────
    $('#password').on('input', function () {
        const val = $(this).val();
        let strength = 0;
        let label    = '';
        let color    = '';

        if (val.length >= 6)  strength++;
        if (val.length >= 10) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        if (val.length === 0) {
            $('#strength-bar').css({ width: '0%', background: 'transparent' });
            $('#strength-label').text('');
            return;
        }

        if (strength <= 2)      { label = 'Weak';   color = '#dc3545'; }
        else if (strength === 3) { label = 'Fair';   color = '#ffc107'; }
        else if (strength === 4) { label = 'Good';   color = '#1a6b2f'; }
        else                     { label = 'Strong'; color = '#0d6efd'; }

        $('#strength-bar').css({ width: (strength * 20) + '%', background: color });
        $('#strength-label').text(label).css('color', color);
    });

    // ── Alert helpers ─────────────────────────────────────────────
    function showAlert(message, type) {
        const box = $('#alert-box');
        box.removeClass('alert-danger alert-success')
           .addClass('alert-' + type)
           .html(`<i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'check-circle'}-fill me-2"></i>${message}`)
           .show();
    }

    function hideAlert() {
        $('#alert-box').hide().removeClass('alert-danger alert-success');
    }

    // ── Set button loading ────────────────────────────────────────
    function setLoading(isLoading) {
        const btn     = $('#btn-register');
        const spinner = $('#btn-spinner');
        const label   = $('#btn-label');
        btn.prop('disabled', isLoading);
        spinner.toggle(isLoading);
        label.text(isLoading ? 'Creating Account...' : 'Create Account');
    }

    // ── Form submit via AJAX ──────────────────────────────────────
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        hideAlert();

        // Client-side validation
        const fullName = $('#full_name').val().trim();
        const email    = $('#email').val().trim();
        const password = $('#password').val();
        const confirm  = $('#confirm_password').val();

        if (!fullName || !email || !password || !confirm) {
            showAlert('Please fill in all required fields.', 'danger');
            return;
        }

        if (password.length < 6) {
            showAlert('Password must be at least 6 characters.', 'danger');
            return;
        }

        if (password !== confirm) {
            showAlert('Passwords do not match.', 'danger');
            $('#confirm_password').focus();
            return;
        }

        setLoading(true);

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showAlert(res.message + ' Redirecting to login...', 'success');
                    setTimeout(function () {
                        window.location.href = 'login.html?registered=1';
                    }, 1500);
                } else {
                    showAlert(res.message, 'danger');
                    setLoading(false);
                }
            },
            error: function () {
                showAlert('Server error. Please try again.', 'danger');
                setLoading(false);
            }
        });
    });
});