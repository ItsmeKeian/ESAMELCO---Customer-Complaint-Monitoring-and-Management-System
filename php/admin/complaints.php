<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'list';

try {

    // ── ACTION: Get all complaints ───────────────────────────────
    if ($action === 'list') {

        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $sql = "
            SELECT
                c.id,
                c.ticket_no,
                c.complaint_type,
                c.description,
                c.status,
                c.created_at,
                u.full_name  AS consumer_name,
                u.phone      AS consumer_phone,
                cr.full_name AS crew_name
            FROM complaints c
            JOIN users u ON c.consumer_id = u.id
            LEFT JOIN assignments a ON c.id = a.complaint_id 
            AND a.id = (SELECT MAX(id) FROM assignments WHERE complaint_id = c.id)
            LEFT JOIN users cr ON a.crew_id = cr.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($status)) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (c.ticket_no LIKE ? OR u.full_name LIKE ? OR c.complaint_type LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $complaints = $stmt->fetchAll();

        echo json_encode(['success' => true, 'complaints' => $complaints]);
    }

    // ── ACTION: Get single complaint details ─────────────────────
    elseif ($action === 'view') {

        $id = intval($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                c.*,
                u.full_name  AS consumer_name,
                u.phone      AS consumer_phone,
                u.email      AS consumer_email,
                cr.full_name AS crew_name,
                a.eta_minutes,
                a.assigned_at
            FROM complaints c
            JOIN users u ON c.consumer_id = u.id
            LEFT JOIN assignments a ON c.id = a.complaint_id 
            AND a.id = (SELECT MAX(id) FROM assignments WHERE complaint_id = c.id)
            LEFT JOIN users cr ON a.crew_id = cr.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $complaint = $stmt->fetch();

        if (!$complaint) {
            echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
            exit();
        }

        echo json_encode(['success' => true, 'complaint' => $complaint]);
    }

    // ── ACTION: Update complaint status ─────────────────────────
    elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id     = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $allowed = ['pending', 'ongoing', 'resolved', 'cancelled'];
        if (!in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // If resolved/cancelled, mark assignment as completed
        if (in_array($status, ['resolved', 'cancelled'])) {
            $pdo->prepare("
                UPDATE assignments SET status = 'completed', completed_at = NOW()
                WHERE complaint_id = ? AND status = 'active'
            ")->execute([$id]);
        }

        // Notify the consumer
        $comp = $pdo->prepare("SELECT consumer_id, ticket_no FROM complaints WHERE id = ?");
        $comp->execute([$id]);
        $row = $comp->fetch();

        if ($row) {
            $title   = "Complaint " . ucfirst($status);
            $message = "Your complaint ({$row['ticket_no']}) has been updated to: " . strtoupper($status) . ".";
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")
                ->execute([$row['consumer_id'], $title, $message]);
        }

        echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
    }

    // ── ACTION: Delete complaint ─────────────────────────────────
    elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = intval($_POST['id'] ?? 0);

        // Delete related records first (foreign key order)
        $pdo->prepare("DELETE FROM notifications WHERE user_id IN (SELECT consumer_id FROM complaints WHERE id = ?)")->execute([$id]);
        $pdo->prepare("DELETE FROM feedback    WHERE complaint_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM assignments WHERE complaint_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM complaints  WHERE id = ?")->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Complaint deleted.']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}