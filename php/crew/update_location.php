<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'crew') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$crewId    = $_SESSION['user_id'];
$latitude  = $_POST['latitude']  ?? null;
$longitude = $_POST['longitude'] ?? null;

if (!$latitude || !$longitude) {
    echo json_encode(['success' => false, 'message' => 'Location data missing.']);
    exit();
}

try {
    // Insert or update crew location
    $stmt = $pdo->prepare("
        INSERT INTO crew_locations (crew_id, latitude, longitude)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            latitude   = VALUES(latitude),
            longitude  = VALUES(longitude),
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$crewId, $latitude, $longitude]);

    echo json_encode(['success' => true, 'message' => 'Location updated.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}