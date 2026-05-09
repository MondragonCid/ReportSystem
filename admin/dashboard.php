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
                  CONCAT(s.FirstName, ' ', s.LastName) as StaffName
                  FROM damage_report dr
                  JOIN user u ON dr.ReporterID = u.UserID
                  JOIN location l ON dr.LocationID = l.LocationID
                  LEFT JOIN maintainance_staff ms ON dr.StaffID = ms.UserID
                  LEFT JOIN user s ON ms.UserID = s.UserID
                  ORDER BY dr.DateReported DESC";
$reports_result = mysqli_query($conn, $reports_query);

// Get staff list for assignment
$staff_query = "SELECT ms.UserID, CONCAT(u.FirstName, ' ', u.LastName) as StaffName, ms.Specialization
                FROM maintainance_staff ms
                JOIN user u ON ms.UserID = u.UserID";
$staff_result = mysqli_query($conn, $staff_query);
$staff_list = [];
while ($staff = mysqli_fetch_assoc($staff_result)) {
    $staff_list[] = $staff;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $staff_id = !empty($_POST['staff_id']) ? mysqli_real_escape_string($conn, $_POST['staff_id']) : 'NULL';
    
    $update_query = "UPDATE damage_report SET Status = '$new_status'";
    
    if ($new_status == 'resolved') {
        $update_query .= ", DateResolved = NOW()";
    }
    
    if ($staff_id != 'NULL') {
        $update_query .= ", StaffID = $staff_id";
    }
    
    $update_query .= " WHERE ReportID = '$report_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Report #$report_id updated successfully!";
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
    <title>Admin Dashboard - CIT University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-card.pending .stat-number { color: #f39c12; }
        .stat-card.progress .stat-number { color: #3498db; }
        .stat-card.resolved .stat-number { color: #27ae60; }
        .filter-bar {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-bar input, .filter-bar select {
            width: auto;
            margin: 0;
            padding: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-pending { background: #f39c12; color: white; }
        .badge-progress { background: #3498db; color: white; }
        .badge-resolved { background: #27ae60; color: white; }
        .badge-cancelled { background: #e74c3c; color: white; }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        .action-cell {
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👑 Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! Manage and track all damage reports.</p>
        
        <hr>
        
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success">✅ Report updated successfully!</div>
            <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">✅ Report deleted successfully!</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📋 Total Reports</h3>
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
        
        <!-- Quick Actions -->
        <div class="info-box" style="margin-bottom: 20px;">
            <strong>⚡ Quick Actions:</strong><br>
            <a href="index.php" class="btn btn-sm">👑 Manage Admins</a>
            <a href="../report_damage.php" class="btn btn-sm">📝 Submit Test Report</a>
        </div>
        
        <!-- Reports Table -->
        <h2>📋 All Damage Reports</h2>
        
        <div class="filter-bar">
            <input type="text" id="searchInput" placeholder="🔍 Search by reporter, location, or category..." onkeyup="filterTable()">
            <select id="statusFilter" onchange="filterTable()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <table class="data-table" id="reportsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reporter</th>
                    <th>Location</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Reported</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($reports_result) > 0): ?>
                    <?php while($report = mysqli_fetch_assoc($reports_result)): ?>
                        <tr class="report-row" data-status="<?php echo $report['Status']; ?>">
                            <td>#<?php echo $report['ReportID']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($report['ReporterName']); ?></strong><br>
                                <small><?php echo htmlspecialchars($report['ReporterEmail']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($report['LocationName']); ?></td>
                            <td><?php echo htmlspecialchars($report['Category']); ?></td>
                            <td>
                                <?php echo htmlspecialchars(substr($report['Description'], 0, 50)); ?>
                                <?php if(strlen($report['Description']) > 50): ?>...<?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($report['DateReported'])); ?></td>
                            <td>
                                <?php if($report['StaffName']): ?>
                                    <?php echo htmlspecialchars($report['StaffName']); ?>
                                <?php else: ?>
                                    <span style="color: #999;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $report['Status'] == 'in-progress' ? 'progress' : $report['Status']; ?>">
                                    <?php echo ucfirst($report['Status']); ?>
                                </span>
                            </td>
                            <td class="action-cell">
                                <form method="POST" action="" style="display: inline-block;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?php echo $report['Status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in-progress" <?php echo $report['Status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $report['Status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="cancelled" <?php echo $report['Status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <select name="staff_id" style="margin-top: 5px;">
                                        <option value="">Assign Staff</option>
                                        <?php foreach($staff_list as $staff): ?>
                                            <option value="<?php echo $staff['UserID']; ?>">
                                                <?php echo htmlspecialchars($staff['StaffName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-sm">Update</button>
                                </form>
                                <a href="?delete=<?php echo $report['ReportID']; ?>" class="btn-sm btn-danger" 
                                   onclick="return confirm('Delete report #<?php echo $report['ReportID']; ?>?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No reports found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#reportsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const status = row.getAttribute('data-status');
                
                let matchesSearch = text.includes(searchValue);
                let matchesStatus = statusValue === 'all' || status === statusValue;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>