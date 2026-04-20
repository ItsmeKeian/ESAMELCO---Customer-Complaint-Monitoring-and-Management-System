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

    // ── Chart instances (kept for later destroy on refresh) ──────
    let lineChart    = null;
    let doughnutChart = null;

    // ── Leaflet map instance ─────────────────────────────────────
    let map          = null;
    let crewMarkers  = [];

    // ── Initialize Leaflet Map ───────────────────────────────────
    function initMap() {
        map = L.map('crewMap').setView([11.5780, 125.5060], 11); // Eastern Samar center

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);
    }

    // ── Custom map marker icons ──────────────────────────────────
    function crewIcon(availability) {
        const color = availability === 'available' ? '#1a6b2f' : '#f59e0b';
        return L.divIcon({
            className: '',
            html: `<div style="
                width:34px; height:34px; border-radius:50%;
                background:${color}; border:3px solid #fff;
                box-shadow:0 2px 8px rgba(0,0,0,0.25);
                display:flex; align-items:center; justify-content:center;
                color:#fff; font-size:14px;">
                <i class='bi bi-person-fill'></i>
            </div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17],
            popupAnchor: [0, -20]
        });
    }

    // ── Place crew on map ────────────────────────────────────────
    function renderCrewOnMap(crewLocations) {
        crewMarkers.forEach(m => map.removeLayer(m));
        crewMarkers = [];

        crewLocations.forEach(function (crew) {
            if (!crew.latitude || !crew.longitude) return;

            const marker = L.marker(
                [parseFloat(crew.latitude), parseFloat(crew.longitude)],
                { icon: crewIcon(crew.availability) }
            ).addTo(map);

            marker.bindPopup(`
                <div style="font-family:Segoe UI,sans-serif; min-width:150px;">
                    <strong style="font-size:0.88rem;">${crew.full_name}</strong><br>
                    <span style="font-size:0.78rem; color:#6c757d;">
                        Status: <b style="color:${crew.availability === 'available' ? '#1a6b2f' : '#f59e0b'}">
                            ${crew.availability.charAt(0).toUpperCase() + crew.availability.slice(1)}
                        </b>
                    </span><br>
                    <span style="font-size:0.72rem; color:#adb5bd;">
                        Last seen: ${crew.last_seen}
                    </span>
                </div>
            `);

            crewMarkers.push(marker);
        });
    }

    // ── Build line chart (monthly complaints) ────────────────────
    function renderLineChart(monthly) {
        const labels = monthly.map(r => r.month);
        const data   = monthly.map(r => parseInt(r.total));

        if (lineChart) lineChart.destroy();

        const ctx = document.getElementById('lineChart').getContext('2d');
        lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Complaints',
                    data: data,
                    borderColor: '#1a6b2f',
                    backgroundColor: 'rgba(26,107,47,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#1a6b2f',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a6b2f',
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: '#f0f0f0' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── Build doughnut chart (complaints by type) ────────────────
    function renderDoughnutChart(byType) {
        const labels = byType.map(r => r.complaint_type);
        const data   = byType.map(r => parseInt(r.total));
        const colors = ['#1a6b2f','#4caf50','#ffc107','#2196f3','#e91e63'];

        if (doughnutChart) doughnutChart.destroy();

        const ctx = document.getElementById('doughnutChart').getContext('2d');
        doughnutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyleWidth: 10,
                        }
                    },
                    tooltip: {
                        backgroundColor: '#212529',
                        cornerRadius: 8,
                        padding: 10,
                    }
                }
            }
        });
    }

    // ── Render stats cards ───────────────────────────────────────
    function renderStats(stats) {
        $('#stat-total').text(stats.total);
        $('#stat-pending').text(stats.pending);
        $('#stat-ongoing').text(stats.ongoing);
        $('#stat-resolved').text(stats.resolved);

        // Update pending badge on sidebar
        $('#nav-badge-complaints').text(stats.pending);
    }

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

    // ── Render recent complaints table ───────────────────────────
    function renderTable(complaints) {
        const tbody = $('#complaints-tbody');
        tbody.empty();

        if (complaints.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted py-4">No complaints found.</td></tr>');
            return;
        }

        complaints.forEach(function (c) {
            const date = new Date(c.created_at).toLocaleDateString('en-PH', {
                month: 'short', day: 'numeric', year: 'numeric'
            });

            tbody.append(`
                <tr>
                    <td><span style="font-weight:600; color:#1a6b2f;">${c.ticket_no}</span></td>
                    <td>${c.consumer_name}</td>
                    <td>${c.complaint_type}</td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                        title="${c.description}">${c.description}</td>
                    <td>${c.crew_name ?? '<span class="text-muted">Unassigned</span>'}</td>
                    <td>${statusBadge(c.status)}</td>
                    <td>${date}</td>
                </tr>
            `);
        });
    }

    // ── Main: load all dashboard data via AJAX ───────────────────
    function loadDashboard() {
       

        $.ajax({
            url: '/esamelco/php/admin/dashboard.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    alert('Failed to load dashboard: ' + res.message);
                    return;
                }

                // Admin name
                $('#admin-name-display').text(res.adminName);
                $('#admin-initials').text(
                    res.adminName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
                );

                renderStats(res.stats);
                renderLineChart(res.monthly);
                renderDoughnutChart(res.byType);
                renderTable(res.recentComplaints);
                renderCrewOnMap(res.crewLocations);
            },
            error: function () {
                alert('Server error. Could not load dashboard data.');
            },
           
        });
    }

    // ── Init ─────────────────────────────────────────────────────
    initMap();
    loadDashboard();

    // Auto-refresh every 30 seconds
    setInterval(loadDashboard, 30000);

    // Manual refresh button
    $('#btn-refresh').on('click', function () {
        $(this).find('i').addClass('spin');
        loadDashboard();
        setTimeout(() => $('#btn-refresh i').removeClass('spin'), 1000);
    });
});