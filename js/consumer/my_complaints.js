$(document).ready(function () {

    let map         = null;
    let crewMarker  = null;
    let compMarker  = null;
    let routeLine   = null;

    // ── Helpers ──────────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    function formatDateTime(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function statusBadge(status) {
        const map = {
            pending:   '<span class="badge-status badge-pending">Pending</span>',
            ongoing:   '<span class="badge-status badge-ongoing">Ongoing</span>',
            resolved:  '<span class="badge-status badge-resolved">Resolved</span>',
            cancelled: '<span class="badge-status badge-cancelled">Cancelled</span>',
        };
        return map[status] || `<span class="badge-status">${status}</span>`;
    }

    function typeIcon(type) {
        const t = (type || '').toLowerCase();
        if (t.includes('power') || t.includes('interrupt')) return 'bi-lightning-charge';
        if (t.includes('flicker'))  return 'bi-lightbulb';
        if (t.includes('line'))     return 'bi-diagram-3';
        if (t.includes('meter'))    return 'bi-speedometer';
        if (t.includes('bill'))     return 'bi-receipt';
        return 'bi-exclamation-circle';
    }

    // ── Load complaints ───────────────────────────────────────────
    function loadComplaints() {
        const filter = $('.filter-tab.active').data('filter') || 'all';

        $('#complaints-list').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                <p style="font-size:0.85rem;">Loading...</p>
            </div>
        `);

        $.ajax({
            url: '/esamelco/php/consumer/my_complaints.php',
            type: 'GET',
            data: { action: 'list', filter: filter },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#complaints-list').html(`<p class="text-danger p-3">${res.message}</p>`);
                    return;
                }
                $('#complaint-count').text(res.complaints.length);
                renderComplaints(res.complaints);
            },
            error: function () {
                $('#complaints-list').html('<p class="text-danger p-3">Server error.</p>');
            }
        });
    }

    // ── Render complaint cards ────────────────────────────────────
    function renderComplaints(complaints) {
        const container = $('#complaints-list');
        container.empty();

        if (complaints.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x"></i>
                    <p>No complaints found.<br>
                       <a href="submit_complaint.php">File your first complaint</a>
                    </p>
                </div>
            `);
            return;
        }

        complaints.forEach(function (c) {
            const crewHtml = c.crew_name
                ? `<div class="crew-indicator">
                       <i class="bi bi-person-fill"></i> ${c.crew_name}
                   </div>`
                : '';

            const etaHtml = (c.status === 'ongoing' && c.eta_minutes)
                ? `<div class="eta-pill">
                       <i class="bi bi-clock"></i> ETA: ${c.eta_minutes} min
                   </div>`
                : '';

            const feedbackHtml = (c.status === 'resolved' && c.feedback_id)
                ? `<div class="feedback-given">
                       <i class="bi bi-star-fill"></i> Feedback given (${c.feedback_rating}/5)
                   </div>`
                : '';

            container.append(`
                <div class="complaint-card status-${c.status}" data-id="${c.id}">
                    <div class="card-header-row">
                        <div>
                            <div class="card-ticket">
                                <i class="bi ${typeIcon(c.complaint_type)} me-1"></i>
                                ${c.ticket_no}
                            </div>
                            <div class="card-type">${c.complaint_type}</div>
                        </div>
                        ${statusBadge(c.status)}
                    </div>
                    <div class="card-desc">${c.description}</div>
                    ${crewHtml}
                    ${etaHtml}
                    ${feedbackHtml}
                    <div class="card-meta">
                        <span><i class="bi bi-calendar3"></i>${formatDate(c.created_at)}</span>
                        ${c.latitude ? '<span><i class="bi bi-geo-alt-fill" style="color:#1a6b2f;"></i>GPS</span>' : ''}
                    </div>
                </div>
            `);
        });
    }

    // ── Click card → open detail modal ────────────────────────────
    $(document).on('click', '.complaint-card', function () {
        const id = $(this).data('id');
        openDetailModal(id);
    });

    // ── Open detail modal ─────────────────────────────────────────
    function openDetailModal(id) {
        $('#modal-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 text-muted" style="font-size:0.85rem;">Loading details...</p>
            </div>
        `);
        $('#detailModal').css('display', 'flex');

        $.ajax({
            url: '/esamelco/php/consumer/my_complaints.php',
            type: 'GET',
            data: { action: 'view', id: id },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#modal-body').html(`<p class="text-danger">${res.message}</p>`);
                    return;
                }
                renderModalContent(res.complaint);
            },
            error: function () {
                $('#modal-body').html('<p class="text-danger">Failed to load details.</p>');
            }
        });
    }

    // ── Render modal content ──────────────────────────────────────
    function renderModalContent(c) {

        // Status timeline
        const steps  = ['pending', 'ongoing', 'resolved'];
        const labels = ['Filed', 'In Progress', 'Resolved'];
        const icons  = ['bi-send', 'bi-tools', 'bi-check2-circle'];
        const curIdx = steps.indexOf(c.status);

        let timelineHtml = '<div class="status-timeline">';
        steps.forEach(function (step, i) {
            const isDone    = i < curIdx;
            const isCurrent = i === curIdx;
            const dotClass  = isDone ? 'done' : isCurrent ? 'current' : '';
            const iconClass = isDone ? 'bi-check' : icons[i];

            if (i > 0) {
                timelineHtml += `<div class="timeline-line ${isDone ? 'done' : ''}"></div>`;
            }

            timelineHtml += `
                <div class="timeline-step">
                    <div class="timeline-dot ${dotClass}">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="timeline-label">${labels[i]}</div>
                </div>`;
        });
        timelineHtml += '</div>';

        // Photo
        const photoHtml = c.photo
            ? `<img src="/esamelco/uploads/${c.photo}" class="modal-photo" alt="Complaint photo">`
            : '';

        // Crew section
        const crewHtml = c.crew_name ? `
            <div class="modal-section">
                <div class="modal-section-title">Assigned Crew</div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;background:#1a6b2f;color:#fff;
                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                        font-size:1rem;font-weight:700;flex-shrink:0;">
                        ${c.crew_name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:0.92rem;">${c.crew_name}</div>
                        ${c.crew_phone ? `<div style="font-size:0.80rem;color:#6c757d;"><i class="bi bi-telephone me-1"></i>${c.crew_phone}</div>` : ''}
                        ${c.eta_minutes ? `<div class="eta-pill" style="margin-top:4px;"><i class="bi bi-clock"></i> ETA: ${c.eta_minutes} min</div>` : ''}
                    </div>
                </div>
            </div>` : '';

        // Map section (show if complaint has GPS OR crew has GPS)
        const hasMap = (c.latitude && c.longitude) || (c.crew_lat && c.crew_lng);
        const mapHtml = hasMap
            ? `<div class="modal-section">
                   <div class="modal-section-title">Location Map</div>
                   <div id="complaint-map"></div>
               </div>`
            : '';

        // Feedback / Rate button
        let feedbackHtml = '';
        if (c.status === 'resolved') {
            if (c.feedback_id) {
                feedbackHtml = `
                    <div class="modal-section">
                        <div class="modal-section-title">Your Feedback</div>
                        <div style="display:flex;gap:4px;margin-bottom:6px;">
                            ${[1,2,3,4,5].map(i =>
                                `<i class="bi bi-star-fill" style="color:${i <= c.feedback_rating ? '#ffc107' : '#dee2e6'};font-size:1.2rem;"></i>`
                            ).join('')}
                        </div>
                        ${c.feedback_comment ? `<div style="font-size:0.85rem;color:#6c757d;">"${c.feedback_comment}"</div>` : ''}
                    </div>`;
            } else {
                feedbackHtml = `
                    <div class="modal-section">
                        <div class="modal-section-title">Rate This Service</div>
                        <a href="feedback.php?complaint_id=${c.id}" class="btn-rate">
                            <i class="bi bi-star-fill"></i> Rate Your Experience
                        </a>
                    </div>`;
            }
        }

        $('#modal-body').html(`
            <!-- Ticket header -->
            <div style="margin-bottom:16px;">
                <div style="font-size:0.80rem;font-weight:700;color:#1a6b2f;margin-bottom:4px;">
                    ${c.ticket_no}
                </div>
                <div style="font-size:1.1rem;font-weight:700;color:#212529;margin-bottom:6px;">
                    ${c.complaint_type}
                </div>
                ${statusBadge(c.status)}
            </div>

            <!-- Status timeline -->
            <div class="modal-section">
                <div class="modal-section-title">Status</div>
                ${timelineHtml}
            </div>

            <!-- Description -->
            <div class="modal-section">
                <div class="modal-section-title">Description</div>
                <div style="font-size:0.88rem;color:#495057;line-height:1.6;">${c.description}</div>
                ${photoHtml ? `<div style="margin-top:10px;">${photoHtml}</div>` : ''}
            </div>

            <!-- Dates -->
            <div class="modal-section">
                <div class="modal-section-title">Timeline</div>
                <div style="font-size:0.82rem;color:#6c757d;display:flex;flex-direction:column;gap:4px;">
                    <div><i class="bi bi-calendar3 me-2"></i>Filed: ${formatDateTime(c.created_at)}</div>
                    ${c.assigned_at ? `<div><i class="bi bi-send me-2"></i>Dispatched: ${formatDateTime(c.assigned_at)}</div>` : ''}
                </div>
            </div>

            ${crewHtml}
            ${mapHtml}
            ${feedbackHtml}
        `);

        // Initialize map after content is rendered
        if (hasMap) {
            setTimeout(() => initModalMap(c), 300);
        }
    }

    // ── Initialize map in modal ───────────────────────────────────
    function initModalMap(c) {
        if (map) {
            map.remove();
            map = null;
        }

        const centerLat = parseFloat(c.crew_lat || c.latitude);
        const centerLng = parseFloat(c.crew_lng || c.longitude);

        map = L.map('complaint-map').setView([centerLat, centerLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);

        // Complaint location pin (red)
        if (c.latitude && c.longitude) {
            const compIcon = L.divIcon({
                className: '',
                html: `<div style="width:32px;height:32px;border-radius:50%;background:#dc3545;
                    border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.25);
                    display:flex;align-items:center;justify-content:center;
                    color:#fff;font-size:14px;">
                    <i class='bi bi-exclamation-lg'></i></div>`,
                iconSize: [32,32], iconAnchor: [16,16], popupAnchor: [0,-20],
            });
            compMarker = L.marker([parseFloat(c.latitude), parseFloat(c.longitude)], { icon: compIcon })
                .addTo(map)
                .bindPopup('<b>Complaint Location</b>');
        }

        // Crew location pin (green)
        if (c.crew_lat && c.crew_lng) {
            const crewIcon = L.divIcon({
                className: '',
                html: `<div style="width:34px;height:34px;border-radius:50%;background:#1a6b2f;
                    border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.25);
                    display:flex;align-items:center;justify-content:center;
                    color:#fff;font-size:14px;">
                    <i class='bi bi-person-fill'></i></div>`,
                iconSize: [34,34], iconAnchor: [17,17], popupAnchor: [0,-20],
            });
            crewMarker = L.marker([parseFloat(c.crew_lat), parseFloat(c.crew_lng)], { icon: crewIcon })
                .addTo(map)
                .bindPopup(`<b>${c.crew_name}</b><br><small>Maintenance Crew</small>`);

            // Route line
            if (c.latitude && c.longitude) {
                routeLine = L.polyline(
                    [[parseFloat(c.crew_lat), parseFloat(c.crew_lng)],
                     [parseFloat(c.latitude), parseFloat(c.longitude)]],
                    { color: '#1a6b2f', weight: 2, dashArray: '6 6', opacity: 0.7 }
                ).addTo(map);

                // Fit both markers
                map.fitBounds([
                    [parseFloat(c.crew_lat), parseFloat(c.crew_lng)],
                    [parseFloat(c.latitude), parseFloat(c.longitude)]
                ], { padding: [30, 30] });
            }
        }
    }

    // ── Close modal ───────────────────────────────────────────────
    function closeModal() {
        $('#detailModal').css('display', 'none');
        if (map) { map.remove(); map = null; }
    }

    $('#modal-close-btn, #modal-backdrop').on('click', closeModal);

    // ── Filter tabs ───────────────────────────────────────────────
    $('.filter-tab').on('click', function () {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        loadComplaints();
    });

    // ── Init ─────────────────────────────────────────────────────
    loadComplaints();
});