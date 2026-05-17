<?php
// File: includes/sidebar.php

$current_file = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
$is_staff   = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'staff';
$is_student = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
$base_path  = $base_path ?? '';

// Active page highlighting
$active_home       = ($current_file == 'index.php') ? 'active' : '';
$active_dashboard  = ($current_file == 'dashboard.php') ? 'active' : '';
$active_report     = ($current_file == 'report_damage.php') ? 'active' : '';
$active_myreports  = ($current_file == 'my_reports.php') ? 'active' : '';
$active_admin      = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && $current_file != 'dashboard.php') ? 'active' : '';
$active_locations  = (strpos($_SERVER['PHP_SELF'], '/locations/') !== false) ? 'active' : '';

// For staff pages inside /staff/ folder, dashboard link should NOT be active
if (strpos($_SERVER['PHP_SELF'], '/staff/') !== false) {
    $active_dashboard = '';
}
?>

<nav class="sidebar">
    <div class="sidebar-brand">
        <?php
        if ($is_admin) echo 'ADMIN PANEL';
        elseif ($is_staff) echo 'STAFF PANEL';
        elseif ($is_student) echo 'STUDENT PANEL';
        else echo 'MENU';
        ?>
    </div>

    <!-- Everyone sees Home -->
    <a href="<?= $base_path ?>index.php" class="nav-item <?= $active_home ?>"> Home</a>

    <?php if ($is_staff): ?>
        <!-- Staff sees: Dashboard, My Tasks, Report Damage, My Reports -->
        <a href="<?= $base_path ?>dashboard.php" class="nav-item <?= $active_dashboard ?>"> Dashboard</a>
        <a href="<?= $base_path ?>staff/dashboard.php" class="nav-item <?= $active_mytasks ?>"> My Tasks</a>
        <a href="<?= $base_path ?>report_damage.php" class="nav-item <?= $active_report ?>"> Report Damage</a>
        <a href="<?= $base_path ?>my_reports.php" class="nav-item <?= $active_myreports ?>"> My Reports</a>

    <?php elseif ($is_admin): ?>
        <!-- Admin sees all 6 -->
        <a href="<?= $base_path ?>dashboard.php" class="nav-item <?= $active_dashboard ?>"> Dashboard</a>
        <a href="<?= $base_path ?>report_damage.php" class="nav-item <?= $active_report ?>"> Report Damage</a>
        <a href="<?= $base_path ?>my_reports.php" class="nav-item <?= $active_myreports ?>"> My Reports</a>
        <a href="<?= $base_path ?>admin/index.php" class="nav-item <?= $active_admin ?>"> Manage Admins</a>
        <a href="<?= $base_path ?>locations/index.php" class="nav-item <?= $active_locations ?>"> Manage Locations</a>

    <?php else: ?>
        <!-- Employee and Student see the same 3 buttons -->
        <a href="<?= $base_path ?>dashboard.php" class="nav-item <?= $active_dashboard ?>"> Dashboard</a>
        <a href="<?= $base_path ?>report_damage.php" class="nav-item <?= $active_report ?>"> Report Damage</a>
        <a href="<?= $base_path ?>my_reports.php" class="nav-item <?= $active_myreports ?>"> My Reports</a>
    <?php endif; ?>

    <a href="<?= $base_path ?>logout.php" class="nav-item logout-btn"> Logout</a>
</nav>
