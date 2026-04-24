$(document).ready(function () {

    // ── Sidebar toggle ────────────────────────────────────────────
    $('#sidebarToggle').on('click', function () {
        $('.sidebar').addClass('open');
        $('#sidebarOverlay').addClass('open');
    });
    $('#sidebarOverlay').on('click', function () {
        $('.sidebar').removeClass('open');
        $('#sidebarOverlay').removeClass('open');
    });

    // ── Helpers ───────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    function renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="bi bi-star-fill" style="color:${i <= rating ? '#ffc107' : '#dee2e6'};font-size:0.90rem;"></i>`;
        }
        return stars;
    }

    // ── Load all data ─────────────────────────────────────────────
    function loadFeedback() {
        $.ajax({
            url: '/esamelco/php/admin/feedback.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    console.error(res.message);
                    return;
                }
                renderSummary(res.summary);
                renderCrewRanking(res.crewRanking);
                renderFeedbackList(res.feedback);
            },
            error: function () {
                console.error('Failed to load feedback data.');
            }
        });
    }

    // ── Render overall summary ────────────────────────────────────
    function renderSummary(s) {
        const avg   = parseFloat(s.overall_avg) || 0;
        const total = parseInt(s.total_feedback) || 0;

        $('#overall-avg').text(avg.toFixed(1));
        $('#overall-stars').html(renderStars(Math.round(avg)));
        $('#total-feedback').text(total + (total === 1 ? ' review' : ' reviews'));

        const bars = [
            { label: '5', count: s.five_star  },
            { label: '4', count: s.four_star  },
            { label: '3', count: s.three_star },
            { label: '2', count: s.two_star   },
            { label: '1', count: s.one_star   },
        ];

        $('#star-bars').html(bars.map(function (b) {
            const count = parseInt(b.count) || 0;
            const pct   = total > 0 ? Math.round((count / total) * 100) : 0;
            return `
                <div class="star-bar-row">
                    <span class="star-bar-label">${b.label}<i class="bi bi-star-fill ms-1" style="color:#ffc107;font-size:0.68rem;"></i></span>
                    <div class="star-bar-track">
                        <div class="star-bar-fill" style="width:${pct}%;"></div>
                    </div>
                    <span class="star-bar-count">${count}</span>
                </div>
            `;
        }).join(''));
    }

    // ── Render crew ranking ───────────────────────────────────────
    function renderCrewRanking(crew) {
        const container = $('#crew-ranking');
        container.empty();

        if (crew.length === 0) {
            container.html('<p class="text-muted text-center py-3" style="font-size:0.85rem;">No ratings yet.</p>');
            return;
        }

        crew.forEach(function (c, i) {
            const avg     = parseFloat(c.avg_rating) || 0;
            const initial = c.full_name.charAt(0).toUpperCase();
            const medal   = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `${i + 1}.`;

            container.append(`
                <div class="crew-rank-row">
                    <span class="rank-medal">${medal}</span>
                    <div class="crew-rank-avatar">${initial}</div>
                    <div class="crew-rank-info">
                        <div class="crew-rank-name">${c.full_name}</div>
                        <div class="crew-rank-meta">${c.total_ratings} rating${c.total_ratings != 1 ? 's' : ''}</div>
                    </div>
                    <div class="crew-rank-score">
                        <div class="crew-rank-avg">${avg.toFixed(1)}</div>
                        <div>${renderStars(Math.round(avg))}</div>
                    </div>
                </div>
            `);
        });
    }

    // ── Render feedback list ──────────────────────────────────────
    function renderFeedbackList(feedback) {
        const container = $('#feedback-list');
        container.empty();

        if (feedback.length === 0) {
            container.html(`
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-star" style="font-size:2.5rem;display:block;margin-bottom:10px;"></i>
                    <p style="font-size:0.85rem;">No feedback submitted yet.</p>
                </div>
            `);
            return;
        }

        feedback.forEach(function (f) {
            const consumerInitial = f.consumer_name.charAt(0).toUpperCase();
            const comment = f.comment
                ? `<div class="fb-comment">"${f.comment}"</div>`
                : `<div class="fb-no-comment">No comment provided.</div>`;

            container.append(`
                <div class="fb-card">
                    <div class="fb-card-top">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="fb-avatar">${consumerInitial}</div>
                            <div>
                                <div class="fb-consumer">${f.consumer_name}</div>
                                <div class="fb-meta">
                                    <i class="bi bi-ticket-perforated me-1" style="color:#1a6b2f;"></i>
                                    ${f.ticket_no} · ${f.complaint_type}
                                </div>
                                <div class="fb-crew">
                                    <i class="bi bi-person-fill me-1"></i>${f.crew_name}
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div>${renderStars(f.rating)}</div>
                            <div class="fb-date">${formatDate(f.created_at)}</div>
                        </div>
                    </div>
                    ${comment}
                </div>
            `);
        });

        $('#feedback-count').text(feedback.length);
    }

    // ── Init ──────────────────────────────────────────────────────
    loadFeedback();

    $('#btn-refresh').on('click', loadFeedback);
});