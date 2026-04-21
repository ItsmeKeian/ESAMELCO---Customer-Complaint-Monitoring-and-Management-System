<?php


session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'crew') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'consumer') {
        header("Location: ../consumer/dashboard.php");
    } else {
        header("Location: ../login.php");
    }
    exit();
}