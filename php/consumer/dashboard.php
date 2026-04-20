<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$userId = $_SESSION['user_id'];

try {

    // ── 1. Complaint summary stats ───────────────────────────────
    $stats = $pdo->prepare("
        SELECT
            COUNT(*)                    AS total,
            SUM(status = 'pending')     AS pending,
            SUM(status = 'ongoing')     AS ongoing,
            SUM(status = 'resolved')    AS resolved
        FROM complaints
        WHERE consumer_id = ?
    ");
    $stats->execute([$userId]);
    $summary = $stats->fetch();

    // ── 2. Recent complaints (latest 5) ──────────────────────────
    $recentStmt = $pdo->prepare("
        SELECT
            c.id,
            c.ticket_no,
            c.complaint_type,
            c.description,
            c.status,
            c.created_at,
            a.eta_minutes,
            a.assigned_at,
            cr.full_name AS crew_name
        FROM complaints c
        LEFT JOIN assignments a  ON c.id = a.complaint_id AND a.status = 'active'
        LEFT JOIN users cr       ON a.crew_id = cr.id
        WHERE c.consumer_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $recentStmt->execute([$userId]);
    $recent = $recentStmt->fetchAll();

    // ── 3. Unread notifications ──────────────────────────────────
    $notifStmt = $pdo->prepare("
        SELECT id, title, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $notifStmt->execute([$userId]);
    $notifications = $notifStmt->fetchAll();

    $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

    // ── 4. Mark notifications as read ────────────────────────────
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
        ->execute([$userId]);

    echo json_encode([
        'success'       => true,
        'summary'       => $summary,
        'recent'        => $recent,
        'notifications' => $notifications,
        'unread_count'  => $unreadCount,
        'consumer_name' => $_SESSION['full_name'],
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}