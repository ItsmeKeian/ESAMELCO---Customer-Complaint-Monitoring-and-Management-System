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

    // ── Chart instances ──────────────────────────────────────────
    let barChart      = null;
    let doughnutChart = null;
    let typeChart     = null;

    // ── Chart color palette ──────────────────────────────────────
    const COLORS = ['#1a6b2f','#4caf50','#2196f3','#ffc107','#e91e63','#9c27b0'];

    // ── Format date ──────────────────────────────────────────────
    function formatDate(d) {
        return new Date(d).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    // ── Status badge ─────────────────────────────────────────────
    function statusBadge(status) {
        const map = {
            pending:   '<span class="badge-status badge-pending">Pending</span>',
            ongoing:   '<span class="badge-status badge-ongoing">Ongoing</span>',
            resolved:  '<span class="badge-status badge-resolved">Resolved</span>',
            cancelled: '<span class="badge-status badge-cancelled">Cancelled</span>',
        };
        return map[status] || status;
    }

    // ── Populate year dropdown ────────────────────────────────────
    function populateYears(years) {
        const sel = $('#filter-year').empty();
        years.forEach(y => sel.append(`<option value="${y}" ${y == new Date().getFullYear() ? 'selected' : ''}>${y}</option>`));
    }

    // ── Render summary cards ──────────────────────────────────────
    function renderSummary(s) {
        $('#stat-total').text(s.total);
        $('#stat-pending').text(s.pending);
        $('#stat-ongoing').text(s.ongoing);
        $('#stat-resolved').text(s.resolved);
        $('#stat-cancelled').text(s.cancelled);
        $('#stat-rate').text(s.resolution_rate + '%');
    }

    // ── Render bar chart (monthly trend) ─────────────────────────
    function renderBarChart(monthly) {
        const labels   = monthly.map(m => m.month_label);
        const totals   = monthly.map(m => m.total);
        const resolved = monthly.map(m => m.resolved);

        if (barChart) barChart.destroy();

        barChart = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total',
                        data: totals,
                        backgroundColor: 'rgba(26,107,47,0.15)',
                        borderColor: '#1a6b2f',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    },
                    {
                        label: 'Resolved',
                        data: resolved,
                        backgroundColor: 'rgba(33,150,243,0.15)',
                        borderColor: '#2196f3',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 11 }, usePointStyle: true } },
                    tooltip: { backgroundColor: '#212529', cornerRadius: 8, padding: 10 }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f0f0f0' } },
                    x: { ticks: { font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }

    // ── Render doughnut (by status) ───────────────────────────────
    function renderDoughnutChart(byStatus) {
        const labels = byStatus.map(s => s.label);
        const data   = byStatus.map(s => s.value);
        const colors = ['#ffc107', '#1a6b2f', '#2196f3', '#e91e63'];

        if (doughnutChart) doughnutChart.destroy();

        doughnutChart = new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12, usePointStyle: true } },
                    tooltip: { backgroundColor: '#212529', cornerRadius: 8, padding: 10 }
                }
            }
        });
    }

    // ── Render horizontal bar (by type) ───────────────────────────
    function renderTypeChart(byType) {
        const labels = byType.map(t => t.complaint_type);
        const data   = byType.map(t => parseInt(t.total));

        if (typeChart) typeChart.destroy();

        typeChart = new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Complaints',
                    data,
                    backgroundColor: COLORS.slice(0, data.length),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#212529', cornerRadius: 8, padding: 10 }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f0f0f0' } },
                    y: { ticks: { font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }

    // ── Render crew performance table ─────────────────────────────
    function renderCrewTable(crewPerf) {
        const tbody = $('#crew-tbody').empty();

        if (crewPerf.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted py-3">No crew data available.</td></tr>');
            return;
        }

        const maxResolved = Math.max(...crewPerf.map(c => parseInt(c.resolved)), 1);

        crewPerf.forEach(function (c, i) {
            const pct      = Math.round((parseInt(c.resolved) / maxResolved) * 100);
            const initial  = c.full_name.charAt(0).toUpperCase();
            const rank     = i + 1;
            const rankColor = rank === 1 ? '#f59e0b' : rank === 2 ? '#9ca3af' : rank === 3 ? '#b45309' : '#dee2e6';

            tbody.append(`
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:26px;height:26px;border-radius:50%;
                                background:${rankColor};color:#fff;font-size:0.72rem;
                                font-weight:700;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;">${rank}</div>
                            <div style="width:34px;height:34px;background:#1a6b2f;color:#fff;
                                border-radius:50%;display:flex;align-items:center;
                                justify-content:center;font-size:0.82rem;font-weight:700;
                                flex-shrink:0;">${initial}</div>
                            <span style="font-size:0.88rem;font-weight:600;">${c.full_name}</span>
                        </div>
                    </td>
                    <td class="text-center" style="font-weight:600;">${c.total_assigned}</td>
                    <td class="text-center" style="font-weight:700;color:#1a6b2f;">${c.resolved}</td>
                    <td style="min-width:120px;">
                        <div style="background:#e9ecef;border-radius:20px;height:8px;overflow:hidden;">
                            <div style="width:${pct}%;background:#1a6b2f;height:100%;
                                border-radius:20px;transition:width 0.6s;"></div>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // ── Render recent resolved table ──────────────────────────────
    function renderRecentTable(recent) {
        const tbody = $('#recent-tbody').empty();

        if (recent.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">No resolved complaints yet.</td></tr>');
            return;
        }

        recent.forEach(function (c) {
            tbody.append(`
                <tr>
                    <td style="font-weight:600;color:#1a6b2f;font-size:0.85rem;">${c.ticket_no}</td>
                    <td style="font-size:0.85rem;">${c.complaint_type}</td>
                    <td style="font-size:0.85rem;">${c.consumer_name}</td>
                    <td style="font-size:0.85rem;">${c.crew_name ?? '—'}</td>
                    <td style="font-size:0.82rem;">${formatDate(c.created_at)}</td>
                </tr>
            `);
        });
    }

    // ── Main load ─────────────────────────────────────────────────
    function loadReports() {
        const year  = $('#filter-year').val()  || new Date().getFullYear();
        const month = $('#filter-month').val() || 0;

        $('#btn-refresh').find('i').addClass('spin');

        $.ajax({
            url: '../php/admin/reports.php',
            type: 'GET',
            data: { action: 'overview', year, month },
            dataType: 'json',
            success: function (res) {
                if (!res.success) { alert(res.message); return; }

                populateYears(res.years);
                renderSummary(res.summary);
                renderBarChart(res.monthly);
                renderDoughnutChart(res.byStatus);
                renderTypeChart(res.byType);
                renderCrewTable(res.crewPerf);
                renderRecentTable(res.recent);
            },
            error: function () { alert('Failed to load reports.'); },
            complete: function () {
                setTimeout(() => $('#btn-refresh i').removeClass('spin'), 600);
            }
        });
    }

    // ── Print ─────────────────────────────────────────────────────
    $('#btn-print').on('click', function () { window.print(); });

    // ── Filters ──────────────────────────────────────────────────
    $('#filter-year, #filter-month').on('change', loadReports);
    $('#btn-refresh').on('click', loadReports);

    // ── Init ─────────────────────────────────────────────────────
    loadReports();
});