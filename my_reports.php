<?php
require_once 'config/database.php';
include 'includes/navbar.php'; 

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Fetch reports for the logged-in user (Staff/User)
$query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum 
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          WHERE dr.ReporterID = '$user_id'
          ORDER BY dr.DateReported DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - CIT University</title>
    <style>
        /* CORE LAYOUT */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f4f4; overflow: hidden; }
        
        .main-wrapper { display: flex; height: 100vh; width: 100vw; }

        /* SIDEBAR - Full Vertical Coverage */
        .sidebar { 
            width: 260px; 
            background-color: #800000; 
            color: white; 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
            flex-shrink: 0; 
        }
        
        .sidebar-brand { padding: 25px; font-weight: bold; text-align: center; background-color: #600000; font-size: 1.1rem; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; }
        .logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; }
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* TABLE STYLES */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #444; font-size: 14px; }
        .report-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        
        /* STATUS BADGES */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; color: white; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #f39c12; }
        .status-in-progress { background: #3498db; }
        .status-resolved { background: #27ae60; }

        .btn-new { background-color: #800000; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; font-size: 14px; }
        .btn-new:hover { background-color: #a00000; }

        .summary-box { margin-top: 25px; padding: 15px; background: #e9ecef; border-left: 5px solid #800000; border-radius: 4px; font-size: 14px; color: #444; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand"><?php echo strtoupper($user_type); ?> PANEL</div>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="report_damage.php" class="nav-item">Report Issue</a>
            <a href="my_reports.php" class="nav-item active">View My Reports</a>
            <a href="logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System | My Submissions</p>
                </div>
            </div>
            <hr class="red-line">

            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #333;">📋 Personal Report History</h2>
                <a href="report_damage.php" class="btn-new">➕ Report New Damage</a>
            </div>

            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Report #</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Date Reported</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($report = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($report['BuildingName'] . ' - ' . $report['ClassRoomNum']); ?></td>
                                    <td><?php echo htmlspecialchars($report['Category']); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($report['DateReported'])); ?></td>
                                    <td>
                                        <?php
                                        $status = $report['Status'];
                                        $class = "status-pending";
                                        if ($status == 'in-progress') $class = "status-in-progress";
                                        if ($status == 'resolved') $class = "status-resolved";
                                        ?>
                                        <span class="badge <?php echo $class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #777;">
                                    📭 You haven't submitted any reports yet.<br>
                                    <a href="report_damage.php" style="color: #800000;">Submit your first report here.</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="summary-box">
                    <strong>📊 Summary:</strong> You have submitted a total of <strong><?php echo mysqli_num_rows($result); ?></strong> report(s).
                    <br>Status updates are managed by the Maintenance Department. Check back here for progress.
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>