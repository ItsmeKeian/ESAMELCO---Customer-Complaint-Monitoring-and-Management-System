<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Already logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$full_name       = trim($_POST['full_name']       ?? '');
$email           = trim($_POST['email']           ?? '');
$phone           = trim($_POST['phone']           ?? '');
$address         = trim($_POST['address']         ?? '');
$password        = $_POST['password']             ?? '';
$confirm_password = $_POST['confirm_password']   ?? '';

// ── Validation ───────────────────────────────────────────────────
if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit();
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

try {
    // Check if email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered. Please login instead.']);
        exit();
    }

    // Hash password and insert
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, phone, address, role, status)
        VALUES (?, ?, ?, ?, ?, 'consumer', 'active')
    ");
    $stmt->execute([$full_name, $email, $hash, $phone ?: null, $address ?: null]);

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully! You can now log in.',
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}