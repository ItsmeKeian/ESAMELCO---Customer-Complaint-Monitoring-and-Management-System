<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

try {

    // ── 1. Overall summary ────────────────────────────────────────
    $summary = $pdo->query("
        SELECT
            COUNT(*)              AS total_feedback,
            ROUND(AVG(rating), 1) AS overall_avg,
            SUM(rating = 5)       AS five_star,
            SUM(rating = 4)       AS four_star,
            SUM(rating = 3)       AS three_star,
            SUM(rating = 2)       AS two_star,
            SUM(rating = 1)       AS one_star
        FROM feedback
    ")->fetch();

    // ── 2. Crew performance ranking ───────────────────────────────
    $crewRanking = $pdo->query("
        SELECT
            u.id,
            u.full_name,
            COUNT(f.id)             AS total_ratings,
            ROUND(AVG(f.rating), 1) AS avg_rating
        FROM users u
        JOIN assignments a  ON u.id = a.crew_id
        JOIN complaints c   ON a.complaint_id = c.id
        JOIN feedback f     ON c.id = f.complaint_id
        WHERE u.role = 'crew'
        GROUP BY u.id, u.full_name
        ORDER BY avg_rating DESC
    ")->fetchAll();

    // ── 3. All feedback list ──────────────────────────────────────
    $feedbackList = $pdo->query("
        SELECT
            f.id,
            f.rating,
            f.comment,
            f.created_at,
            c.ticket_no,
            c.complaint_type,
            consumer.full_name AS consumer_name,
            crew.full_name     AS crew_name
        FROM feedback f
        JOIN complaints c    ON f.complaint_id = c.id
        JOIN users consumer  ON f.consumer_id  = consumer.id
        JOIN assignments a   ON c.id = a.complaint_id
        JOIN users crew      ON a.crew_id = crew.id
        GROUP BY f.id
        ORDER BY f.created_at DESC
    ")->fetchAll();

    echo json_encode([
        'success'     => true,
        'summary'     => $summary,
        'crewRanking' => $crewRanking,
        'feedback'    => $feedbackList,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}