<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// If admin, redirect to admin dashboard (Optional: Add logic if Staff has separate directory)
if ($user_type == 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Handle Status Update for Staff
if (isset($_POST['update_status'])) {
    $report_id = mysqli_real_escape_with_string($conn, $_POST['report_id']);
    $new_status = mysqli_real_escape_with_string($conn, $_POST['status']);
    
    $update_query = "UPDATE damage_report SET Status = '$new_status' WHERE ReportID = '$report_id'";
    mysqli_query($conn, $update_query);
}

// Stats Query
$reports_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM damage_report WHERE ReporterID = '$user_id'";
$reports_result = mysqli_query($conn, $reports_query);
$stats = mysqli_fetch_assoc($reports_result);

// Recent Reports Query
$recent_query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum 
                 FROM damage_report dr
                 JOIN location l ON dr.LocationID = l.LocationID
                 WHERE dr.ReporterID = '$user_id'
                 ORDER BY dr.DateReported DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

$base_path = './'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - CIT Damage Reporting</title>
    <style>
        /* RESET & FULL HEIGHT LAYOUT */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f4f4; }
        
        .main-wrapper { display: flex; height: 100vh; overflow: hidden; }

        /* SIDEBAR - FULL VERTICAL COVERAGE */
        .sidebar { 
            width: 260px; 
            background-color: #800000; 
            color: white; 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
            position: sticky;
            top: 0;
        }
        .sidebar-brand { padding: 25px; font-weight: bold; text-align: center; background-color: #600000; letter-spacing: 1px; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; height: 100%; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; }
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; width: 100%; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; margin-top: 10px; }

        /* TABLE STYLES */
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: #333; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        /* STATUS BADGES */
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; color: white; text-transform: uppercase; }
        .status-pending { background: #f39c12; }
        .status-in-progress { background: #3498db; }
        .status-resolved { background: #27ae60; }

        .btn-update { background: #800000; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; }
        .btn-update:hover { background: #a00000; }
        
        .main-footer { text-align: center; padding: 20px; color: #777; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">STAFF PANEL</div>
            <a href="dashboard.php" class="nav-item active">Dashboard</a>
            <a href="report_damage.php" class="nav-item">Report Issue</a>
            <a href="my_reports.php" class="nav-item">View My Reports</a>
            <a href="logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>Staff Dashboard</h1>
                    <p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📋 Total Reports</h3>
                    <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                </div>
                <div class="stat-card" style="border-top: 4px solid #f39c12;">
                    <h3>⏳ Pending</h3>
                    <div class="stat-number" style="color: #f39c12;"><?php echo $stats['pending'] ?? 0; ?></div>
                </div>
                <div class="stat-card" style="border-top: 4px solid #3498db;">
                    <h3>🔄 In Progress</h3>
                    <div class="stat-number" style="color: #3498db;"><?php echo $stats['in_progress'] ?? 0; ?></div>
                </div>
                <div class="stat-card" style="border-top: 4px solid #27ae60;">
                    <h3>✅ Resolved</h3>
                    <div class="stat-number" style="color: #27ae60;"><?php echo $stats['resolved'] ?? 0; ?></div>
                </div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h2 style="margin-bottom: 20px; color: #333;">📊 Recent Reports Management</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Manage Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_result) > 0): ?>
                            <?php while($report = mysqli_fetch_assoc($recent_result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($report['BuildingName'] . ' - ' . $report['ClassRoomNum']); ?></td>
                                    <td><?php echo htmlspecialchars($report['Category']); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo $report['Status']; ?>">
                                            <?php echo $report['Status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                            <select name="status" style="padding: 4px; border-radius: 4px; border: 1px solid #ddd; font-size: 12px;">
                                                <option value="pending" <?php echo ($report['Status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in-progress" <?php echo ($report['Status'] == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo ($report['Status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999; padding: 30px;">📭 No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>