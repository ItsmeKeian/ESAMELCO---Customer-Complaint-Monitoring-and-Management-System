<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

// Auth guard - only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

try {
    // ── 1. Overview Stats ────────────────────────────────────────
    $stats = [];

    $stats['total']    = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
    $stats['pending']  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
    $stats['ongoing']  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'ongoing'")->fetchColumn();
    $stats['resolved'] = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();

    // ── 2. Complaints Per Month (last 6 months) for Line Chart ──
    $monthlyStmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') AS month,
               COUNT(*) AS total
        FROM complaints
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY MIN(created_at) ASC
    ");
    $monthly = $monthlyStmt->fetchAll();

    // ── 3. Complaints by Type for Doughnut Chart ─────────────────
    $typeStmt = $pdo->query("
        SELECT complaint_type, COUNT(*) AS total
        FROM complaints
        GROUP BY complaint_type
        ORDER BY total DESC
        LIMIT 5
    ");
    $byType = $typeStmt->fetchAll();

    // ── 4. Recent Complaints Table (latest 10) ───────────────────
    $recentStmt = $pdo->query("
        SELECT
            c.id,
            c.ticket_no,
            c.complaint_type,
            c.description,
            c.status,
            c.created_at,
            u.full_name  AS consumer_name,
            cr.full_name AS crew_name
        FROM complaints c
        JOIN users u  ON c.consumer_id = u.id
        LEFT JOIN assignments a  ON c.id = a.complaint_id AND a.status = 'active'
        LEFT JOIN users cr ON a.crew_id = cr.id
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $recentComplaints = $recentStmt->fetchAll();

    // ── 5. Available Crew for Map Markers ────────────────────────
    $crewStmt = $pdo->query("
        SELECT
            u.id,
            u.full_name,
            cl.latitude,
            cl.longitude,
            cl.updated_at AS last_seen,
            CASE
                WHEN EXISTS (
                    SELECT 1 FROM assignments a
                    WHERE a.crew_id = u.id AND a.status = 'active'
                ) THEN 'busy'
                ELSE 'available'
            END AS availability
        FROM users u
        JOIN crew_locations cl ON u.id = cl.crew_id
        WHERE u.role = 'crew' AND u.status = 'active'
    ");
    $crewLocations = $crewStmt->fetchAll();

    echo json_encode([
        'success'          => true,
        'stats'            => $stats,
        'monthly'          => $monthly,
        'byType'           => $byType,
        'recentComplaints' => $recentComplaints,
        'crewLocations'    => $crewLocations,
        'adminName'        => $_SESSION['full_name'],
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load dashboard data.']);
}