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

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

// Student and Employee have the same access level
function isRegularUser() {
    return isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['employee', 'student']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Works from both root and subdirectory pages
        $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
        $prefix = str_repeat('../', max(0, $depth - 1));
        header("Location: " . $prefix . "login.php");
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
