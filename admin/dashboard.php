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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending { background: #f39c12; color: white; }
        .badge-progress { background: #3498db; color: white; }
        .badge-in-progress { background: #3498db; color: white; }
        .badge-resolved { background: #27ae60; color: white; }
        .badge-cancelled { background: #e74c3c; color: white; }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            margin: 2px;
        }
        .action-cell {
            min-width: 180px;
        }
        .assigned-staff {
            background: #d4edda;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 11px;
            display: inline-block;
        }
        .unassigned {
            color: #999;
            font-style: italic;
        }
        .staff-select {
            margin-top: 5px;
            padding: 5px;
            font-size: 12px;
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
                            <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($report['ReporterName']); ?><br>
                                <small><?php echo htmlspecialchars($report['ReporterEmail']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($report['LocationName']); ?></td>
                            <td><?php echo htmlspecialchars($report['Category']); ?></td>
                            <td>
                                <?php echo htmlspecialchars(substr($report['Description'], 0, 50)); ?>
                                <?php if(strlen($report['Description']) > 50): ?>...<?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($report['DateReported'])); ?></d>
                            <d>
                                <?php if($report['StaffName']): ?>
                                    <span class="assigned-staff">
                                        ✅ <?php echo htmlspecialchars($report['StaffName']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="unassigned">❌ Not assigned</span>
                                <?php endif; ?>
                            </d>
                            <d>
                                <span class="badge badge-<?php echo $report['Status'] == 'in-progress' ? 'progress' : $report['Status']; ?>">
                                    <?php 
                                    $status_display = [
                                        'pending' => '⏳ Pending',
                                        'in-progress' => '🔄 In Progress',
                                        'resolved' => '✅ Resolved',
                                        'cancelled' => '❌ Cancelled'
                                    ];
                                    echo $status_display[$report['Status']] ?? ucfirst($report['Status']);
                                    ?>
                                </span>
                            </d>
                            <td class="action-cell">
                                <form method="POST" action="" style="display: inline-block;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                    
                                    <select name="status" class="staff-select" style="width: 120px;">
                                        <option value="pending" <?php echo $report['Status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                        <option value="in-progress" <?php echo $report['Status'] == 'in-progress' ? 'selected' : ''; ?>>🔄 In Progress</option>
                                        <option value="resolved" <?php echo $report['Status'] == 'resolved' ? 'selected' : ''; ?>>✅ Resolved</option>
                                        <option value="cancelled" <?php echo $report['Status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                                    </select>
                                    
                                    <select name="staff_id" class="staff-select" style="width: 130px;">
                                        <option value="">-- Assign Staff --</option>
                                        <?php foreach($staff_list as $staff): ?>
                                            <option value="<?php echo $staff['UserID']; ?>"
                                                <?php echo ($report['StaffUserID'] == $staff['UserID']) ? 'selected' : ''; ?>>
                                                🔧 <?php echo htmlspecialchars($staff['StaffName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <button type="submit" name="update_status" class="btn-sm" style="background: #28a745;">Save</button>
                                </form>
                                
                                <a href="?delete=<?php echo $report['ReportID']; ?>" class="btn-sm btn-danger" 
                                   onclick="return confirm('Delete report #<?php echo $report['ReportID']; ?>?')" 
                                   style="background: #dc3545; color: white; text-decoration: none; display: inline-block; text-align: center;">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">📭 No reports found</td>
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