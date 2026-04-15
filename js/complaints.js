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

    // ── Status badge helper ──────────────────────────────────────
    function statusBadge(status) {
        const map = {
            pending:   '<span class="badge-status badge-pending">Pending</span>',
            ongoing:   '<span class="badge-status badge-ongoing">Ongoing</span>',
            resolved:  '<span class="badge-status badge-resolved">Resolved</span>',
            cancelled: '<span class="badge-status badge-cancelled">Cancelled</span>',
        };
        return map[status] || `<span class="badge-status">${status}</span>`;
    }

    // ── Format date ──────────────────────────────────────────────
    function formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    // ── Load complaints table ────────────────────────────────────
    function loadComplaints() {
        const status = $('#filter-status').val();
        const search = $('#search-input').val().trim();

        $('#complaints-tbody').html(
            '<tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>'
        );

        $.ajax({
            url: '../php/complaints.php',
            type: 'GET',
            data: { action: 'list', status: status, search: search },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#complaints-tbody').html(
                        `<tr><td colspan="8" class="text-center text-danger py-4">${res.message}</td></tr>`
                    );
                    return;
                }

                const complaints = res.complaints;
                const tbody = $('#complaints-tbody');
                tbody.empty();

                if (complaints.length === 0) {
                    tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">No complaints found.</td></tr>');
                    return;
                }

                complaints.forEach(function (c) {
                    tbody.append(`
                        <tr>
                            <td><span style="font-weight:600;color:#1a6b2f;">${c.ticket_no}</span></td>
                            <td>${c.consumer_name}<br>
                                <small class="text-muted">${c.consumer_phone ?? ''}</small>
                            </td>
                            <td>${c.complaint_type}</td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="${c.description}">${c.description}</td>
                            <td>${c.crew_name ?? '<span class="text-muted">Unassigned</span>'}</td>
                            <td>${statusBadge(c.status)}</td>
                            <td>${formatDate(c.created_at)}</td>
                            <td>
                                <button class="btn-action btn-view" data-id="${c.id}" title="View details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-action btn-delete" data-id="${c.id}" data-ticket="${c.ticket_no}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                // Update count badge
                $('#complaints-count').text(complaints.length);
            },
            error: function () {
                $('#complaints-tbody').html(
                    '<tr><td colspan="8" class="text-center text-danger py-4">Server error. Please try again.</td></tr>'
                );
            }
        });
    }

    // ── View complaint detail modal ──────────────────────────────
    $(document).on('click', '.btn-view', function () {
        const id = $(this).data('id');

        // Reset modal
        $('#modal-body-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <p class="mt-2 text-muted" style="font-size:0.85rem;">Loading details...</p>
            </div>
        `);
        $('#viewModal').fadeIn(200);

        $.ajax({
            url: '../php/complaints.php',
            type: 'GET',
            data: { action: 'view', id: id },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#modal-body-content').html(`<p class="text-danger">${res.message}</p>`);
                    return;
                }

                const c = res.complaint;
                const photo = c.photo
                    ? `<img src="../uploads/${c.photo}" class="img-fluid rounded mt-2" style="max-height:200px;">`
                    : `<span class="text-muted" style="font-size:0.85rem;">No photo attached</span>`;

                $('#modal-body-content').html(`
                    <div class="detail-grid">
                        <div class="detail-row">
                            <span class="detail-label">Ticket No.</span>
                            <span class="detail-value" style="color:#1a6b2f;font-weight:700;">${c.ticket_no}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Consumer</span>
                            <span class="detail-value">${c.consumer_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Contact</span>
                            <span class="detail-value">${c.consumer_phone ?? '—'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">${c.consumer_email}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Type</span>
                            <span class="detail-value">${c.complaint_type}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">${statusBadge(c.status)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Assigned Crew</span>
                            <span class="detail-value">${c.crew_name ?? '<span class="text-muted">Unassigned</span>'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ETA</span>
                            <span class="detail-value">${c.eta_minutes ? c.eta_minutes + ' mins' : '—'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date Filed</span>
                            <span class="detail-value">${formatDate(c.created_at)}</span>
                        </div>
                        <div class="detail-row" style="flex-direction:column;gap:6px;">
                            <span class="detail-label">Description</span>
                            <span class="detail-value">${c.description}</span>
                        </div>
                        <div class="detail-row" style="flex-direction:column;gap:6px;">
                            <span class="detail-label">Photo</span>
                            ${photo}
                        </div>
                    </div>

                    <!-- Update Status -->
                    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e9ecef;">
                        <label style="font-size:0.82rem;font-weight:600;color:#495057;margin-bottom:6px;display:block;">
                            Update Status
                        </label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <select id="status-select" class="form-select form-select-sm" style="max-width:160px;">
                                <option value="pending"   ${c.status==='pending'   ? 'selected':''}>Pending</option>
                                <option value="ongoing"   ${c.status==='ongoing'   ? 'selected':''}>Ongoing</option>
                                <option value="resolved"  ${c.status==='resolved'  ? 'selected':''}>Resolved</option>
                                <option value="cancelled" ${c.status==='cancelled' ? 'selected':''}>Cancelled</option>
                            </select>
                            <button id="btn-update-status" class="btn-save" data-id="${c.id}">
                                Save Status
                            </button>
                        </div>
                        <div id="status-msg" style="margin-top:8px;font-size:0.82rem;"></div>
                    </div>
                `);
            },
            error: function () {
                $('#modal-body-content').html('<p class="text-danger">Failed to load complaint details.</p>');
            }
        });
    });

    // ── Update status from modal ─────────────────────────────────
    $(document).on('click', '#btn-update-status', function () {
        const id     = $(this).data('id');
        const status = $('#status-select').val();
        const btn    = $(this);

        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/complaints.php?action=update_status',
            type: 'POST',
            data: { id: id, status: status },
            dataType: 'json',
            success: function (res) {
                const msg = $('#status-msg');
                if (res.success) {
                    msg.html('<span style="color:#1a6b2f;">✓ Status updated successfully.</span>');
                    loadComplaints(); // refresh table
                } else {
                    msg.html(`<span style="color:#dc3545;">${res.message}</span>`);
                }
            },
            error: function () {
                $('#status-msg').html('<span style="color:#dc3545;">Server error.</span>');
            },
            complete: function () {
                btn.prop('disabled', false).text('Save Status');
            }
        });
    });

    // ── Delete complaint ─────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        const id     = $(this).data('id');
        const ticket = $(this).data('ticket');

        if (!confirm(`Delete complaint ${ticket}?\n\nThis cannot be undone.`)) return;

        $.ajax({
            url: '../php/complaints.php?action=delete',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    loadComplaints();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function () {
                alert('Server error. Could not delete complaint.');
            }
        });
    });

    // ── Close modal ──────────────────────────────────────────────
    $('#modal-close, #viewModal-backdrop').on('click', function () {
        $('#viewModal').fadeOut(200);
    });

    // ── Filter & Search ──────────────────────────────────────────
    $('#filter-status').on('change', loadComplaints);

    let searchTimer;
    $('#search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadComplaints, 400);
    });

    // ── Initial load ─────────────────────────────────────────────
    loadComplaints();
});