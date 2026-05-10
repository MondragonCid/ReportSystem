<?php
require_once 'config/database.php';
include 'includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// If admin, redirect to admin dashboard
if ($user_type == 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// For students/employees - get their reports
$reports_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM damage_report WHERE ReporterID = '$user_id'";
$reports_result = mysqli_query($conn, $reports_query);
$stats = mysqli_fetch_assoc($reports_result);

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
    <title>Dashboard - CIT Damage Reporting System</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
    
        /* RESET & BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }

        .main-wrapper { display: flex; min-height: calc(100vh - 70px); }

        /* SIDEBAR (image_59adb1.png) */
        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; letter-spacing: 1px; text-align: center; background-color: #600000; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: flex; align-items: center; }
        .nav-item:hover { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } /* Logo placement like image_59b8ee.png */
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 16px; color: #555; font-weight: 500; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* ALERTS */
        .alert-banner { background-color: #e8f5e9; border-left: 5px solid #4caf50; padding: 20px; margin-bottom: 30px; border-radius: 4px; color: #2e7d32; font-size: 14px; }
        .alert-banner strong { font-size: 16px; }

        /* ABOUT SECTION CARDS (image_594b78.png) */
        .white-card { background: white; padding: 30px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .white-card h3 { margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .action-list { display: grid; gap: 15px; margin-top: 20px; }
        .action-item { display: flex; align-items: center; padding: 18px; background-color: #fcfcfc; border: 1px solid #eee; border-radius: 10px; text-decoration: none; color: #333; transition: all 0.3s ease; }
        .action-item:hover { background-color: #fff5f5; border-color: #800000; transform: translateX(8px); box-shadow: 0 4px 12px rgba(128,0,0,0.1); }
        .action-item .icon { font-size: 24px; margin-right: 20px; min-width: 40px; text-align: center; }
        .action-item .label { font-weight: bold; color: #800000; font-size: 16px; display: block; }
        .action-item .subtext { font-size: 13px; color: #777; }

        /* FOOTER (image_59ad12.png) */
        .main-footer { background-color: #333; color: white; text-align: center; padding: 20px; font-size: 13px; line-height: 1.5; }
    </style>
    
</head>
<body>
    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">MENU</div>
            
            <a href="<?= $base_path ?>index.php" class="nav-item">Home</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $base_path ?>dashboard.php" class="nav-item">Dashboard</a>
                <a href="<?= $base_path ?>report_damage.php" class="nav-item">Report Damage</a>
                <a href="<?= $base_path ?>my_reports.php" class="nav-item">My Reports</a>
                
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <a href="<?= $base_path ?>admin/index.php" class="nav-item">Manage Admins</a>
                    <a href="<?= $base_path ?>locations/index.php" class="nav-item">Manage Locations</a>
                <?php endif; ?>
                
                <a href="<?= $base_path ?>logout.php" class="nav-item logout-btn">Logout</a>
            <?php else: ?>
                <a href="<?= $base_path ?>login.php" class="nav-item">Login to System</a>
            <?php endif; ?>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="<?= $base_path ?>citu_logo.png" alt="CIT-U Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="main-container">
                h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! 👋</h1>
        <p>Manage your damage reports and track their status</p>
        
        <hr>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📋 Total Reports</h3>
                <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>⏳ Pending</h3>
                <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>🔄 In Progress</h3>
                <div class="stat-number"><?php echo $stats['in_progress'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
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
                                <td>#<?php echo $report['ReportID']; ?></td>
                                <td><?php echo $report['BuildingName'] . ' - ' . $report['ClassRoomNum']; ?></td>
                                <td><?php echo $report['Category']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($report['DateReported'])); ?></td>
                                <td class="status-<?php echo $report['Status']; ?>"><?php echo ucfirst($report['Status']); ?></td>
                                <td><a href="view_report.php?id=<?php echo $report['ReportID']; ?>">View</a></td>
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
            </div>
        </main>
    </div>

    <footer class="main-footer">
        © 2026 Cebu Institute of Technology - University<br>
        Damage Reporting System | For authorized personnel only
    </footer>

    
</body>
</html>

<?php include 'includes/footer.php'; ?>