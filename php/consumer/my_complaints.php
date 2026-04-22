<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

try {

    // ── LIST all complaints ──────────────────────────────────────
    if ($action === 'list') {

        $filter = $_GET['filter'] ?? 'all';

        $sql = "
            SELECT
                c.id,
                c.ticket_no,
                c.complaint_type,
                c.description,
                c.photo,
                c.latitude,
                c.longitude,
                c.status,
                c.created_at,
                a.eta_minutes,
                a.assigned_at,
                cr.full_name   AS crew_name,
                cr.phone       AS crew_phone,
                cl.latitude    AS crew_lat,
                cl.longitude   AS crew_lng,
                cl.updated_at  AS crew_last_seen,
                fb.id          AS feedback_id,
                fb.rating      AS feedback_rating
            FROM complaints c
            LEFT JOIN assignments a   ON c.id = a.complaint_id AND a.status = 'active'
            LEFT JOIN users cr        ON a.crew_id = cr.id
            LEFT JOIN crew_locations cl ON cr.id = cl.crew_id
            LEFT JOIN feedback fb     ON c.id = fb.complaint_id AND fb.consumer_id = ?
            WHERE c.consumer_id = ?
        ";

        $params = [$userId, $userId];

        if ($filter !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $filter;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $complaints = $stmt->fetchAll();

        echo json_encode(['success' => true, 'complaints' => $complaints]);
    }

    // ── GET single complaint detail ───────────────────────────────
    elseif ($action === 'view') {

        $id = intval($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                c.*,
                a.eta_minutes,
                a.assigned_at,
                cr.full_name   AS crew_name,
                cr.phone       AS crew_phone,
                cl.latitude    AS crew_lat,
                cl.longitude   AS crew_lng,
                cl.updated_at  AS crew_last_seen,
                fb.id          AS feedback_id,
                fb.rating      AS feedback_rating,
                fb.comment     AS feedback_comment
            FROM complaints c
            LEFT JOIN assignments a     ON c.id = a.complaint_id AND a.status = 'active'
            LEFT JOIN users cr          ON a.crew_id = cr.id
            LEFT JOIN crew_locations cl ON cr.id = cl.crew_id
            LEFT JOIN feedback fb       ON c.id = fb.complaint_id AND fb.consumer_id = ?
            WHERE c.id = ? AND c.consumer_id = ?
        ");
        $stmt->execute([$userId, $id, $userId]);
        $complaint = $stmt->fetch();

        if (!$complaint) {
            echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
            exit();
        }

        echo json_encode(['success' => true, 'complaint' => $complaint]);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}