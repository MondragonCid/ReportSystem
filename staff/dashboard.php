<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();
requireStaff();

$staff_id  = $_SESSION['user_id'];
$base_path = '../';

// Count stats for this staff
$stats_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM damage_report WHERE StaffID = '$staff_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all tasks assigned to this staff
$query = "SELECT dr.*,
          CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as Location,
          CONCAT(u.FirstName, ' ', u.LastName) as Reporter
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          JOIN user u ON dr.ReporterID = u.UserID
          WHERE dr.StaffID = '$staff_id'
          ORDER BY 
            FIELD(dr.Status, 'in-progress', 'pending', 'resolved', 'cancelled'),
            dr.DateReported DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - CIT Damage Reporting</title>
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

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; }
        .stat-card h3 { font-size: 13px; color: #666; margin-bottom: 10px; }
        .stat-number { font-size: 30px; font-weight: bold; color: #2c3e50; }
        .stat-card.pending  .stat-number { color: #f39c12; }
        .stat-card.progress .stat-number { color: #3498db; }
        .stat-card.resolved .stat-number { color: #27ae60; }

        /* Section */
        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .section h2 { margin-bottom: 20px; color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8f9fa; padding: 13px 15px; text-align: left; border-bottom: 2px solid #eee; font-size: 13px; font-weight: bold; }
        .data-table td { padding: 13px 15px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: middle; }
        .data-table tr:hover { background: #fafafa; }

        /* Status badges */
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; color: white; display: inline-block; }
        .badge-pending    { background: #f39c12; }
        .badge-progress   { background: #3498db; }
        .badge-resolved   { background: #27ae60; }
        .badge-cancelled  { background: #e74c3c; }

        /* Info banner */
        .info-banner { background: #e8f4fd; border-left: 5px solid #3498db; padding: 14px 18px; border-radius: 4px; color: #1a6fa0; font-size: 14px; margin-bottom: 25px; }

        .empty-msg { text-align: center; padding: 50px; color: #888; }
        .empty-msg p { font-size: 16px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="main-wrapper">

    <?php include '../includes/sidebar.php'; ?>

    <main class="content-area">
        <div class="header-container">
            <img src="<?= $base_path ?>citu_logo.png" alt="CIT-U Logo" class="logo">
            <div class="header-text">
                <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                <p>My Assigned Tasks — Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>
            </div>
        </div>
        <hr class="red-line">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📋 Total Assigned</h3>
                <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
            </div>
            <div class="stat-card pending">
                <h3>⏳ Pending</h3>
                <div class="stat-number"><?= $stats['pending'] ?? 0 ?></div>
            </div>
            <div class="stat-card progress">
                <h3>🔄 In Progress</h3>
                <div class="stat-number"><?= $stats['in_progress'] ?? 0 ?></div>
            </div>
            <div class="stat-card resolved">
                <h3>✅ Resolved</h3>
                <div class="stat-number"><?= $stats['resolved'] ?? 0 ?></div>
            </div>
        </div>

        <div class="info-banner">
            📋 These are the damage reports assigned to you. Once you complete a repair, notify the Admin to mark it as <strong>Resolved</strong>.
        </div>

        <div class="section">
            <h2>🔧 My Assigned Tasks</h2>

            <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Report #</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Reported By</th>
                        <th>Description</th>
                        <th>Date Reported</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($task = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?= $task['ReportID'] ?></strong></td>
                        <td><?= htmlspecialchars($task['Location']) ?></td>
                        <td><?= htmlspecialchars($task['Category']) ?></td>
                        <td><?= htmlspecialchars($task['Reporter']) ?></td>
                        <td><?= htmlspecialchars(substr($task['Description'], 0, 60)) ?><?= strlen($task['Description']) > 60 ? '...' : '' ?></td>
                        <td><?= date('M d, Y', strtotime($task['DateReported'])) ?></td>
                        <td>
                            <?php
                            $s = $task['Status'];
                            $label = ucfirst(str_replace('-', ' ', $s));
                            $cls = $s == 'in-progress' ? 'progress' : $s;
                            echo "<span class='badge badge-$cls'>$label</span>";
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-msg">
                    <p>✅ No tasks assigned to you yet.</p>
                    <p style="font-size:13px;">Check back later or contact the admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
