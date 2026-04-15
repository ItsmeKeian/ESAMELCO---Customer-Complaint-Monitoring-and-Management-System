<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

// Check if role is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}