<?php
require_once 'config/database.php';
include 'includes/navbar.php';

// Only staff and admin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'staff' && $_SESSION['user_type'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// Get pending reports assigned to staff
$user_id = $_SESSION['user_id'];
$query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum, 
          CONCAT(u.FirstName, ' ', u.LastName) as ReporterName
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          JOIN user u ON dr.ReporterID = u.UserID
          WHERE dr.Status IN ('pending', 'in-progress')
          ORDER BY dr.DateReported DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - CIT University</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔧 Maintenance Staff Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</p>
        
        <hr>
        
        <h2>📋 Pending & In-Progress Reports</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Report #</th>
                    <th>Location</th>
                    <th>Category</th>
                    <th>Reported By</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($report = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $report['ReportID']; ?></td>
                            <td><?php echo $report['BuildingName'] . ' ' . $report['ClassRoomNum']; ?></td>
                            <td><?php echo $report['Category']; ?></td>
                            <td><?php echo $report['ReporterName']; ?></td>
                            <td><?php echo substr($report['Description'], 0, 50); ?>...<tr>
                            <td>
                                <span class="status-<?php echo $report['Status']; ?>">
                                    <?php echo ucfirst($report['Status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="update_status.php" style="display: inline;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $report['Status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in-progress" <?php echo $report['Status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $report['Status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No pending reports 🎉</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>