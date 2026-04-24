$(document).ready(function () {

    // ── Helpers ──────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    // ── Render stars ──────────────────────────────────────────────
    function renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="bi bi-star-fill" style="color:${i <= rating ? '#ffc107' : '#dee2e6'};font-size:0.95rem;"></i>`;
        }
        return stars;
    }

    // ── Load ratings ──────────────────────────────────────────────
    function loadRatings() {
        $.ajax({
            url: '/esamelco/php/crew/my_ratings.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#ratings-content').html(`<p class="text-danger p-3">${res.message}</p>`);
                    return;
                }

                renderSummary(res.stats);
                renderFeedbackList(res.feedback);
            },
            error: function () {
                $('#ratings-content').html('<p class="text-danger p-3">Server error. Please try again.</p>');
            }
        });
    }

    // ── Render summary card ───────────────────────────────────────
    function renderSummary(s) {
        const avg   = parseFloat(s.average_rating) || 0;
        const total = parseInt(s.total_ratings)    || 0;

        // Big average number
        $('#avg-rating').text(avg.toFixed(1));
        $('#total-ratings').text(total + (total === 1 ? ' rating' : ' ratings'));
        $('#avg-stars').html(renderStars(Math.round(avg)));

        // Star breakdown bars
        const bars = [
            { label: '5', count: s.five_star  },
            { label: '4', count: s.four_star  },
            { label: '3', count: s.three_star },
            { label: '2', count: s.two_star   },
            { label: '1', count: s.one_star   },
        ];

        const barsHtml = bars.map(function (b) {
            const count = parseInt(b.count) || 0;
            const pct   = total > 0 ? Math.round((count / total) * 100) : 0;
            return `
                <div class="star-bar-row">
                    <span class="star-bar-label">${b.label} <i class="bi bi-star-fill" style="color:#ffc107;font-size:0.70rem;"></i></span>
                    <div class="star-bar-track">
                        <div class="star-bar-fill" style="width:${pct}%;"></div>
                    </div>
                    <span class="star-bar-count">${count}</span>
                </div>
            `;
        }).join('');

        $('#star-breakdown').html(barsHtml);
    }

    // ── Render feedback cards ─────────────────────────────────────
    function renderFeedbackList(feedback) {
        const container = $('#feedback-list');
        container.empty();

        if (feedback.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-star"></i>
                    <p>No ratings yet.<br>Complete jobs to receive feedback from consumers.</p>
                </div>
            `);
            return;
        }

        feedback.forEach(function (f) {
            const initial = f.consumer_name.charAt(0).toUpperCase();
            const comment = f.comment
                ? `<div class="feedback-comment">"${f.comment}"</div>`
                : `<div class="feedback-no-comment">No comment provided.</div>`;

            container.append(`
                <div class="feedback-card">
                    <div class="feedback-card-top">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="consumer-initial">${initial}</div>
                            <div>
                                <div class="consumer-name-text">${f.consumer_name}</div>
                                <div class="feedback-ticket">
                                    <i class="bi bi-ticket-perforated me-1"></i>${f.ticket_no} · ${f.complaint_type}
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div>${renderStars(f.rating)}</div>
                            <div class="feedback-date">${formatDate(f.created_at)}</div>
                        </div>
                    </div>
                    ${comment}
                </div>
            `);
        });
    }

    // ── Init ─────────────────────────────────────────────────────
    loadRatings();
});