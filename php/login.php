<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit();
}

try {
    // Fetch user by email
    $stmt = $pdo->prepare("SELECT id, full_name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Incorrect email or password.']);
        exit();
    }

    if ($user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact ESAMELCO.']);
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect email or password.']);
        exit();
    }

    // Set session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];

    // Determine redirect URL based on role
    $redirect = match($user['role']) {
        'admin'    => 'admin/dashboard.php',
        'crew'     => 'crew/dashboard.php',
        default    => 'consumer/dashboard.php',
    };

    echo json_encode(['success' => true, 'redirect' => $redirect]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}