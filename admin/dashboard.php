<?php
require_once '../config/database.php';
include '../includes/navbar.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get dashboard statistics
$stats_query = "SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM damage_report";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all reports with reporter and location info
$reports_query = "SELECT dr.*, 
                  CONCAT(u.FirstName, ' ', u.LastName) as ReporterName,
                  u.Email as ReporterEmail,
                  CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as LocationName,
                  CONCAT(s.FirstName, ' ', s.LastName) as StaffName,
                  s.UserID as StaffUserID
                  FROM damage_report dr
                  JOIN user u ON dr.ReporterID = u.UserID
                  JOIN location l ON dr.LocationID = l.LocationID
                  LEFT JOIN maintainance_staff ms ON dr.StaffID = ms.UserID
                  LEFT JOIN user s ON ms.UserID = s.UserID
                  ORDER BY dr.DateReported DESC";
$reports_result = mysqli_query($conn, $reports_query);

// Get staff list for assignment (using UserID from maintainance_staff)
$staff_query = "SELECT ms.UserID, CONCAT(u.FirstName, ' ', u.LastName) as StaffName, ms.Specialization
                FROM maintainance_staff ms
                JOIN user u ON ms.UserID = u.UserID
                WHERE u.IsActive = 1";
$staff_result = mysqli_query($conn, $staff_query);
$staff_list = [];
while ($staff = mysqli_fetch_assoc($staff_result)) {
    $staff_list[] = $staff;
}

// Handle status update and staff assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $staff_id = $_POST['staff_id'];
    
    // Start building the update query
    $update_query = "UPDATE damage_report SET Status = '$new_status'";
    
    // Handle resolved date
    if ($new_status == 'resolved') {
        $update_query .= ", DateResolved = NOW()";
    } else {
        $update_query .= ", DateResolved = NULL";
    }
    
    // Handle staff assignment (FIXED: proper NULL handling)
    if (!empty($staff_id) && $staff_id != '') {
        $update_query .= ", StaffID = '$staff_id'";
    } else {
        $update_query .= ", StaffID = NULL";
    }
    
    $update_query .= " WHERE ReportID = '$report_id'";
    
    // Debug: uncomment to see the query
    // echo "<pre>$update_query</pre>";
    
    if (mysqli_query($conn, $update_query)) {
        header("Location: dashboard.php?msg=updated");
        exit();
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Handle delete report
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $report_id = $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM damage_report WHERE ReportID = '$report_id'")) {
        header("Location: dashboard.php?msg=deleted");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CIT Damage Reporting</title>
    <style>
        /* BASE & SIDEBAR STYLES (From index.php) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } 
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* YOUR STATS CARDS CSS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-card.pending .stat-number { color: #f39c12; }
        .stat-card.progress .stat-number { color: #3498db; }
        .stat-card.resolved .stat-number { color: #27ae60; }

        /* TABLE & FILTERS CSS */
        .filter-bar { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; display: flex; gap: 10px; border: 1px solid #ddd; }
        .filter-bar input, .filter-bar select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; color: white; }
        .badge-pending { background: #f39c12; }
        .badge-progress { background: #3498db; }
        .badge-resolved { background: #27ae60; }
        .badge-cancelled { background: #e74c3c; }

        .btn-save { background: #28a745; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
        .btn-delete { background: #dc3545; color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-size: 12px; }

        .main-footer { background-color: #333; color: white; text-align: center; padding: 20px; font-size: 13px; margin-top: 40px; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">ADMIN PANEL</div>
            <a href="<?= $base_path ?>index.php" class="nav-item">Home</a>
            <a href="dashboard.php" class="nav-item active">Dashboard</a>
            <a href="index.php" class="nav-item">Manage Admins</a>
            <a href="<?= $base_path ?>locations/index.php" class="nav-item">Manage Locations</a>
            <a href="<?= $base_path ?>logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="<?= $base_path ?>citu_logo.png" alt="CIT-U Logo" class="logo">
                <div class="header-text">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>! System Overview</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📋 Total</h3>
                    <div class="stat-number"><?php echo $stats['total_reports'] ?? 0; ?></div>
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

            <div class="white-card-container" style="background: white; padding: 25px; border-radius: 10px; border: 1px solid #ddd;">
                <h2>📋 All Damage Reports</h2>
                <div class="filter-bar">
                    <input type="text" id="searchInput" placeholder="🔍 Search reports..." onkeyup="filterTable()" style="flex: 1;">
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <table class="data-table" id="reportsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Staff</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($reports_result) > 0): ?>
                            <?php while($report = mysqli_fetch_assoc($reports_result)): ?>
                                <tr class="report-row" data-status="<?php echo $report['Status']; ?>">
                                    <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                                    <td><?= htmlspecialchars($report['ReporterName']) ?></td>
                                    <td><?= htmlspecialchars($report['LocationName']) ?></td>
                                    <td><?= htmlspecialchars(substr($report['Description'], 0, 30)) ?>...</td>
                                    <td>
                                        <?php if($report['StaffName']): ?>
                                            <span style="font-size: 12px; color: #2e7d32;">✅ <?= htmlspecialchars($report['StaffName']) ?></span>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $report['Status'] == 'in-progress' ? 'progress' : $report['Status']; ?>">
                                            <?= ucfirst($report['Status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                            <select name="status" style="font-size: 11px;">
                                                <option value="pending" <?php echo $report['Status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in-progress" <?php echo $report['Status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $report['Status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-save">Save</button>
                                            <a href="?delete=<?= $report['ReportID'] ?>" class="btn-delete" onclick="return confirm('Delete this?')">🗑️</a>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#reportsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const status = row.getAttribute('data-status');
                const matchesSearch = text.includes(searchValue);
                const matchesStatus = statusValue === 'all' || status === statusValue;
                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }
    </script>
</body>
</html>