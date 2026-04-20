$(document).ready(function () {

    // ── GPS variables ────────────────────────────────────────────
    let gpsLat = null;
    let gpsLng = null;

    // ── Character counter for description ────────────────────────
    $('#description').on('input', function () {
        const len = $(this).val().length;
        $('#char-count').text(len + ' / 500');
        if (len > 450) {
            $('#char-count').css('color', '#f59e0b');
        } else {
            $('#char-count').css('color', '#adb5bd');
        }
    });

    // ── GPS: Get current location ────────────────────────────────
    $('#btn-get-location').on('click', function () {
        if (!navigator.geolocation) {
            setGpsStatus('error', 'Geolocation is not supported by your browser.');
            return;
        }

        setGpsStatus('loading', 'Getting your location...');
        $(this).prop('disabled', true);

        navigator.geolocation.getCurrentPosition(
            function (position) {
                gpsLat = position.coords.latitude.toFixed(7);
                gpsLng = position.coords.longitude.toFixed(7);

                $('#latitude').val(gpsLat);
                $('#longitude').val(gpsLng);

                setGpsStatus('active', 'Location captured successfully!');
                $('#gps-coords').text(`Lat: ${gpsLat}, Lng: ${gpsLng}`);
                $('#btn-get-location').prop('disabled', false).html(
                    '<i class="bi bi-arrow-clockwise"></i> Update Location'
                );
            },
            function (error) {
                let msg = 'Could not get location.';
                if (error.code === 1) msg = 'Location access denied. Please allow location in your browser.';
                if (error.code === 2) msg = 'Location unavailable. Please try again.';
                if (error.code === 3) msg = 'Location request timed out. Please try again.';
                setGpsStatus('error', msg);
                $('#btn-get-location').prop('disabled', false);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    function setGpsStatus(type, message) {
        const dot   = $('#gps-dot');
        const label = $('#gps-label');
        dot.removeClass('active loading error').addClass(type);
        label.text(message);
        if (type !== 'active') $('#gps-coords').text('');
    }

    // ── Photo preview ────────────────────────────────────────────
    $('#photo').on('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Validate type
        const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowed.includes(file.type)) {
            showAlert('Only JPG, PNG, and WEBP images are allowed.', 'danger');
            $(this).val('');
            return;
        }

        // Validate size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showAlert('Photo must be less than 5MB.', 'danger');
            $(this).val('');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#photo-preview').attr('src', e.target.result);
            $('#photo-preview-wrap').show();
            $('#upload-placeholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // ── Remove photo ─────────────────────────────────────────────
    $('#remove-photo').on('click', function (e) {
        e.stopPropagation();
        $('#photo').val('');
        $('#photo-preview-wrap').hide();
        $('#upload-placeholder').show();
    });

    // ── Drag and drop ────────────────────────────────────────────
    const uploadArea = document.getElementById('photo-upload-area');

    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function () {
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt       = new DataTransfer();
            dt.items.add(file);
            $('#photo')[0].files = dt.files;
            $('#photo').trigger('change');
        }
    });

    // ── Alert helpers ─────────────────────────────────────────────
    function showAlert(message, type) {
        const box = $('#alert-box');
        box.removeClass('alert-danger alert-success')
           .addClass('alert-' + type)
           .html(`<i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'check-circle'}-fill me-2"></i>${message}`)
           .show();
        $('html, body').animate({ scrollTop: box.offset().top - 80 }, 300);
    }

    function hideAlert() { $('#alert-box').hide().removeClass('alert-danger alert-success'); }

    // ── Form submit ───────────────────────────────────────────────
    $('#complaint-form').on('submit', function (e) {
        e.preventDefault();
        hideAlert();

        const type = $('#complaint_type').val();
        const desc = $('#description').val().trim();

        if (!type) {
            showAlert('Please select a complaint type.', 'danger');
            return;
        }

        if (desc.length < 10) {
            showAlert('Description must be at least 10 characters.', 'danger');
            return;
        }

        const btn = $('#btn-submit').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...'
        );

        const formData = new FormData(this);

        $.ajax({
            url: '/esamelco/php/consumer/submit_complaint.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    // Show success modal with ticket number
                    $('#success-ticket').text(res.ticket_no);
                    $('#successModal').fadeIn(200);
                } else {
                    showAlert(res.message, 'danger');
                }
            },
            error: function () {
                showAlert('Server error. Please try again.', 'danger');
            },
            complete: function () {
                btn.prop('disabled', false).html(
                    '<i class="bi bi-send-fill"></i> Submit Complaint'
                );
            }
        });
    });

    // ── Go to dashboard after success ────────────────────────────
    $('#btn-go-dashboard').on('click', function () {
        window.location.href = 'dashboard.php';
    });

    // ── View my complaints ────────────────────────────────────────
    $('#btn-view-complaints').on('click', function () {
        window.location.href = 'my_complaints.php';
    });
});