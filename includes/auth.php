<?php
// Authentication helper functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function isStaff() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'staff';
}

function isEmployee() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'employee';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../login.php");
        exit();
    }
}

function requireStaff() {
    if (!isStaff()) {
        header("Location: ../login.php");
        exit();
    }
}
?>