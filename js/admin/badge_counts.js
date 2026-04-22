// Fetch and update pending complaints badge on all admin pages
function updatePendingBadge() {
    $.ajax({
        url: '/esamelco/php/get_pending_count.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            $('#nav-badge-complaints').text(res.count);
        }
    });
}

// Run on page load and every 15 seconds
$(document).ready(function () {
    updatePendingBadge();
    setInterval(updatePendingBadge, 15000);
});