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

    // ── Format date ──────────────────────────────────────────────
    function formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    // ── Currently selected complaint ─────────────────────────────
    let selectedComplaintId   = null;
    let selectedComplaintData = null;

    // ── Load pending complaints ──────────────────────────────────
    function loadComplaints() {
        $('#complaints-list').html(`
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                <p style="font-size:0.85rem;">Loading complaints...</p>
            </div>
        `);

        $.ajax({
            url: '../php/dispatch_crew.php',
            type: 'GET',
            data: { action: 'load' },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#complaints-list').html(`<p class="text-danger p-3">${res.message}</p>`);
                    return;
                }

                $('#pending-count').text(res.complaints.length);
                renderComplaintCards(res.complaints);
            },
            error: function () {
                $('#complaints-list').html('<p class="text-danger p-3">Server error loading complaints.</p>');
            }
        });
    }

    // ── Render complaint cards ───────────────────────────────────
    function renderComplaintCards(complaints) {
        const container = $('#complaints-list');
        container.empty();

        if (complaints.length === 0) {
            container.html(`
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle" style="font-size:2rem;color:#1a6b2f;"></i>
                    <p class="mt-2" style="font-size:0.88rem;">No pending complaints.</p>
                </div>
            `);
            return;
        }

        complaints.forEach(function (c) {
            const hasGPS = c.latitude && c.longitude;
            container.append(`
                <div class="complaint-card" data-id="${c.id}" data-lat="${c.latitude ?? ''}" data-lng="${c.longitude ?? ''}">
                    <div class="complaint-card-header">
                        <span class="ticket-no">${c.ticket_no}</span>
                        <span class="badge-status badge-pending">Pending</span>
                    </div>
                    <div class="complaint-type">${c.complaint_type}</div>
                    <div class="complaint-consumer">
                        <i class="bi bi-person"></i> ${c.consumer_name}
                        ${c.consumer_phone ? `&nbsp;&middot;&nbsp;<i class="bi bi-telephone"></i> ${c.consumer_phone}` : ''}
                    </div>
                    <div class="complaint-desc">${c.description}</div>
                    <div class="complaint-meta">
                        <span><i class="bi bi-clock"></i> ${formatDate(c.created_at)}</span>
                        <span class="${hasGPS ? 'gps-ok' : 'gps-none'}">
                            <i class="bi bi-geo-alt"></i> ${hasGPS ? 'GPS available' : 'No GPS'}
                        </span>
                    </div>
                </div>
            `);
        });
    }

    // ── Click complaint card → load crew panel ───────────────────
    $(document).on('click', '.complaint-card', function () {
        $('.complaint-card').removeClass('selected');
        $(this).addClass('selected');

        selectedComplaintId   = $(this).data('id');
        selectedComplaintData = {
            id:  selectedComplaintId,
            lat: $(this).data('lat'),
            lng: $(this).data('lng'),
        };

        $('#crew-panel-placeholder').hide();
        $('#crew-panel-content').show();
        $('#selected-ticket').text(
            $(this).find('.ticket-no').text() + ' — ' + $(this).find('.complaint-type').text()
        );

        loadCrewForComplaint(selectedComplaintId);
    });

    // ── Load crew list for selected complaint ────────────────────
    function loadCrewForComplaint(complaint_id) {
        $('#crew-list').html(`
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                <p style="font-size:0.85rem;">Calculating nearest crew...</p>
            </div>
        `);

        $.ajax({
            url: '../php/dispatch_crew.php',
            type: 'GET',
            data: { action: 'get_crew', complaint_id: complaint_id },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#crew-list').html(`<p class="text-danger p-3">${res.message}</p>`);
                    return;
                }
                renderCrewCards(res.crew);
            },
            error: function () {
                $('#crew-list').html('<p class="text-danger p-3">Server error loading crew.</p>');
            }
        });
    }

    // ── Render crew cards ────────────────────────────────────────
    function renderCrewCards(crewList) {
        const container = $('#crew-list');
        container.empty();

        if (crewList.length === 0) {
            container.html(`
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people" style="font-size:2rem;"></i>
                    <p class="mt-2" style="font-size:0.88rem;">No available crew at the moment.</p>
                </div>
            `);
            return;
        }

        crewList.forEach(function (crew, index) {
            const eta  = crew.eta_minutes !== null ? `${crew.eta_minutes} min` : 'N/A';
            const dist = crew.distance_km !== null ? `${crew.distance_km} km away` : 'Location unknown';
            const isNearest = index === 0 ? '<span class="nearest-badge">Nearest</span>' : '';

            container.append(`
                <div class="crew-card" data-crew-id="${crew.id}" data-eta="${crew.eta_minutes ?? 0}">
                    <div class="crew-avatar">${crew.full_name.charAt(0).toUpperCase()}</div>
                    <div class="crew-info">
                        <div class="crew-name">${crew.full_name} ${isNearest}</div>
                        <div class="crew-meta">
                            <span><i class="bi bi-geo-alt"></i> ${dist}</span>
                            <span><i class="bi bi-clock"></i> ETA: <strong>${eta}</strong></span>
                        </div>
                        ${crew.phone ? `<div class="crew-meta"><i class="bi bi-telephone"></i> ${crew.phone}</div>` : ''}
                    </div>
                    <button class="btn-dispatch" data-crew-id="${crew.id}" data-eta="${crew.eta_minutes ?? 0}">
                        Dispatch
                    </button>
                </div>
            `);
        });
    }

    // ── Dispatch button click ────────────────────────────────────
    $(document).on('click', '.btn-dispatch', function () {
        const crew_id     = $(this).data('crew-id');
        const eta_minutes = $(this).data('eta');
        const crewName    = $(this).closest('.crew-card').find('.crew-name').text().trim();
        const ticket      = $('#selected-ticket').text();

        if (!confirm(`Dispatch ${crewName} to:\n${ticket}?\n\nETA: ${eta_minutes} minutes`)) return;

        const btn = $(this);
        btn.prop('disabled', true).text('Dispatching...');

        $.ajax({
            url: '../php/dispatch_crew.php?action=dispatch',
            type: 'POST',
            data: {
                complaint_id: selectedComplaintId,
                crew_id:      crew_id,
                eta_minutes:  eta_minutes,
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    // Refresh both panels
                    loadComplaints();
                    $('#crew-panel-content').hide();
                    $('#crew-panel-placeholder').show();
                    selectedComplaintId = null;
                } else {
                    showToast(res.message, 'danger');
                    btn.prop('disabled', false).text('Dispatch');
                }
            },
            error: function () {
                showToast('Server error. Please try again.', 'danger');
                btn.prop('disabled', false).text('Dispatch');
            }
        });
    });

    // ── Refresh button ───────────────────────────────────────────
    $('#btn-refresh').on('click', function () {
        loadComplaints();
        if (selectedComplaintId) {
            loadCrewForComplaint(selectedComplaintId);
        }
    });

    // ── Toast notification ───────────────────────────────────────
    function showToast(message, type) {
        const bg    = type === 'success' ? '#1a6b2f' : '#dc3545';
        const toast = $(`
            <div style="
                position:fixed; bottom:24px; right:24px; z-index:9999;
                background:${bg}; color:#fff;
                padding:12px 20px; border-radius:10px;
                font-size:0.88rem; font-weight:600;
                box-shadow:0 4px 16px rgba(0,0,0,0.2);
                max-width:320px;
            ">${message}</div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3500);
    }

    // ── Initial load ─────────────────────────────────────────────
    loadComplaints();
});