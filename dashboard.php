<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

// If admin, redirect to admin dashboard
if ($_SESSION['user_type'] == 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// For students/employees - get their reports
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM damage_report WHERE ReporterID = '$user_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$recent_query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum 
                 FROM damage_report dr
                 JOIN location l ON dr.LocationID = l.LocationID
                 WHERE dr.ReporterID = '$user_id'
                 ORDER BY dr.DateReported DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CIT Damage Reporting</title>
    <style>
        /* BASE & SIDEBAR STYLES */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } 
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 16px; color: #555; font-weight: 500; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; }
        .stat-card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-card.pending .stat-number { color: #f39c12; }
        .stat-card.progress .stat-number { color: #3498db; }
        .stat-card.resolved .stat-number { color: #27ae60; }

        /* SECTIONS */
        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .section h2 { margin-bottom: 20px; color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        /* BUTTONS */
        .btn { display: inline-block; padding: 10px 20px; background: #800000; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn-secondary { background: #6c757d; }
        .btn:hover { opacity: 0.9; }

        /* TABLE */
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-weight: bold; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .data-table tr:hover { background: #f5f5f5; }

        /* STATUS COLORS */
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-in-progress { color: #3498db; font-weight: bold; }
        .status-resolved { color: #27ae60; font-weight: bold; }

        /* FOOTER */
        .main-footer { background-color: #333; color: white; text-align: center; padding: 20px; font-size: 13px; margin-top: 40px; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <nav class="sidebar">
        <div class="sidebar-brand">MENU</div>
        
        <a href="index.php" class="nav-item">🏠 Home</a>
        <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
        <a href="report_damage.php" class="nav-item">📝 Report Damage</a>
        <a href="my_reports.php" class="nav-item">📋 My Reports</a>
        
        <a href="logout.php" class="nav-item logout-btn">🚪 Logout</a>
    </nav>

    <main class="content-area">
        <div class="header-container">
            <img src="citu_logo.png" alt="CIT-U Logo" class="logo" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                <p>Damage Reporting System</p>
            </div>
        </div>
        <hr class="red-line">

        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! 👋</h1>
        <p>Manage your damage reports and track their status</p>
        
        <br>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📋 Total Reports</h3>
                <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card pending">
                <h3>⏳ Pending</h3>
                <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
            </div>
            <div class="stat-card progress">
                <h3>🔄 In Progress</h3>
                <div class="stat-number"><?php echo $stats['in_progress'] ?? 0; ?></div>
            </div>
            <div class="stat-card resolved">
                <h3>✅ Resolved</h3>
                <div class="stat-number"><?php echo $stats['resolved'] ?? 0; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>📝 Quick Actions</h2>
            <div style="display: flex; gap: 15px;">
                <a href="report_damage.php" class="btn">➕ Report New Damage</a>
                <a href="my_reports.php" class="btn btn-secondary">📋 View All My Reports</a>
            </div>
        </div>
        
        <div class="section">
            <h2>📊 Recent Reports</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Date Reported</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_result) > 0): ?>
                        <?php while($report = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                                <td><?php echo $report['BuildingName'] . ' - ' . $report['ClassRoomNum']; ?></td>
                                <td><?php echo $report['Category']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($report['DateReported'])); ?></td>
                                <td class="status-<?php echo $report['Status']; ?>"><?php echo ucfirst($report['Status']); ?></td>
                                <td><a href="view_report.php?id=<?php echo $report['ReportID']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">View</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No reports found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<footer class="main-footer">
    © 2026 Cebu Institute of Technology - University<br>
    Damage Reporting System | For authorized personnel only
</footer>

</body>
</html>