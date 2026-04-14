$(document).ready(function () {

    // ── Password show/hide toggle ────────────────────────────────
    $('#togglePassword').on('click', function () {
        const input   = $('#password');
        const icon    = $('#eyeIcon');
        const isPass  = input.attr('type') === 'password';

        input.attr('type', isPass ? 'text' : 'password');
        icon.toggleClass('bi-eye', !isPass)
            .toggleClass('bi-eye-slash', isPass);
    });

    // ── Show alert helper ────────────────────────────────────────
    function showAlert(message, type) {
        const box = $('#alert-box');
        box.removeClass('alert-danger alert-success')
           .addClass('alert-' + type)
           .html('<i class="bi bi-' + (type === 'danger' ? 'exclamation-triangle-fill' : 'check-circle-fill') + ' me-1"></i>' + message);
    }

    // ── Hide alert ───────────────────────────────────────────────
    function hideAlert() {
        $('#alert-box').removeClass('alert-danger alert-success').hide();
    }

    // ── Set button loading state ─────────────────────────────────
    function setLoading(isLoading) {
        const btn     = $('#btn-login');
        const spinner = $('#btn-spinner');
        const label   = $('#btn-label');

        btn.prop('disabled', isLoading);
        spinner.toggle(isLoading);
        label.text(isLoading ? 'Logging in...' : 'Log In');
    }

    // ── Login form submit via AJAX ───────────────────────────────
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        hideAlert();

        const email    = $('#email').val().trim();
        const password = $('#password').val();

        // Client-side check
        if (!email || !password) {
            showAlert('Please fill in all fields.', 'danger');
            return;
        }

        setLoading(true);

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: { email: email, password: password },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 800);
                } else {
                    showAlert(response.message, 'danger');
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