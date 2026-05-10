<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$base_path = '../';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'admins';

// Get all admins
$admins_result = mysqli_query($conn,
    "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
            sa.AdminID, sa.Department
     FROM user u
     INNER JOIN system_administrator sa ON u.UserID = sa.UserID
     ORDER BY u.CreatedAt DESC"
);

// Get all staff
$staff_result = mysqli_query($conn,
    "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
            ms.StaffID, ms.Specialization, ms.ContactNumber
     FROM user u
     INNER JOIN maintainance_staff ms ON u.UserID = ms.UserID
     ORDER BY u.CreatedAt DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; }
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 15px; color: #555; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* Tabs */
        .tabs { display: flex; gap: 5px; margin-bottom: 25px; border-bottom: 2px solid #ddd; }
        .tab-btn { padding: 12px 24px; background: none; border: none; font-size: 15px; font-weight: bold; cursor: pointer; color: #888; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: 0.2s; text-decoration: none; display: inline-block; }
        .tab-btn:hover { color: #800000; }
        .tab-btn.active { color: #800000; border-bottom-color: #800000; }

        /* Section */
        .section { background: white; padding: 25px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: none; }
        .section.active { display: block; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section h2 { color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8f9fa; padding: 13px 15px; text-align: left; border-bottom: 2px solid #eee; font-size: 13px; font-weight: bold; }
        .data-table td { padding: 13px 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #555; vertical-align: middle; }
        .data-table tr:hover { background: #fafafa; }

        .btn { display: inline-block; padding: 8px 16px; border-radius: 5px; font-weight: bold; font-size: 13px; text-decoration: none; border: none; cursor: pointer; transition: 0.2s; }
        .btn-create  { background: #800000; color: white; padding: 10px 18px; }
        .btn-create:hover  { background: #600000; }
        .btn-delete  { background: #dc3545; color: white; }
        .btn-delete:hover  { background: #a71d2a; }

        .alert-success { background: #e8f5e9; border-left: 5px solid #4caf50; padding: 12px 15px; border-radius: 4px; color: #2e7d32; margin-bottom: 20px; font-size: 14px; }
        .empty-msg { text-align: center; padding: 30px; color: #888; font-size: 14px; }

        .badge-admin { background: #800000; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-staff { background: #3498db; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <main class="content-area">
        <div class="header-container">
            <img src="<?= $base_path ?>citu_logo.png" alt="Logo" class="logo">
            <div class="header-text">
                <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                <p>System Administration | User Management</p>
            </div>
        </div>
        <hr class="red-line">

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert-success">✅ Account deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['created'])): ?>
            <div class="alert-success">✅ Account created successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self'): ?>
            <div class="alert-success" style="background:#fdecea;border-color:#e53935;color:#c62828;">❌ You cannot delete your own account.</div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=admins" class="tab-btn <?= $active_tab == 'admins' ? 'active' : '' ?>"> Administrators</a>
            <a href="?tab=staff"  class="tab-btn <?= $active_tab == 'staff'  ? 'active' : '' ?>"> Maintenance Staff</a>
        </div>

        <!-- ADMINS TAB -->
        <div class="section <?= $active_tab == 'admins' ? 'active' : '' ?>">
            <div class="section-header">
                <h2> List of Administrators</h2>
                <a href="create.php?type=admin" class="btn btn-create"> Add Administrator</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admin ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($admins_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($admins_result)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['AdminID']) ?></strong></td>
                            <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                            <td><?= htmlspecialchars($row['Username']) ?></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td><?= htmlspecialchars($row['Department'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                            <td>
                                <a href="delete.php?id=<?= $row['UserID'] ?>&type=admin"
                                   class="btn btn-delete"
                                   onclick="return confirm('Delete admin <?= htmlspecialchars($row['FirstName']) ?>?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty-msg">No administrators found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- STAFF TAB -->
        <div class="section <?= $active_tab == 'staff' ? 'active' : '' ?>">
            <div class="section-header">
                <h2>🔧 Maintenance Staff</h2>
                <a href="create.php?type=staff" class="btn btn-create"> Add Staff</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($staff_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($staff_result)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['StaffID']) ?></strong></td>
                            <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                            <td><?= htmlspecialchars($row['Username']) ?></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td><?= htmlspecialchars($row['Specialization'] ?? 'General') ?></td>
                            <td><?= htmlspecialchars($row['ContactNumber'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                            <td>
                                <a href="delete.php?id=<?= $row['UserID'] ?>&type=staff"
                                   class="btn btn-delete"
                                   onclick="return confirm('Delete staff <?= htmlspecialchars($row['FirstName']) ?>?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="empty-msg">No maintenance staff found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>
</body>
</html>
