<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'crew') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$crewId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

try {

    // ── LIST all assignments ──────────────────────────────────────
    if ($action === 'list') {

        $filter = $_GET['filter'] ?? 'all';

        $sql = "
            SELECT
                a.id            AS assignment_id,
                a.status        AS assignment_status,
                a.eta_minutes,
                a.assigned_at,
                a.completed_at,
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
            WHERE a.crew_id = ?
        ";

        $params = [$crewId];

        if ($filter === 'active') {
            $sql .= " AND a.status = 'active'";
        } elseif ($filter === 'completed') {
            $sql .= " AND a.status = 'completed'";
        }

        $sql .= " ORDER BY a.assigned_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $assignments = $stmt->fetchAll();

        echo json_encode(['success' => true, 'assignments' => $assignments]);
    }

    // ── UPDATE complaint status ───────────────────────────────────
    elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $complaint_id = intval($_POST['complaint_id'] ?? 0);
        $new_status   = $_POST['status'] ?? '';

        $allowed = ['ongoing', 'resolved'];
        if (!in_array($new_status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit();
        }

        // Verify this complaint is assigned to this crew
        $check = $pdo->prepare("
            SELECT a.id FROM assignments a
            WHERE a.crew_id = ? AND a.complaint_id = ? AND a.status = 'active'
        ");
        $check->execute([$crewId, $complaint_id]);

        if (!$check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Assignment not found.']);
            exit();
        }

        // Update complaint status
        $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?")
            ->execute([$new_status, $complaint_id]);

        // If resolved — close the assignment
        if ($new_status === 'resolved') {
            $pdo->prepare("
                UPDATE assignments
                SET status = 'completed', completed_at = NOW()
                WHERE crew_id = ? AND complaint_id = ? AND status = 'active'
            ")->execute([$crewId, $complaint_id]);
        }

        // Get ticket and consumer for notification
        $info = $pdo->prepare("SELECT ticket_no, consumer_id FROM complaints WHERE id = ?");
        $info->execute([$complaint_id]);
        $row = $info->fetch();

        if ($row) {
            $crewName = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $crewName->execute([$crewId]);
            $crew = $crewName->fetch();

            $title   = $new_status === 'resolved' ? 'Complaint Resolved' : 'Crew Has Arrived';
            $message = $new_status === 'resolved'
                ? "Your complaint ({$row['ticket_no']}) has been resolved by {$crew['full_name']}. Please rate your experience."
                : "Your maintenance crew ({$crew['full_name']}) has arrived and is working on your complaint ({$row['ticket_no']}).";

            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")
                ->execute([$row['consumer_id'], $title, $message]);
        }

        echo json_encode([
            'success' => true,
            'message' => $new_status === 'resolved'
                ? 'Job marked as resolved. Great work!'
                : 'Status updated. Consumer has been notified.',
        ]);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}