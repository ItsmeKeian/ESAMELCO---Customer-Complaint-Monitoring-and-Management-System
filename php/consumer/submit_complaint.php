<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

// ── Get form fields ──────────────────────────────────────────────
$complaint_type = trim($_POST['complaint_type'] ?? '');
$description    = trim($_POST['description']    ?? '');
$latitude       = $_POST['latitude']            ?? null;
$longitude      = $_POST['longitude']           ?? null;

// ── Validation ───────────────────────────────────────────────────
if (empty($complaint_type) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Complaint type and description are required.']);
    exit();
}

if (strlen($description) < 10) {
    echo json_encode(['success' => false, 'message' => 'Description must be at least 10 characters.']);
    exit();
}

// ── Photo upload ─────────────────────────────────────────────────
$photo_path = null;

if (!empty($_FILES['photo']['name'])) {
    $file      = $_FILES['photo'];
    $allowed   = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $maxSize   = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WEBP images are allowed.']);
        exit();
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Photo must be less than 5MB.']);
        exit();
    }

    // Create upload directory if not exists
    $uploadDir = '../../uploads/complaints/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename  = 'complaint_' . $userId . '_' . time() . '.' . $ext;
    $dest      = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload photo. Please try again.']);
        exit();
    }

    $photo_path = 'complaints/' . $filename;
}

// ── Generate ticket number ────────────────────────────────────────
// Format: TKT-YYYY-NNNNN (e.g. TKT-2025-00006)
try {
    $lastTicket = $pdo->query("
        SELECT ticket_no FROM complaints
        ORDER BY id DESC LIMIT 1
    ")->fetchColumn();

    if ($lastTicket) {
        $lastNum    = intval(substr($lastTicket, -5));
        $nextNum    = $lastNum + 1;
    } else {
        $nextNum = 1;
    }

    $ticket_no = 'TKT-' . date('Y') . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

    // ── Insert complaint ─────────────────────────────────────────
    $stmt = $pdo->prepare("
        INSERT INTO complaints
            (ticket_no, consumer_id, complaint_type, description, photo, latitude, longitude, status)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $ticket_no,
        $userId,
        $complaint_type,
        $description,
        $photo_path,
        !empty($latitude)  ? $latitude  : null,
        !empty($longitude) ? $longitude : null,
    ]);

    // ── Notify admin (insert notification for all admins) ────────
    $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message)
        VALUES (?, ?, ?)
    ");

    foreach ($admins->fetchAll() as $admin) {
        $notifStmt->execute([
            $admin['id'],
            'New Complaint Filed',
            "A new complaint ({$ticket_no}) has been filed by {$_SESSION['full_name']}. Type: {$complaint_type}.",
        ]);
    }

    echo json_encode([
        'success'   => true,
        'message'   => 'Complaint submitted successfully!',
        'ticket_no' => $ticket_no,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}