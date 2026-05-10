<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);
$type    = isset($_GET['type']) ? $_GET['type'] : 'admin'; // 'admin' or 'staff'

// Don't allow self-deletion
if ($user_id == $_SESSION['user_id']) {
    header("Location: index.php?error=cannot_delete_self");
    exit();
}

// Get user info depending on type
if ($type == 'staff') {
    $query = "SELECT u.FirstName, u.LastName, ms.StaffID as RoleID
              FROM user u
              INNER JOIN maintainance_staff ms ON u.UserID = ms.UserID
              WHERE u.UserID = '$user_id'";
} else {
    $query = "SELECT u.FirstName, u.LastName, sa.AdminID as RoleID
              FROM user u
              INNER JOIN system_administrator sa ON u.UserID = sa.UserID
              WHERE u.UserID = '$user_id'";
}

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: index.php?error=not_found");
    exit();
}

$person = mysqli_fetch_assoc($result);
$error  = '';

if (isset($_POST['confirm'])) {
    if ($type == 'staff') {
        // Step 1: Unassign this staff from any damage reports (removes FK reference)
        $unassign = mysqli_query($conn, "UPDATE damage_report SET StaffID = NULL WHERE StaffID = '$user_id'");
        if (!$unassign) {
            $error = "Failed to unassign reports: " . mysqli_error($conn);
        } else {
            // Step 2: Delete from maintainance_staff
            $del1 = mysqli_query($conn, "DELETE FROM maintainance_staff WHERE UserID = '$user_id'");
            if (!$del1) {
                $error = "Failed to remove staff record: " . mysqli_error($conn);
            } else {
                // Step 3: Delete from user
                $del2 = mysqli_query($conn, "DELETE FROM user WHERE UserID = '$user_id'");
                if ($del2) {
                    header("Location: index.php?tab=staff&deleted=1");
                    exit();
                } else {
                    $error = "Failed to delete user account: " . mysqli_error($conn);
                }
            }
        }
    } else {
        // Admin deletion — no FK issues with damage_report
        $del1 = mysqli_query($conn, "DELETE FROM system_administrator WHERE UserID = '$user_id'");
        if (!$del1) {
            $error = "Failed to remove admin record: " . mysqli_error($conn);
        } else {
            $del2 = mysqli_query($conn, "DELETE FROM user WHERE UserID = '$user_id'");
            if ($del2) {
                header("Location: index.php?tab=admins&deleted=1");
                exit();
            } else {
                $error = "Failed to delete user account: " . mysqli_error($conn);
            }
        }
    }
}

$base_path = '../';
$label = $type == 'staff' ? 'Staff Member' : 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete <?= $label ?> - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }
        .content-area { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .confirm-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-top: 5px solid #dc3545; max-width: 480px; width: 100%; text-align: center; }
        .confirm-card h2 { color: #dc3545; margin-bottom: 20px; font-size: 22px; }
        .info-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .info-box p { margin-bottom: 8px; color: #333; font-size: 14px; }
        .info-box p:last-child { color: #dc3545; font-size: 12px; margin-bottom: 0; }
        .btn { display: inline-block; padding: 11px 22px; border-radius: 6px; font-weight: bold; font-size: 14px; border: none; cursor: pointer; text-decoration: none; margin: 5px; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #a71d2a; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .alert-error { background: #fdecea; border-left: 4px solid #e53935; padding: 12px 15px; border-radius: 4px; color: #c62828; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-area">
        <div class="confirm-card">
            <h2>⚠️ Confirm Deletion</h2>

            <?php if ($error): ?>
                <div class="alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="info-box">
                <p>You are about to delete this <?= strtolower($label) ?>:</p>
                <p><strong><?= htmlspecialchars($person['FirstName'] . ' ' . $person['LastName']) ?></strong></p>
                <p><?= $type == 'staff' ? 'Staff ID' : 'Admin ID' ?>: <strong><?= htmlspecialchars($person['RoleID']) ?></strong></p>
                <p>⚠️ This action cannot be undone!</p>
            </div>

            <form method="POST">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="btn btn-danger"> Yes, Delete</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>