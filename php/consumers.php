<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'list';

try {

    // ── LIST all consumers ───────────────────────────────────────
    if ($action === 'list') {

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $sql = "
            SELECT
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.address,
                u.status,
                u.created_at,
                COUNT(c.id)                              AS total_complaints,
                SUM(c.status = 'pending')                AS pending,
                SUM(c.status = 'ongoing')                AS ongoing,
                SUM(c.status = 'resolved')               AS resolved
            FROM users u
            LEFT JOIN complaints c ON u.id = c.consumer_id
            WHERE u.role = 'consumer'
        ";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " GROUP BY u.id ORDER BY u.full_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $consumers = $stmt->fetchAll();

        echo json_encode(['success' => true, 'consumers' => $consumers]);
    }

    // ── VIEW single consumer + complaint history ──────────────────
    elseif ($action === 'view') {

        $id = intval($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT id, full_name, email, phone, address, status, created_at
            FROM users WHERE id = ? AND role = 'consumer'
        ");
        $stmt->execute([$id]);
        $consumer = $stmt->fetch();

        if (!$consumer) {
            echo json_encode(['success' => false, 'message' => 'Consumer not found.']);
            exit();
        }

        // Last 5 complaints
        $cStmt = $pdo->prepare("
            SELECT ticket_no, complaint_type, status, created_at
            FROM complaints
            WHERE consumer_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $cStmt->execute([$id]);
        $complaints = $cStmt->fetchAll();

        echo json_encode([
            'success'    => true,
            'consumer'   => $consumer,
            'complaints' => $complaints,
        ]);
    }

    // ── TOGGLE status ────────────────────────────────────────────
    elseif ($action === 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = intval($_POST['id'] ?? 0);

        $current = $pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'consumer'");
        $current->execute([$id]);
        $row = $current->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Consumer not found.']);
            exit();
        }

        $newStatus = $row['status'] === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        echo json_encode([
            'success'    => true,
            'message'    => "Account status changed to {$newStatus}.",
            'new_status' => $newStatus,
        ]);
    }

    // ── DELETE consumer ──────────────────────────────────────────
    elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = intval($_POST['id'] ?? 0);

        // Check for active/ongoing complaints
        $active = $pdo->prepare("
            SELECT id FROM complaints
            WHERE consumer_id = ? AND status IN ('pending', 'ongoing')
        ");
        $active->execute([$id]);

        if ($active->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete consumer with pending or ongoing complaints. Resolve them first.',
            ]);
            exit();
        }

        // Delete related records
        $pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM feedback WHERE consumer_id = ?")->execute([$id]);

        // Get complaint IDs first then delete assignments
        $compIds = $pdo->prepare("SELECT id FROM complaints WHERE consumer_id = ?");
        $compIds->execute([$id]);
        foreach ($compIds->fetchAll() as $comp) {
            $pdo->prepare("DELETE FROM assignments WHERE complaint_id = ?")->execute([$comp['id']]);
        }

        $pdo->prepare("DELETE FROM complaints WHERE consumer_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'consumer'")->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Consumer account deleted.']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}