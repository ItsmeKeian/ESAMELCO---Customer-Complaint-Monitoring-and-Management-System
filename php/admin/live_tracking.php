<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

try {

    // ── All crew with their locations + assignment info ───────────
    $stmt = $pdo->query("
        SELECT
            u.id,
            u.full_name,
            u.phone,
            cl.latitude,
            cl.longitude,
            cl.updated_at                       AS last_seen,
            a.id                                AS assignment_id,
            a.eta_minutes,
            a.assigned_at,
            c.id                                AS complaint_id,
            c.ticket_no,
            c.complaint_type,
            c.description,
            c.latitude                          AS complaint_lat,
            c.longitude                         AS complaint_lng,
            cu.full_name                        AS consumer_name,
            cu.phone                            AS consumer_phone,
            CASE
                WHEN a.id IS NOT NULL THEN 'busy'
                ELSE 'available'
            END                                 AS availability
        FROM users u
        JOIN crew_locations cl ON u.id = cl.crew_id
        LEFT JOIN assignments a  ON u.id = a.crew_id AND a.status = 'active'
        LEFT JOIN complaints c   ON a.complaint_id = c.id
        LEFT JOIN users cu       ON c.consumer_id = cu.id
        WHERE u.role   = 'crew'
          AND u.status = 'active'
        ORDER BY availability DESC, u.full_name ASC
    ");

    $crew = $stmt->fetchAll();

    // ── Summary counts ───────────────────────────────────────────
    $total     = count($crew);
    $busy      = count(array_filter($crew, fn($c) => $c['availability'] === 'busy'));
    $available = $total - $busy;

    echo json_encode([
        'success'   => true,
        'crew'      => $crew,
        'summary'   => [
            'total'     => $total,
            'busy'      => $busy,
            'available' => $available,
        ],
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}