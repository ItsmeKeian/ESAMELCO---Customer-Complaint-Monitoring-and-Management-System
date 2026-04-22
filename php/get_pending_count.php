<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['count' => 0]);
    exit();
}

try {
    $count = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
    echo json_encode(['count' => intval($count)]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}