$(document).ready(function () {

    // ── Format date ──────────────────────────────────────────────
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

    // ── Complaint type icon ───────────────────────────────────────
    function typeIcon(type) {
        const t = (type || '').toLowerCase();
        if (t.includes('power') || t.includes('interrupt')) return 'bi-lightning-charge';
        if (t.includes('flicker'))  return 'bi-lightbulb';
        if (t.includes('line'))     return 'bi-diagram-3';
        if (t.includes('meter'))    return 'bi-speedometer';
        if (t.includes('bill'))     return 'bi-receipt';
        return 'bi-exclamation-circle';
    }

    // ── Render stats ─────────────────────────────────────────────
    function renderStats(s) {
        $('#stat-total').text(s.total    ?? 0);
        $('#stat-pending').text(s.pending  ?? 0);
        $('#stat-ongoing').text(s.ongoing  ?? 0);
        $('#stat-resolved').text(s.resolved ?? 0);
    }

    // ── Render recent complaints ──────────────────────────────────
    function renderRecent(complaints) {
        const container = $('#recent-list');
        container.empty();

        if (complaints.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x"></i>
                    <p>You have no complaints yet.<br>
                       <a href="submit_complaint.php" style="color:#1a6b2f;font-weight:600;">
                           File your first complaint
                       </a>
                    </p>
                </div>
            `);
            return;
        }

        complaints.forEach(function (c) {
            const eta = (c.status === 'ongoing' && c.eta_minutes)
                ? `<span class="eta-pill"><i class="bi bi-clock"></i> ETA: ${c.eta_minutes} min</span>`
                : '';

            const crew = c.crew_name
                ? `<div style="font-size:0.72rem;color:#6c757d;margin-top:2px;">
                       <i class="bi bi-person"></i> ${c.crew_name}
                   </div>`
                : '';

            container.append(`
                <div class="complaint-item">
                    <div class="complaint-type-icon">
                        <i class="bi ${typeIcon(c.complaint_type)}"></i>
                    </div>
                    <div class="complaint-info">
                        <div class="complaint-ticket">${c.ticket_no}</div>
                        <div class="complaint-type-label">${c.complaint_type}</div>
                        <div class="complaint-desc">${c.description}</div>
                        ${crew}
                        <div class="complaint-date">${formatDate(c.created_at)}</div>
                    </div>
                    <div class="complaint-right">
                        ${statusBadge(c.status)}
                        ${eta}
                    </div>
                </div>
            `);
        });
    }

    // ── Render notifications ──────────────────────────────────────
    function renderNotifications(notifications, unreadCount) {
        // Update bell badge
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
                    <div class="notif-dot-wrap">
                        <i class="bi bi-bell"></i>
                    </div>
                    <div>
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-message">${n.message}</div>
                        <div class="notif-time">${timeAgo(n.created_at)}</div>
                    </div>
                </div>
            `);
        });
    }

    // ── Main load ─────────────────────────────────────────────────
    function loadDashboard() {
        $.ajax({
            url: '/esamelco/php/consumer/dashboard.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;

                // Set consumer name and avatar
                const name     = res.consumer_name;
                const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                $('#consumer-name-display').text(name);
                $('#consumer-initials').text(initials);
                $('#welcome-name').text(name.split(' ')[0]); // First name only

                renderStats(res.summary);
                renderRecent(res.recent);
                renderNotifications(res.notifications, res.unread_count);
            },
            error: function () {
                console.warn('Failed to load dashboard.');
            }
        });
    }

    // ── Init ─────────────────────────────────────────────────────
    loadDashboard();

    // Auto-refresh notifications every 15 seconds
    setInterval(loadDashboard, 15000);
});