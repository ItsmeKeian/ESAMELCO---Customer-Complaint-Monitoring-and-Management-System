<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'list';

try {

    // ── LIST all crew ────────────────────────────────────────────
    if ($action === 'list') {

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $sql    = "
            SELECT
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.address,
                u.status,
                u.created_at,
                CASE
                    WHEN a.id IS NOT NULL THEN 'busy'
                    ELSE 'available'
                END AS availability,
                a.complaint_id,
                c.ticket_no
            FROM users u
            LEFT JOIN assignments a ON u.id = a.crew_id AND a.status = 'active'
            LEFT JOIN complaints c  ON a.complaint_id = c.id
            WHERE u.role = 'crew'
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

        $sql .= " ORDER BY u.full_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $crew = $stmt->fetchAll();

        echo json_encode(['success' => true, 'crew' => $crew]);
    }

    // ── ADD new crew ─────────────────────────────────────────────
    elseif ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $phone     = trim($_POST['phone']     ?? '');
        $address   = trim($_POST['address']   ?? '');
        $password  = $_POST['password']       ?? '';

        if (!$full_name || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Full name, email, and password are required.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit();
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
            exit();
        }

        // Check email uniqueness
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            exit();
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, password, phone, address, role, status)
            VALUES (?, ?, ?, ?, ?, 'crew', 'active')
        ");
        $stmt->execute([$full_name, $email, $hash, $phone, $address]);

        echo json_encode(['success' => true, 'message' => 'Crew member added successfully.']);
    }

    // ── GET single crew for editing ──────────────────────────────
    elseif ($action === 'get') {

        $id   = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, address, status FROM users WHERE id = ? AND role = 'crew'");
        $stmt->execute([$id]);
        $crew = $stmt->fetch();

        if (!$crew) {
            echo json_encode(['success' => false, 'message' => 'Crew member not found.']);
            exit();
        }

        echo json_encode(['success' => true, 'crew' => $crew]);
    }

    // ── EDIT crew ────────────────────────────────────────────────
    elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id        = intval($_POST['id']        ?? 0);
        $full_name = trim($_POST['full_name']   ?? '');
        $email     = trim($_POST['email']       ?? '');
        $phone     = trim($_POST['phone']       ?? '');
        $address   = trim($_POST['address']     ?? '');
        $status    = $_POST['status']           ?? 'active';
        $password  = $_POST['password']         ?? '';

        if (!$full_name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Full name and email are required.']);
            exit();
        }

        // Check email uniqueness (exclude self)
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already used by another account.']);
            exit();
        }

        if (!empty($password)) {
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
                exit();
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, status=?, password=? WHERE id=? AND role='crew'");
            $stmt->execute([$full_name, $email, $phone, $address, $status, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, status=? WHERE id=? AND role='crew'");
            $stmt->execute([$full_name, $email, $phone, $address, $status, $id]);
        }

        echo json_encode(['success' => true, 'message' => 'Crew member updated successfully.']);
    }

    // ── TOGGLE status (active / inactive) ────────────────────────
    elseif ($action === 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = intval($_POST['id'] ?? 0);

        $current = $pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'crew'");
        $current->execute([$id]);
        $row = $current->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Crew not found.']);
            exit();
        }

        $newStatus = $row['status'] === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        echo json_encode(['success' => true, 'message' => "Status changed to {$newStatus}.", 'new_status' => $newStatus]);
    }

    // ── DELETE crew ──────────────────────────────────────────────
    elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = intval($_POST['id'] ?? 0);

        // Check if crew has active assignment
        $active = $pdo->prepare("SELECT id FROM assignments WHERE crew_id = ? AND status = 'active'");
        $active->execute([$id]);
        if ($active->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete crew with an active assignment. Complete or cancel the assignment first.']);
            exit();
        }

        $pdo->prepare("DELETE FROM crew_locations WHERE crew_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM assignments   WHERE crew_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM users         WHERE id = ? AND role = 'crew'")->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Crew member deleted.']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}