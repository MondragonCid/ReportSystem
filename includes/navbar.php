<?php
// SUPER SIMPLE NAVBAR - Just for testing navigation
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
}
?>

<div class="navbar">
    <a href="<?php echo $base_path; ?>index.php">🏠 Home</a>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo $base_path; ?>dashboard.php">📊 Dashboard</a>
        <a href="<?php echo $base_path; ?>report_damage.php">📝 Report Damage</a>
        <a href="<?php echo $base_path; ?>my_reports.php">📋 My Reports</a>
        <?php if ($_SESSION['user_type'] == 'admin'): ?>
            <a href="<?php echo $base_path; ?>admin/index.php">👑 Admin CRUD</a>
        <?php endif; ?>
        <span style="margin-left: auto;">👤 <?php echo $_SESSION['username']; ?></span>
        <a href="<?php echo $base_path; ?>logout.php">🚪 Logout</a>
    <?php else: ?>
        <a href="<?php echo $base_path; ?>login.php" style="margin-left: auto;">🔐 Login</a>
    <?php endif; ?>
</div>