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

    // ── 1. Rating summary ─────────────────────────────────────────
    $summary = $pdo->prepare("
        SELECT
            COUNT(f.id)        AS total_ratings,
            ROUND(AVG(f.rating), 1) AS average_rating,
            SUM(f.rating = 5)  AS five_star,
            SUM(f.rating = 4)  AS four_star,
            SUM(f.rating = 3)  AS three_star,
            SUM(f.rating = 2)  AS two_star,
            SUM(f.rating = 1)  AS one_star
        FROM feedback f
        JOIN complaints c ON f.complaint_id = c.id
        JOIN assignments a ON c.id = a.complaint_id AND a.crew_id = ?
        WHERE f.consumer_id = c.consumer_id
    ");
    $summary->execute([$crewId]);
    $stats = $summary->fetch();

    // ── 2. All feedback list ──────────────────────────────────────
    $feedbackStmt = $pdo->prepare("
        SELECT
            f.id,
            f.rating,
            f.comment,
            f.created_at,
            c.ticket_no,
            c.complaint_type,
            u.full_name AS consumer_name
        FROM feedback f
        JOIN complaints c  ON f.complaint_id = c.id
        JOIN users u       ON f.consumer_id  = u.id
        JOIN assignments a ON c.id = a.complaint_id AND a.crew_id = ?
        WHERE f.consumer_id = c.consumer_id
        ORDER BY f.created_at DESC
    ");
    $feedbackStmt->execute([$crewId]);
    $feedback = $feedbackStmt->fetchAll();

    echo json_encode([
        'success'  => true,
        'stats'    => $stats,
        'feedback' => $feedback,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}