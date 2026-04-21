$(document).ready(function () {

    // ── Helpers ──────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return 'Just now';
        if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return formatDate(dateStr);
    }

    // ── Render active job card ────────────────────────────────────
    function renderActiveJob(job) {
        const container = $('#active-job-container');

        if (!job) {
            container.html(`
                <div class="no-job-card">
                    <i class="bi bi-check2-circle text-success"></i>
                    <h6>No Active Assignment</h6>
                    <p>You currently have no assigned job.<br>
                       Stand by for new dispatches from admin.</p>
                </div>
            `);
            return;
        }

        const eta = job.eta_minutes
            ? `<div class="job-eta"><i class="bi bi-clock"></i> ETA: ${job.eta_minutes} minutes</div>`
            : '';

        container.html(`
            <div class="active-job-card">
                <div class="job-card-label">
                    <i class="bi bi-tools me-1"></i> Active Assignment
                </div>
                <div class="job-ticket">${job.ticket_no}</div>
                <div class="job-type">${job.complaint_type}</div>
                <div class="job-desc">${job.description}</div>
                <div class="job-consumer">
                    <i class="bi bi-person-fill"></i>
                    ${job.consumer_name}
                    ${job.consumer_phone ? `&nbsp;&middot;&nbsp;<i class="bi bi-telephone-fill"></i> ${job.consumer_phone}` : ''}
                </div>
                ${job.consumer_address
                    ? `<div class="job-consumer"><i class="bi bi-geo-alt-fill"></i> ${job.consumer_address}</div>`
                    : ''}
                ${eta}
                <div class="job-actions">
                    <a href="my_assignments.php" class="btn-job-action btn-view-job">
                        <i class="bi bi-list-check"></i> View Assignment
                    </a>
                    <button class="btn-job-action btn-update-gps" id="btn-quick-gps">
                        <i class="bi bi-geo-alt-fill"></i> Share Location
                    </button>
                </div>
            </div>
        `);
    }

    // ── Render stats ──────────────────────────────────────────────
    function renderStats(s) {
        const total = (parseInt(s.completed) || 0) + (parseInt(s.active) || 0) + (parseInt(s.cancelled) || 0);
        $('#stat-total').text(total);
        $('#stat-completed').text(s.completed ?? 0);
        $('#stat-active').text(s.active ?? 0);
    }

    // ── Render recent jobs ────────────────────────────────────────
    function renderRecentJobs(jobs) {
        const container = $('#recent-jobs-list');
        container.empty();

        if (jobs.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x"></i>
                    <p>No completed jobs yet.</p>
                </div>
            `);
            return;
        }

        jobs.forEach(function (j) {
            container.append(`
                <div class="job-row">
                    <div class="job-row-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="job-row-info">
                        <div class="job-row-ticket">${j.ticket_no}</div>
                        <div class="job-row-type">${j.complaint_type}</div>
                        <div class="job-row-consumer">
                            <i class="bi bi-person"></i> ${j.consumer_name}
                        </div>
                    </div>
                    <div class="job-row-date">
                        ${j.completed_at ? formatDate(j.completed_at) : '—'}
                    </div>
                </div>
            `);
        });
    }

    // ── Render notifications ──────────────────────────────────────
    function renderNotifications(notifications, unreadCount) {
        if (unreadCount > 0) {
            $('#bell-badge').text(unreadCount).show();
        } else {
            $('#bell-badge').hide();
        }

        const container = $('#notif-list');
        container.empty();

        if (notifications.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications yet.</p>
                </div>
            `);
            return;
        }

        notifications.forEach(function (n) {
            container.append(`
                <div class="notif-item ${!parseInt(n.is_read) ? 'unread' : ''}">
                    <div class="notif-icon"><i class="bi bi-bell"></i></div>
                    <div>
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-message">${n.message}</div>
                        <div class="notif-time">${timeAgo(n.created_at)}</div>
                    </div>
                </div>
            `);
        });
    }

    // ── GPS sharing ───────────────────────────────────────────────
    function shareGPS(btnEl) {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        if (btnEl) $(btnEl).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Getting location...');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                $.ajax({
                    url: '/esamelco/php/crew/update_location.php',
                    type: 'POST',
                    data: { latitude: lat, longitude: lng },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            $('#gps-status-text').text(`Location shared — ${new Date().toLocaleTimeString('en-PH')}`);
                            showToast('Location updated successfully!', 'success');
                        }
                    },
                    complete: function () {
                        if (btnEl) {
                            $(btnEl).prop('disabled', false).html(
                                '<i class="bi bi-geo-alt-fill"></i> Share My Location'
                            );
                        }
                    }
                });
            },
            function () {
                if (btnEl) $(btnEl).prop('disabled', false).html('<i class="bi bi-geo-alt-fill"></i> Share My Location');
                alert('Could not get location. Please allow location access.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    // ── GPS bar button ────────────────────────────────────────────
    $('#btn-share-gps').on('click', function () { shareGPS(this); });

    // ── Quick GPS from active job card ────────────────────────────
    $(document).on('click', '#btn-quick-gps', function () { shareGPS(this); });

    // ── Toast ─────────────────────────────────────────────────────
    function showToast(message, type) {
        const bg    = type === 'success' ? '#1a6b2f' : '#dc3545';
        const toast = $(`
            <div style="position:fixed;bottom:24px;right:24px;z-index:9999;
                background:${bg};color:#fff;padding:12px 20px;border-radius:10px;
                font-size:0.88rem;font-weight:600;
                box-shadow:0 4px 16px rgba(0,0,0,0.2);max-width:300px;">
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
    }

    // ── Main load ─────────────────────────────────────────────────
    function loadDashboard() {
        $.ajax({
            url: '/esamelco/php/crew/dashboard.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;

                // Set crew name and avatar
                const name     = res.crew_name;
                const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                $('#crew-name-display').text(name);
                $('#crew-initials').text(initials);
                $('#welcome-name').text(name.split(' ')[0]);

                renderActiveJob(res.active_job);
                renderStats(res.stats);
                renderRecentJobs(res.recent_jobs);
                renderNotifications(res.notifications, res.unread_count);

                // Update GPS status if location exists
                if (res.location && res.location.latitude) {
                    $('#gps-status-text').text(
                        `Last shared: ${timeAgo(res.location.updated_at)}`
                    );
                }
            },
            error: function () {
                console.warn('Failed to load crew dashboard.');
            }
        });
    }

    // ── Init + auto-refresh every 20 seconds ─────────────────────
    loadDashboard();
    setInterval(loadDashboard, 20000);
});