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
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! 👋</h1>
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
</body>
</html>

<?php include 'includes/footer.php'; ?>