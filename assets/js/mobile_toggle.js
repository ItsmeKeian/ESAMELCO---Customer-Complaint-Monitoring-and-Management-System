 // ── Sidebar toggle (mobile) ──────────────────────────────────
 $('#sidebarToggle').on('click', function () {
    $('.sidebar').addClass('open');
    $('#sidebarOverlay').addClass('open');
});

$('#sidebarOverlay').on('click', function () {
    $('.sidebar').removeClass('open');
    $('#sidebarOverlay').removeClass('open');
});