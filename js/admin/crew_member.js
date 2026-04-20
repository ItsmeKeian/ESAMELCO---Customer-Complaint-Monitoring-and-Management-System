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
        return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function showToast(message, type) {
        const bg = type === 'success' ? '#1a6b2f' : '#dc3545';
        const toast = $(`
            <div style="position:fixed;bottom:24px;right:24px;z-index:9999;
                background:${bg};color:#fff;padding:12px 20px;border-radius:10px;
                font-size:0.88rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,0.2);
                max-width:320px;">${message}</div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3500);
    }

    function openModal(id)  { $(id).fadeIn(200); }
    function closeModal(id) { $(id).fadeOut(200); }

    // Close modals on backdrop click
    $('.modal-backdrop').on('click', function () {
        closeModal('#addModal');
        closeModal('#editModal');
    });

    $('#close-add-modal, #cancel-add').on('click', () => closeModal('#addModal'));
    $('#close-edit-modal, #cancel-edit').on('click', () => closeModal('#editModal'));

    // ── Load crew list ───────────────────────────────────────────
    function loadCrew() {
        const search = $('#search-input').val().trim();
        const status = $('#filter-status').val();

        $('#crew-tbody').html(
            '<tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>'
        );

        $.ajax({
            url: '../php/admin/crew_member.php',
            type: 'GET',
            data: { action: 'list', search, status },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#crew-tbody').html(`<tr><td colspan="7" class="text-center text-danger py-4">${res.message}</td></tr>`);
                    return;
                }

                $('#crew-count').text(res.crew.length);
                const tbody = $('#crew-tbody').empty();

                if (res.crew.length === 0) {
                    tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">No crew members found.</td></tr>');
                    return;
                }

                res.crew.forEach(function (c) {
                    const statusBadge = c.status === 'active'
                        ? '<span class="badge-status badge-active">Active</span>'
                        : '<span class="badge-status badge-inactive">Inactive</span>';

                    const availBadge = c.availability === 'busy'
                        ? `<span class="badge-avail busy">On Duty${c.ticket_no ? ' · ' + c.ticket_no : ''}</span>`
                        : '<span class="badge-avail available">Available</span>';

                    const initial = c.full_name.charAt(0).toUpperCase();

                    tbody.append(`
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="crew-avatar-sm">${initial}</div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.88rem;">${c.full_name}</div>
                                        <div style="font-size:0.76rem;color:#6c757d;">${c.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.85rem;">${c.phone ?? '—'}</td>
                            <td style="font-size:0.82rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="${c.address ?? ''}">${c.address ?? '—'}</td>
                            <td>${availBadge}</td>
                            <td>${statusBadge}</td>
                            <td style="font-size:0.82rem;">${formatDate(c.created_at)}</td>
                            <td>
                                <button class="btn-action btn-edit"   data-id="${c.id}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action btn-toggle" data-id="${c.id}"
                                    data-status="${c.status}" title="Toggle status"
                                    style="background:${c.status === 'active' ? '#fff8e1' : '#e8f5e9'};
                                           color:${c.status === 'active' ? '#b45309' : '#1a6b2f'};">
                                    <i class="bi bi-${c.status === 'active' ? 'pause-circle' : 'play-circle'}"></i>
                                </button>
                                <button class="btn-action btn-delete" data-id="${c.id}"
                                    data-name="${c.full_name}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function () {
                $('#crew-tbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Server error.</td></tr>');
            }
        });
    }

    // ── Add crew ─────────────────────────────────────────────────
    $('#btn-add-crew').on('click', function () {
        $('#add-form')[0].reset();
        $('#add-error').hide();
        openModal('#addModal');
    });

    $('#add-form').on('submit', function (e) {
        e.preventDefault();
        $('#add-error').hide();
        const btn = $('#btn-save-add').prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/admin_crew_member.php?action=add',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    closeModal('#addModal');
                    showToast(res.message, 'success');
                    loadCrew();
                } else {
                    $('#add-error').text(res.message).show();
                }
            },
            error: function () { $('#add-error').text('Server error.').show(); },
            complete: function () { btn.prop('disabled', false).text('Save'); }
        });
    });

    // ── Edit crew — load data ────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $('#edit-error').hide();
        $('#edit-password').val('');

        $.ajax({
            url: '../php/admin/crew_member.php',
            type: 'GET',
            data: { action: 'get', id },
            dataType: 'json',
            success: function (res) {
                if (!res.success) { showToast(res.message, 'danger'); return; }
                const c = res.crew;
                $('#edit-id').val(c.id);
                $('#edit-full-name').val(c.full_name);
                $('#edit-email').val(c.email);
                $('#edit-phone').val(c.phone ?? '');
                $('#edit-address').val(c.address ?? '');
                $('#edit-status').val(c.status);
                openModal('#editModal');
            },
            error: function () { showToast('Server error.', 'danger'); }
        });
    });

    // ── Edit crew — save ─────────────────────────────────────────
    $('#edit-form').on('submit', function (e) {
        e.preventDefault();
        $('#edit-error').hide();
        const btn = $('#btn-save-edit').prop('disabled', true).text('Saving...');

        $.ajax({
            url: '../php/admin/crew_member.php?action=edit',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    closeModal('#editModal');
                    showToast(res.message, 'success');
                    loadCrew();
                } else {
                    $('#edit-error').text(res.message).show();
                }
            },
            error: function () { $('#edit-error').text('Server error.').show(); },
            complete: function () { btn.prop('disabled', false).text('Save Changes'); }
        });
    });

    // ── Toggle status ────────────────────────────────────────────
    $(document).on('click', '.btn-toggle', function () {
        const id     = $(this).data('id');
        const status = $(this).data('status');
        const action = status === 'active' ? 'deactivate' : 'activate';

        if (!confirm(`Are you sure you want to ${action} this crew member?`)) return;

        $.ajax({
            url: '../php/admin/crew_member.php?action=toggle_status',
            type: 'POST',
            data: { id },
            dataType: 'json',
            success: function (res) {
                if (res.success) { showToast(res.message, 'success'); loadCrew(); }
                else showToast(res.message, 'danger');
            },
            error: function () { showToast('Server error.', 'danger'); }
        });
    });

    // ── Delete crew ──────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');

        if (!confirm(`Delete crew member "${name}"?\n\nThis cannot be undone.`)) return;

        $.ajax({
            url: '../php/admin/crew_member.php?action=delete',
            type: 'POST',
            data: { id },
            dataType: 'json',
            success: function (res) {
                if (res.success) { showToast(res.message, 'success'); loadCrew(); }
                else showToast(res.message, 'danger');
            },
            error: function () { showToast('Server error.', 'danger'); }
        });
    });

    // ── Filters ──────────────────────────────────────────────────
    $('#filter-status').on('change', loadCrew);
    $('#btn-refresh').on('click', loadCrew);

    let searchTimer;
    $('#search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadCrew, 400);
    });

    // ── Init ─────────────────────────────────────────────────────
    loadCrew();
});