$(document).ready(function () {

    // ── Helpers ──────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function showToast(message, type) {
        const bg = type === 'success' ? '#1a6b2f' : '#dc3545';
        const toast = $(`
            <div style="position:fixed;bottom:24px;right:24px;z-index:9999;
                background:${bg};color:#fff;padding:12px 20px;border-radius:10px;
                font-size:0.88rem;font-weight:600;
                box-shadow:0 4px 16px rgba(0,0,0,0.2);max-width:300px;">
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3500);
    }

    // ── Status badge ─────────────────────────────────────────────
    function statusBadge(status) {
        const map = {
            pending:   '<span class="badge-status badge-pending">Pending</span>',
            ongoing:   '<span class="badge-status badge-ongoing">Ongoing</span>',
            resolved:  '<span class="badge-status badge-resolved">Resolved</span>',
            cancelled: '<span class="badge-status badge-cancelled">Cancelled</span>',
        };
        return map[status] || `<span class="badge-status">${status}</span>`;
    }

    // ── Load assignments ──────────────────────────────────────────
    function loadAssignments() {
        const filter = $('.filter-tab.active').data('filter') || 'all';

        $('#assignments-list').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                <p style="font-size:0.85rem;">Loading assignments...</p>
            </div>
        `);

        $.ajax({
            url: '/esamelco/php/crew/my_assignments.php',
            type: 'GET',
            data: { action: 'list', filter },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#assignments-list').html(`<p class="text-danger p-3">${res.message}</p>`);
                    return;
                }

                $('#assign-count').text(res.assignments.length);
                renderAssignments(res.assignments);
            },
            error: function () {
                $('#assignments-list').html('<p class="text-danger p-3">Server error.</p>');
            }
        });
    }

    // ── Render assignment cards ───────────────────────────────────
    function renderAssignments(assignments) {
        const container = $('#assignments-list');
        container.empty();

        if (assignments.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x"></i>
                    <p>No assignments found.</p>
                </div>
            `);
            return;
        }

        assignments.forEach(function (a) {
            const isActive  = a.assignment_status === 'active';
            const photoHtml = a.photo
                ? `<img src="/esamelco/uploads/${a.photo}"
                        class="complaint-photo" alt="Complaint photo">`
                : '';

            const etaHtml = (isActive && a.eta_minutes)
                ? `<div class="assign-eta">
                       <i class="bi bi-clock"></i> ETA: ${a.eta_minutes} minutes from dispatch
                   </div>`
                : '';

            const mapLink = (a.complaint_lat && a.complaint_lng)
                ? `<a href="https://www.google.com/maps?q=${a.complaint_lat},${a.complaint_lng}"
                      target="_blank" class="btn-map-link">
                       <i class="bi bi-map"></i> Open in Maps
                   </a>`
                : '';

            const actionButtons = isActive ? `
                <div class="assign-actions">
                    <button class="btn-status-update btn-arrived"
                        data-id="${a.complaint_id}" data-status="ongoing">
                        <i class="bi bi-person-check"></i> Mark Arrived
                    </button>
                    <button class="btn-status-update btn-resolved"
                        data-id="${a.complaint_id}" data-status="resolved">
                        <i class="bi bi-check2-circle"></i> Mark Resolved
                    </button>
                    <button class="btn-gps-update" title="Share location">
                        <i class="bi bi-geo-alt-fill"></i>
                    </button>
                </div>
            ` : `
                <div style="margin-top:12px;">
                    <span style="font-size:0.78rem;color:#6c757d;">
                        <i class="bi bi-calendar-check me-1"></i>
                        Completed: ${a.completed_at ? formatDate(a.completed_at) : '—'}
                    </span>
                </div>
            `;

            container.append(`
                <div class="assign-card ${isActive ? 'active' : 'completed'}">
                    <div class="assign-card-header">
                        <div>
                            <span class="assign-ticket">${a.ticket_no}</span>
                            <span class="assign-type">${a.complaint_type}</span>
                        </div>
                        ${statusBadge(a.complaint_status)}
                    </div>

                    <div class="assign-desc">${a.description}</div>

                    ${photoHtml}

                    <div class="assign-meta">
                        <div><i class="bi bi-person"></i> ${a.consumer_name}</div>
                        ${a.consumer_phone
                            ? `<div><i class="bi bi-telephone"></i> ${a.consumer_phone}</div>`
                            : ''}
                        ${a.consumer_address
                            ? `<div><i class="bi bi-geo-alt"></i> ${a.consumer_address}</div>`
                            : ''}
                        <div><i class="bi bi-calendar"></i> Filed: ${formatDate(a.filed_at)}</div>
                        <div><i class="bi bi-send"></i> Dispatched: ${formatDate(a.assigned_at)}</div>
                    </div>

                    ${etaHtml}
                    ${mapLink}
                    ${actionButtons}
                </div>
            `);
        });
    }

    // ── Update status ─────────────────────────────────────────────
    $(document).on('click', '.btn-status-update', function () {
        const complaint_id = $(this).data('id');
        const status       = $(this).data('status');
        const label        = status === 'resolved' ? 'resolve this job' : 'mark as arrived';

        if (!confirm(`Are you sure you want to ${label}?`)) return;

        const btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Updating...'
        );

        $.ajax({
            url: '/esamelco/php/crew/my_assignments.php?action=update_status',
            type: 'POST',
            data: { complaint_id, status },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    loadAssignments();
                } else {
                    showToast(res.message, 'danger');
                    btn.prop('disabled', false);
                }
            },
            error: function () {
                showToast('Server error.', 'danger');
                btn.prop('disabled', false);
            }
        });
    });

    // ── GPS update from card ──────────────────────────────────────
    $(document).on('click', '.btn-gps-update', function () {
        const btn = $(this).prop('disabled', true);

        if (!navigator.geolocation) {
            showToast('Geolocation not supported.', 'danger');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                $.ajax({
                    url: '/esamelco/php/crew/update_location.php',
                    type: 'POST',
                    data: { latitude: pos.coords.latitude, longitude: pos.coords.longitude },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) showToast('Location shared!', 'success');
                    },
                    complete: function () { btn.prop('disabled', false); }
                });
            },
            function () {
                showToast('Could not get location.', 'danger');
                btn.prop('disabled', false);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    // ── Filter tabs ───────────────────────────────────────────────
    $('.filter-tab').on('click', function () {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        loadAssignments();
    });

    // ── Init ─────────────────────────────────────────────────────
    loadAssignments();
});