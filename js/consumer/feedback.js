$(document).ready(function () {

    const urlParams   = new URLSearchParams(window.location.search);
    const complaintId = urlParams.get('complaint_id');

    if (!complaintId) {
        showError('No complaint specified. Please go back and try again.');
        return;
    }

    let selectedRating = 0;

    const ratingLabels = {
        1: 'Poor — Very unsatisfied',
        2: 'Fair — Needs improvement',
        3: 'Good — Satisfied',
        4: 'Very Good — Very satisfied',
        5: 'Excellent — Highly satisfied!',
    };

    $(document).on('mouseover', '.star-btn', function () {
        const val = parseInt($(this).data('value'));
        highlightStars(val);
        $('#rating-text').text(ratingLabels[val]).css('color', '#1a6b2f');
    });

    $(document).on('mouseleave', '.stars-wrap', function () {
        highlightStars(selectedRating);
        if (selectedRating > 0) {
            $('#rating-text').text(ratingLabels[selectedRating]).css('color', '#1a6b2f');
        } else {
            $('#rating-text').text('Tap a star to rate').css('color', '#6c757d');
        }
    });

    $(document).on('click', '.star-btn', function () {
        selectedRating = parseInt($(this).data('value'));
        $('#selected-rating').val(selectedRating);
        highlightStars(selectedRating);
        $('#rating-text').text(ratingLabels[selectedRating]).css('color', '#1a6b2f');
        $('.stars-wrap').addClass('rated');
    });

    function highlightStars(count) {
        $('.star-btn').each(function () {
            const val = parseInt($(this).data('value'));
            $(this).text(val <= count ? '★' : '☆').css('color', val <= count ? '#ffc107' : '#dee2e6');
        });
    }

    function loadComplaint() {
        $.ajax({
            url: '/esamelco/php/consumer/feedback.php',
            type: 'GET',
            data: { action: 'load', complaint_id: complaintId },
            dataType: 'json',
            success: function (res) {
                if (!res.success) { showError(res.message); return; }

                const c = res.complaint;
                $('#summary-ticket').text(c.ticket_no);
                $('#summary-type').text(c.complaint_type);
                $('#summary-desc').text(c.description);

                if (c.crew_name) {
                    $('#crew-initial').text(c.crew_name.charAt(0).toUpperCase());
                    $('#crew-name-display').text(c.crew_name);
                    $('#crew-section').show();
                }

                if (c.feedback_id) {
                    showAlreadyRated(c);
                    return;
                }

                $('#feedback-form-section').show();
                $('#complaint-id-input').val(complaintId);
            },
            error: function () { showError('Failed to load complaint. Please try again.'); }
        });
    }

    function showAlreadyRated(c) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<span style="color:${i <= c.existing_rating ? '#ffc107' : '#dee2e6'};font-size:1.6rem;">★</span>`;
        }
        $('#feedback-form-section').html(`
            <div class="success-state">
                <div class="success-icon-wrap"><i class="bi bi-star-fill"></i></div>
                <h5>Feedback Already Submitted</h5>
                <p>You rated this complaint ${c.existing_rating} out of 5 stars.</p>
                <div class="stars-display">${stars}</div>
                ${c.existing_comment ? `<div style="background:#f8f9fa;border-radius:8px;padding:12px;font-size:0.85rem;color:#495057;margin-bottom:20px;font-style:italic;">"${c.existing_comment}"</div>` : ''}
                <a href="my_complaints.php" class="btn-back-complaints"><i class="bi bi-list-check me-2"></i>Back to My Complaints</a>
            </div>
        `).show();
    }

    $('#feedback-form').on('submit', function (e) {
        e.preventDefault();
        hideAlert();

        if (selectedRating === 0) {
            showAlert('Please select a star rating before submitting.', 'danger');
            return;
        }

        const btn = $('#btn-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

        $.ajax({
            url: '/esamelco/php/consumer/feedback.php?action=submit',
            type: 'POST',
            data: { complaint_id: complaintId, rating: selectedRating, comment: $('#comment').val().trim() },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showSuccessState();
                } else {
                    showAlert(res.message, 'danger');
                    btn.prop('disabled', false).html('<i class="bi bi-send-fill me-2"></i>Submit Feedback');
                }
            },
            error: function () {
                showAlert('Server error. Please try again.', 'danger');
                btn.prop('disabled', false).html('<i class="bi bi-send-fill me-2"></i>Submit Feedback');
            }
        });
    });

    function showSuccessState() {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<span style="color:${i <= selectedRating ? '#ffc107' : '#dee2e6'};font-size:1.6rem;">★</span>`;
        }
        $('#feedback-form-section').html(`
            <div class="success-state">
                <div class="success-icon-wrap"><i class="bi bi-check-circle-fill"></i></div>
                <h5>Thank You for Your Feedback!</h5>
                <p>Your ${selectedRating}-star rating has been submitted.<br>ESAMELCO appreciates your response.</p>
                <div class="stars-display">${stars}</div>
                <a href="my_complaints.php" class="btn-back-complaints"><i class="bi bi-list-check me-2"></i>Back to My Complaints</a>
            </div>
        `);
    }

    function showAlert(message, type) {
        $('#alert-box').removeClass('alert-danger alert-success').addClass('alert-' + type)
            .html(`<i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'check-circle'}-fill me-2"></i>${message}`).show();
    }

    function hideAlert() { $('#alert-box').hide().removeClass('alert-danger alert-success'); }

    function showError(message) {
        $('#page-content').html(`
            <div style="text-align:center;padding:48px 20px;color:#adb5bd;">
                <i class="bi bi-exclamation-circle" style="font-size:2.5rem;display:block;margin-bottom:12px;color:#ffc107;"></i>
                <h6 style="color:#495057;font-weight:700;">${message}</h6>
                <a href="my_complaints.php" style="color:#1a6b2f;font-weight:600;font-size:0.88rem;">
                    <i class="bi bi-arrow-left me-1"></i>Back to My Complaints
                </a>
            </div>
        `);
    }

    loadComplaint();
});