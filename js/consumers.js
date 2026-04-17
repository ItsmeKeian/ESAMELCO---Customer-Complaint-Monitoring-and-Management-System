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

    // ── Helpers ──────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

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

    function statusBadge(status) {
        return status === 'active'
            ? '<span class="badge-status badge-active">Active</span>'
            : '<span class="badge-status badge-inactive">Inactive</span>';
    }

    function complaintStatusBadge(status) {
        const map = {
            pending:   '<span class="badge-status badge-pending">Pending</span>',
            ongoing:   '<span class="badge-status badge-ongoing">Ongoing</span>',
            resolved:  '<span class="badge-status badge-resolved">Resolved</span>',
            cancelled: '<span class="badge-status badge-cancelled">Cancelled</span>',
        };
        return map[status] || status;
    }

    // ── Modal helpers ─────────────────────────────────────────────
    function openModal()  { $('#viewModal').fadeIn(200); }
    function closeModal() { $('#viewModal').fadeOut(200); }

    $('#modal-close, #viewModal-backdrop').on('click', closeModal);

    // ── Load consumers ────────────────────────────────────────────
    function loadConsumers() {
        const search = $('#search-input').val().trim();
        const status = $('#filter-status').val();

        $('#consumer-tbody').html(
            '<tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>'
        );

        $.ajax({
            url: '../php/consumers.php',
            type: 'GET',
            data: { action: 'list', search, status },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#consumer-tbody').html(
                        `<tr><td colspan="8" class="text-center text-danger py-4">${res.message}</td></tr>`
                    );
                    return;
                }

                $('#consumer-count').text(res.consumers.length);
                const tbody = $('#consumer-tbody').empty();

                if (res.consumers.length === 0) {
                    tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">No consumers found.</td></tr>');
                    return;
                }

                res.consumers.forEach(function (c) {
                    const initial = c.full_name.charAt(0).toUpperCase();
                    const toggleIcon  = c.status === 'active' ? 'pause-circle' : 'play-circle';
                    const toggleColor = c.status === 'active'
                        ? 'background:#fff8e1;color:#b45309;'
                        : 'background:#e8f5e9;color:#1a6b2f;';

                    tbody.append(`
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="consumer-avatar">${initial}</div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.88rem;">${c.full_name}</div>
                                        <div style="font-size:0.76rem;color:#6c757d;">${c.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.85rem;">${c.phone ?? '—'}</td>
                            <td style="font-size:0.82rem;max-width:150px;overflow:hidden;
                                text-overflow:ellipsis;white-space:nowrap;"
                                title="${c.address ?? ''}">${c.address ?? '—'}</td>
                            <td class="text-center" style="font-weight:700;color:#212529;">${c.total_complaints}</td>
                            <td class="text-center">
                                <span style="color:#f59e0b;font-weight:600;">${c.pending ?? 0}</span> /
                                <span style="color:#1a6b2f;font-weight:600;">${c.ongoing ?? 0}</span> /
                                <span style="color:#0d6efd;font-weight:600;">${c.resolved ?? 0}</span>
                            </td>
                            <td>${statusBadge(c.status)}</td>
                            <td style="font-size:0.82rem;">${formatDate(c.created_at)}</td>
                            <td>
                                <button class="btn-action btn-view-consumer" data-id="${c.id}" title="View details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-action btn-toggle-consumer"
                                    data-id="${c.id}" data-status="${c.status}"
                                    title="Toggle status"
                                    style="${toggleColor}">
                                    <i class="bi bi-${toggleIcon}"></i>
                                </button>
                                <button class="btn-action btn-delete-consumer"
                                    data-id="${c.id}" data-name="${c.full_name}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function () {
                $('#consumer-tbody').html(
                    '<tr><td colspan="8" class="text-center text-danger py-4">Server error.</td></tr>'
                );
            }
        });
    }

    // ── View consumer modal ───────────────────────────────────────
    $(document).on('click', '.btn-view-consumer', function () {
        const id = $(this).data('id');

        $('#modal-body-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <p class="mt-2 text-muted" style="font-size:0.85rem;">Loading...</p>
            </div>
        `);
        openModal();

        $.ajax({
            url: '../php/consumers.php',
            type: 'GET',
            data: { action: 'view', id },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#modal-body-content').html(`<p class="text-danger">${res.message}</p>`);
                    return;
                }

                const c = res.consumer;
                const initial = c.full_name.charAt(0).toUpperCase();

                // Complaint history rows
                let historyRows = '';
                if (res.complaints.length === 0) {
                    historyRows = '<tr><td colspan="3" class="text-center text-muted py-3" style="font-size:0.82rem;">No complaints filed yet.</td></tr>';
                } else {
                    res.complaints.forEach(function (comp) {
                        historyRows += `
                            <tr>
                                <td style="font-size:0.80rem;font-weight:600;color:#1a6b2f;">${comp.ticket_no}</td>
                                <td style="font-size:0.80rem;">${comp.complaint_type}</td>
                                <td>${complaintStatusBadge(comp.status)}</td>
                            </tr>`;
                    });
                }

                $('#modal-body-content').html(`
                    <!-- Consumer profile -->
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;
                        padding-bottom:16px;border-bottom:1px solid #e9ecef;">
                        <div style="width:54px;height:54px;background:#1a6b2f;color:#fff;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            font-size:1.3rem;font-weight:700;flex-shrink:0;">${initial}</div>
                        <div>
                            <div style="font-size:1rem;font-weight:700;color:#212529;">${c.full_name}</div>
                            <div style="font-size:0.82rem;color:#6c757d;">${c.email}</div>
                            <div style="margin-top:4px;">${statusBadge(c.status)}</div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="detail-grid" style="margin-bottom:20px;">
                        <div class="detail-row">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value">${c.phone ?? '—'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">${c.address ?? '—'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Registered</span>
                            <span class="detail-value">${formatDate(c.created_at)}</span>
                        </div>
                    </div>

                    <!-- Complaint history -->
                    <div style="font-size:0.82rem;font-weight:700;color:#6c757d;
                        text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">
                        Recent Complaints
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="padding:8px 10px;font-size:0.75rem;color:#6c757d;
                                        font-weight:700;text-align:left;">Ticket #</th>
                                    <th style="padding:8px 10px;font-size:0.75rem;color:#6c757d;
                                        font-weight:700;text-align:left;">Type</th>
                                    <th style="padding:8px 10px;font-size:0.75rem;color:#6c757d;
                                        font-weight:700;text-align:left;">Status</th>
                                </tr>
                            </thead>
                            <tbody>${historyRows}</tbody>
                        </table>
                    </div>
                `);
            },
            error: function () {
                $('#modal-body-content').html('<p class="text-danger">Failed to load consumer details.</p>');
            }
        });
    });

    // ── Toggle status ─────────────────────────────────────────────
    $(document).on('click', '.btn-toggle-consumer', function () {
        const id     = $(this).data('id');
        const status = $(this).data('status');
        const action = status === 'active' ? 'deactivate' : 'activate';

        if (!confirm(`Are you sure you want to ${action} this consumer account?`)) return;

        $.ajax({
            url: '../php/consumers.php?action=toggle_status',
            type: 'POST',
            data: { id },
            dataType: 'json',
            success: function (res) {
                if (res.success) { showToast(res.message, 'success'); loadConsumers(); }
                else showToast(res.message, 'danger');
            },
            error: function () { showToast('Server error.', 'danger'); }
        });
    });

    // ── Delete consumer ───────────────────────────────────────────
    $(document).on('click', '.btn-delete-consumer', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');

        if (!confirm(`Delete account of "${name}"?\n\nThis cannot be undone.`)) return;

        $.ajax({
            url: '../php/consumers.php?action=delete',
            type: 'POST',
            data: { id },
            dataType: 'json',
            success: function (res) {
                if (res.success) { showToast(res.message, 'success'); loadConsumers(); }
                else showToast(res.message, 'danger');
            },
            error: function () { showToast('Server error.', 'danger'); }
        });
    });

    // ── Filters ──────────────────────────────────────────────────
    $('#filter-status').on('change', loadConsumers);
    $('#btn-refresh').on('click', loadConsumers);

    let searchTimer;
    $('#search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadConsumers, 400);
    });

    // ── Init ─────────────────────────────────────────────────────
    loadConsumers();
});