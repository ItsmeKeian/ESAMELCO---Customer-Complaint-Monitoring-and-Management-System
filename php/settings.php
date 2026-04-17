<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'load';
$userId = $_SESSION['user_id'];

try {

    // ── LOAD: Get profile + system settings ──────────────────────
    if ($action === 'load') {

        // Admin profile
        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, address FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        // System settings (stored as key-value rows)
        $sysStmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        $rows     = $sysStmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        echo json_encode([
            'success'  => true,
            'profile'  => $profile,
            'settings' => $settings,
        ]);
    }

    // ── UPDATE PROFILE ───────────────────────────────────────────
    elseif ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $phone     = trim($_POST['phone']     ?? '');
        $address   = trim($_POST['address']   ?? '');

        if (!$full_name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Full name and email are required.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit();
        }

        // Check email uniqueness (exclude self)
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already used by another account.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $address, $userId]);

        // Update session name
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email']     = $email;

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    // ── CHANGE PASSWORD ──────────────────────────────────────────
    elseif ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $current  = $_POST['current_password']  ?? '';
        $new      = $_POST['new_password']      ?? '';
        $confirm  = $_POST['confirm_password']  ?? '';

        if (!$current || !$new || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
            exit();
        }

        if (strlen($new) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
            exit();
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
            exit();
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!password_verify($current, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit();
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $userId]);

        echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
    }

    // ── UPDATE SYSTEM SETTINGS ───────────────────────────────────
    elseif ($action === 'update_system' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $allowed = [
            'site_name',
            'site_tagline',
            'contact_email',
            'contact_phone',
            'office_address',
            'dispatch_speed_kmh',
        ];

        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$key, trim($_POST[$key])]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'System settings saved successfully.']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}