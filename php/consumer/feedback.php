<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'load';

try {

    // ── LOAD: Get complaint details for feedback page ─────────────
    if ($action === 'load') {

        $complaint_id = intval($_GET['complaint_id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                c.id,
                c.ticket_no,
                c.complaint_type,
                c.description,
                c.status,
                c.created_at,
                cr.full_name  AS crew_name,
                cr.id         AS crew_id,
                fb.id         AS feedback_id,
                fb.rating     AS existing_rating,
                fb.comment    AS existing_comment
            FROM complaints c
            LEFT JOIN assignments a  ON c.id = a.complaint_id
            LEFT JOIN users cr       ON a.crew_id = cr.id
            LEFT JOIN feedback fb    ON c.id = fb.complaint_id AND fb.consumer_id = ?
            WHERE c.id = ? AND c.consumer_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $complaint_id, $userId]);
        $complaint = $stmt->fetch();

        if (!$complaint) {
            echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
            exit();
        }

        if ($complaint['status'] !== 'resolved') {
            echo json_encode(['success' => false, 'message' => 'You can only rate resolved complaints.']);
            exit();
        }

        echo json_encode(['success' => true, 'complaint' => $complaint]);
    }

    // ── SUBMIT feedback ───────────────────────────────────────────
    elseif ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $complaint_id = intval($_POST['complaint_id'] ?? 0);
        $rating       = intval($_POST['rating']       ?? 0);
        $comment      = trim($_POST['comment']        ?? '');

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Please select a rating from 1 to 5 stars.']);
            exit();
        }

        // Check complaint belongs to consumer and is resolved
        $check = $pdo->prepare("
            SELECT id FROM complaints
            WHERE id = ? AND consumer_id = ? AND status = 'resolved'
        ");
        $check->execute([$complaint_id, $userId]);

        if (!$check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid complaint or not yet resolved.']);
            exit();
        }

        // Check already rated
        $existing = $pdo->prepare("SELECT id FROM feedback WHERE complaint_id = ? AND consumer_id = ?");
        $existing->execute([$complaint_id, $userId]);

        if ($existing->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already submitted feedback for this complaint.']);
            exit();
        }

        // Insert feedback
        $pdo->prepare("
            INSERT INTO feedback (complaint_id, consumer_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ")->execute([$complaint_id, $userId, $rating, $comment ?: null]);

        // Notify admin
        $info = $pdo->prepare("SELECT ticket_no FROM complaints WHERE id = ?");
        $info->execute([$complaint_id]);
        $row = $info->fetch();

        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        $notif  = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");

        foreach ($admins->fetchAll() as $admin) {
            $notif->execute([
                $admin['id'],
                'New Feedback Received',
                "{$_SESSION['full_name']} gave a {$rating}-star rating for complaint {$row['ticket_no']}.",
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}