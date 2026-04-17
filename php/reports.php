<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'overview';

try {

    // ── OVERVIEW: Summary cards + all chart data ─────────────────
    if ($action === 'overview') {

        $year  = intval($_GET['year']  ?? date('Y'));
        $month = intval($_GET['month'] ?? 0); // 0 = all months

        // Build date filter
        $dateFilter = " AND YEAR(c.created_at) = $year";
        if ($month > 0) {
            $dateFilter .= " AND MONTH(c.created_at) = $month";
        }

        // ── 1. Summary cards ─────────────────────────────────────
        $summary = $pdo->query("
            SELECT
                COUNT(*)                          AS total,
                SUM(status = 'pending')           AS pending,
                SUM(status = 'ongoing')           AS ongoing,
                SUM(status = 'resolved')          AS resolved,
                SUM(status = 'cancelled')         AS cancelled
            FROM complaints c
            WHERE 1=1 $dateFilter
        ")->fetch();

        // Resolution rate
        $summary['resolution_rate'] = $summary['total'] > 0
            ? round(($summary['resolved'] / $summary['total']) * 100, 1)
            : 0;

        // ── 2. Monthly trend (12 months) ─────────────────────────
        $monthly = $pdo->query("
            SELECT
                DATE_FORMAT(created_at, '%b') AS month_label,
                MONTH(created_at)             AS month_num,
                COUNT(*)                      AS total,
                SUM(status = 'resolved')      AS resolved
            FROM complaints
            WHERE YEAR(created_at) = $year
            GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
            ORDER BY MONTH(created_at) ASC
        ")->fetchAll();

        // Fill all 12 months (so chart has no gaps)
        $months_full = [];
        $month_names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        foreach ($month_names as $i => $name) {
            $found = array_filter($monthly, fn($m) => intval($m['month_num']) === ($i + 1));
            $found = array_values($found);
            $months_full[] = [
                'month_label' => $name,
                'total'       => $found ? intval($found[0]['total'])    : 0,
                'resolved'    => $found ? intval($found[0]['resolved']) : 0,
            ];
        }

        // ── 3. Complaints by type ─────────────────────────────────
        $byType = $pdo->query("
            SELECT complaint_type, COUNT(*) AS total
            FROM complaints c
            WHERE 1=1 $dateFilter
            GROUP BY complaint_type
            ORDER BY total DESC
            LIMIT 6
        ")->fetchAll();

        // ── 4. Complaints by status (pie) ────────────────────────
        $byStatus = [
            ['label' => 'Pending',   'value' => intval($summary['pending'])],
            ['label' => 'Ongoing',   'value' => intval($summary['ongoing'])],
            ['label' => 'Resolved',  'value' => intval($summary['resolved'])],
            ['label' => 'Cancelled', 'value' => intval($summary['cancelled'])],
        ];

        // ── 5. Crew performance ───────────────────────────────────
        $crewPerf = $pdo->query("
            SELECT
                u.full_name,
                COUNT(a.id)                               AS total_assigned,
                SUM(c.status = 'resolved')                AS resolved,
                SUM(a.status = 'active')                  AS active_now
            FROM users u
            JOIN assignments a ON u.id = a.crew_id
            JOIN complaints  c ON a.complaint_id = c.id
            WHERE u.role = 'crew'
            GROUP BY u.id, u.full_name
            ORDER BY resolved DESC
            LIMIT 8
        ")->fetchAll();

        // ── 6. Recent resolved complaints ────────────────────────
        $recent = $pdo->query("
            SELECT
                c.ticket_no,
                c.complaint_type,
                c.status,
                c.created_at,
                u.full_name  AS consumer_name,
                cr.full_name AS crew_name
            FROM complaints c
            JOIN users u  ON c.consumer_id = u.id
            LEFT JOIN assignments a  ON c.id = a.complaint_id
            LEFT JOIN users cr ON a.crew_id = cr.id
            WHERE c.status = 'resolved'
            ORDER BY c.updated_at DESC
            LIMIT 8
        ")->fetchAll();

        // ── 7. Available years for filter ─────────────────────────
        $years = $pdo->query("
            SELECT DISTINCT YEAR(created_at) AS yr
            FROM complaints
            ORDER BY yr DESC
        ")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($years)) $years = [date('Y')];

        echo json_encode([
            'success'      => true,
            'summary'      => $summary,
            'monthly'      => $months_full,
            'byType'       => $byType,
            'byStatus'     => $byStatus,
            'crewPerf'     => $crewPerf,
            'recent'       => $recent,
            'years'        => $years,
        ]);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}