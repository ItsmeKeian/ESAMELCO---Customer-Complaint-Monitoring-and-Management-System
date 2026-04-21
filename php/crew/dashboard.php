<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'crew') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$crewId = $_SESSION['user_id'];

try {

    // ── 1. Active assignment ──────────────────────────────────────
    $activeStmt = $pdo->prepare("
        SELECT
            a.id            AS assignment_id,
            a.eta_minutes,
            a.assigned_at,
            c.id            AS complaint_id,
            c.ticket_no,
            c.complaint_type,
            c.description,
            c.photo,
            c.latitude      AS complaint_lat,
            c.longitude     AS complaint_lng,
            c.status        AS complaint_status,
            c.created_at    AS filed_at,
            u.full_name     AS consumer_name,
            u.phone         AS consumer_phone,
            u.address       AS consumer_address
        FROM assignments a
        JOIN complaints c ON a.complaint_id = c.id
        JOIN users u      ON c.consumer_id  = u.id
        WHERE a.crew_id = ? AND a.status = 'active'
        LIMIT 1
    ");
    $activeStmt->execute([$crewId]);
    $activeJob = $activeStmt->fetch();

    // ── 2. Job stats (all time) ───────────────────────────────────
    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*)                           AS total,
            SUM(a.status = 'completed')        AS completed,
            SUM(a.status = 'active')           AS active,
            SUM(a.status = 'cancelled')        AS cancelled
        FROM assignments a
        WHERE a.crew_id = ?
    ");
    $statsStmt->execute([$crewId]);
    $stats = $statsStmt->fetch();

    // ── 3. Recent completed jobs (last 5) ─────────────────────────
    $recentStmt = $pdo->prepare("
        SELECT
            c.ticket_no,
            c.complaint_type,
            c.status,
            a.completed_at,
            u.full_name AS consumer_name
        FROM assignments a
        JOIN complaints c ON a.complaint_id = c.id
        JOIN users u      ON c.consumer_id  = u.id
        WHERE a.crew_id = ? AND a.status = 'completed'
        ORDER BY a.completed_at DESC
        LIMIT 5
    ");
    $recentStmt->execute([$crewId]);
    $recentJobs = $recentStmt->fetchAll();

    // ── 4. Unread notifications ───────────────────────────────────
    $notifStmt = $pdo->prepare("
        SELECT id, title, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 8
    ");
    $notifStmt->execute([$crewId]);
    $notifications = $notifStmt->fetchAll();
    $unreadCount   = count(array_filter($notifications, fn($n) => !$n['is_read']));

    // Mark as read
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
        ->execute([$crewId]);

    // ── 5. Current crew location ──────────────────────────────────
    $locStmt = $pdo->prepare("SELECT latitude, longitude, updated_at FROM crew_locations WHERE crew_id = ?");
    $locStmt->execute([$crewId]);
    $location = $locStmt->fetch();

    echo json_encode([
        'success'       => true,
        'crew_name'     => $_SESSION['full_name'],
        'active_job'    => $activeJob,
        'stats'         => $stats,
        'recent_jobs'   => $recentJobs,
        'notifications' => $notifications,
        'unread_count'  => $unreadCount,
        'location'      => $location,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}