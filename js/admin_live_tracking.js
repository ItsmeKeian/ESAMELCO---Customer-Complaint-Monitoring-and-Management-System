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

    // ── Leaflet map init ─────────────────────────────────────────
    const map = L.map('trackingMap').setView([11.5780, 125.5060], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    // ── Marker storage ───────────────────────────────────────────
    let crewMarkers     = {};   // crew_id → L.marker
    let complaintMarkers = {};  // complaint_id → L.marker
    let routeLines      = {};   // crew_id → L.polyline

    // ── Custom crew icon ─────────────────────────────────────────
    function crewIcon(availability, initial) {
        const bg = availability === 'busy' ? '#f59e0b' : '#1a6b2f';
        return L.divIcon({
            className: '',
            html: `
                <div style="
                    width:40px; height:40px; border-radius:50%;
                    background:${bg}; border:3px solid #fff;
                    box-shadow:0 2px 10px rgba(0,0,0,0.25);
                    display:flex; align-items:center; justify-content:center;
                    color:#fff; font-size:15px; font-weight:700;
                    font-family:Segoe UI,sans-serif;
                ">${initial}</div>`,
            iconSize:   [40, 40],
            iconAnchor: [20, 20],
            popupAnchor:[0, -24],
        });
    }

    // ── Complaint pin icon ───────────────────────────────────────
    const complaintIcon = L.divIcon({
        className: '',
        html: `
            <div style="
                width:32px; height:32px; border-radius:50%;
                background:#dc3545; border:3px solid #fff;
                box-shadow:0 2px 8px rgba(0,0,0,0.25);
                display:flex; align-items:center; justify-content:center;
                color:#fff; font-size:14px;
            "><i class='bi bi-exclamation-lg'></i></div>`,
        iconSize:   [32, 32],
        iconAnchor: [16, 16],
        popupAnchor:[0, -20],
    });

    // ── Format last seen time ────────────────────────────────────
    function timeSince(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)  return `${diff}s ago`;
        if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
        return `${Math.floor(diff/3600)}h ago`;
    }

    // ── Build crew popup HTML ────────────────────────────────────
    function crewPopup(c) {
        const statusColor = c.availability === 'busy' ? '#f59e0b' : '#1a6b2f';
        const statusLabel = c.availability === 'busy' ? 'On Duty' : 'Available';
        const assignment  = c.ticket_no
            ? `<div style="margin-top:8px;padding-top:8px;border-top:1px solid #eee;">
                   <div style="font-size:0.75rem;color:#6c757d;font-weight:700;text-transform:uppercase;">Assigned to</div>
                   <div style="font-size:0.82rem;font-weight:600;color:#1a6b2f;">${c.ticket_no}</div>
                   <div style="font-size:0.78rem;color:#495057;">${c.complaint_type}</div>
                   <div style="font-size:0.75rem;color:#6c757d;">Consumer: ${c.consumer_name}</div>
                   ${c.eta_minutes ? `<div style="font-size:0.75rem;color:#f59e0b;font-weight:600;">ETA: ${c.eta_minutes} min</div>` : ''}
               </div>`
            : '';

        return `
            <div style="font-family:Segoe UI,sans-serif;min-width:190px;">
                <div style="font-size:0.92rem;font-weight:700;color:#212529;">${c.full_name}</div>
                <div style="font-size:0.78rem;margin-top:3px;">
                    Status: <span style="color:${statusColor};font-weight:600;">${statusLabel}</span>
                </div>
                ${c.phone ? `<div style="font-size:0.75rem;color:#6c757d;">📞 ${c.phone}</div>` : ''}
                <div style="font-size:0.72rem;color:#adb5bd;margin-top:2px;">Last seen: ${timeSince(c.last_seen)}</div>
                ${assignment}
            </div>`;
    }

    // ── Main: load and render tracking data ──────────────────────
    function loadTracking() {
        $.ajax({
            url: '../php/live_tracking.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) return;

                // Update summary cards
                $('#count-total').text(res.summary.total);
                $('#count-busy').text(res.summary.busy);
                $('#count-available').text(res.summary.available);
                $('#last-updated').text(new Date().toLocaleTimeString('en-PH'));

                renderSidebarList(res.crew);
                renderMapMarkers(res.crew);
            },
            error: function () {
                console.warn('Failed to load tracking data.');
            }
        });
    }

    // ── Render sidebar crew list ─────────────────────────────────
    function renderSidebarList(crewList) {
        const container = $('#crew-sidebar-list');
        container.empty();

        if (crewList.length === 0) {
            container.html('<p class="text-muted text-center p-3" style="font-size:0.85rem;">No active crew found.</p>');
            return;
        }

        crewList.forEach(function (c) {
            const isBusy   = c.availability === 'busy';
            const dotColor = isBusy ? '#f59e0b' : '#1a6b2f';
            const initial  = c.full_name.charAt(0).toUpperCase();

            const item = $(`
                <div class="crew-list-item" data-crew-id="${c.id}"
                     data-lat="${c.latitude}" data-lng="${c.longitude}">
                    <div class="crew-list-avatar" style="background:${dotColor};">${initial}</div>
                    <div class="crew-list-info">
                        <div class="crew-list-name">${c.full_name}</div>
                        <div class="crew-list-meta">
                            <span class="status-dot" style="background:${dotColor};"></span>
                            ${isBusy ? 'On Duty' : 'Available'}
                            ${c.ticket_no ? ` &middot; ${c.ticket_no}` : ''}
                        </div>
                        <div class="crew-list-seen">${timeSince(c.last_seen)}</div>
                    </div>
                </div>
            `);

            container.append(item);
        });
    }

    // ── Click sidebar item → fly to crew on map ──────────────────
    $(document).on('click', '.crew-list-item', function () {
        $('.crew-list-item').removeClass('active');
        $(this).addClass('active');

        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));
        const id  = $(this).data('crew-id');

        if (lat && lng) {
            map.flyTo([lat, lng], 15, { animate: true, duration: 1.2 });
            if (crewMarkers[id]) crewMarkers[id].openPopup();
        }
    });

    // ── Render map markers ───────────────────────────────────────
    function renderMapMarkers(crewList) {
        const seenCrewIds      = new Set();
        const seenComplaintIds = new Set();

        crewList.forEach(function (c) {
            if (!c.latitude || !c.longitude) return;

            const lat     = parseFloat(c.latitude);
            const lng     = parseFloat(c.longitude);
            const initial = c.full_name.charAt(0).toUpperCase();

            seenCrewIds.add(c.id);

            // Update or create crew marker
            if (crewMarkers[c.id]) {
                crewMarkers[c.id].setLatLng([lat, lng]);
                crewMarkers[c.id].setIcon(crewIcon(c.availability, initial));
                crewMarkers[c.id].setPopupContent(crewPopup(c));
            } else {
                crewMarkers[c.id] = L.marker([lat, lng], {
                    icon: crewIcon(c.availability, initial)
                }).addTo(map).bindPopup(crewPopup(c));
            }

            // Complaint pin + route line (if assigned)
            if (c.complaint_lat && c.complaint_lng && c.complaint_id) {
                const clat = parseFloat(c.complaint_lat);
                const clng = parseFloat(c.complaint_lng);
                seenComplaintIds.add(c.complaint_id);

                // Complaint marker
                if (!complaintMarkers[c.complaint_id]) {
                    complaintMarkers[c.complaint_id] = L.marker([clat, clng], {
                        icon: complaintIcon
                    }).addTo(map).bindPopup(`
                        <div style="font-family:Segoe UI,sans-serif;">
                            <div style="font-weight:700;color:#dc3545;">${c.ticket_no}</div>
                            <div style="font-size:0.82rem;">${c.complaint_type}</div>
                            <div style="font-size:0.78rem;color:#6c757d;">Consumer: ${c.consumer_name}</div>
                        </div>
                    `);
                }

                // Dashed route line: crew → complaint
                if (routeLines[c.id]) {
                    routeLines[c.id].setLatLngs([[lat, lng], [clat, clng]]);
                } else {
                    routeLines[c.id] = L.polyline(
                        [[lat, lng], [clat, clng]],
                        { color: '#f59e0b', weight: 2, dashArray: '6 6', opacity: 0.8 }
                    ).addTo(map);
                }
            } else {
                // Remove old route line if no longer assigned
                if (routeLines[c.id]) {
                    map.removeLayer(routeLines[c.id]);
                    delete routeLines[c.id];
                }
            }
        });

        // Remove markers for crew no longer active
        Object.keys(crewMarkers).forEach(function (id) {
            if (!seenCrewIds.has(parseInt(id))) {
                map.removeLayer(crewMarkers[id]);
                delete crewMarkers[id];
                if (routeLines[id]) { map.removeLayer(routeLines[id]); delete routeLines[id]; }
            }
        });

        // Remove complaint markers no longer active
        Object.keys(complaintMarkers).forEach(function (id) {
            if (!seenComplaintIds.has(parseInt(id))) {
                map.removeLayer(complaintMarkers[id]);
                delete complaintMarkers[id];
            }
        });
    }

    // ── Manual refresh ───────────────────────────────────────────
    $('#btn-refresh').on('click', function () {
        $(this).find('i').addClass('spin');
        loadTracking();
        setTimeout(() => $('#btn-refresh i').removeClass('spin'), 800);
    });

    // ── Initial load + auto-refresh every 10 seconds ─────────────
    loadTracking();
    setInterval(loadTracking, 10000);
});