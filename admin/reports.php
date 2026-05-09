<?php
require_once '../config/database.php';
include '../includes/navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$reports_query = "SELECT dr.*, 
                  CONCAT(u.FirstName, ' ', u.LastName) as ReporterName,
                  CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as LocationName
                  FROM damage_report dr
                  JOIN user u ON dr.ReporterID = u.UserID
                  JOIN location l ON dr.LocationID = l.LocationID
                  ORDER BY dr.DateReported DESC";
$reports_result = mysqli_query($conn, $reports_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Reports - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>📊 All Reports</h1>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        
        <table>
            <thead>
                <tr><th>ID</th><th>Reporter</th><th>Location</th><th>Category</th><th>Description</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php while($r = mysqli_fetch_assoc($reports_result)): ?>
                <tr>
                    <td>#<?php echo $r['ReportID']; ?></td>
                    <td><?php echo $r['ReporterName']; ?></td>
                    <td><?php echo $r['LocationName']; ?></td>
                    <td><?php echo $r['Category']; ?></td>
                    <td><?php echo substr($r['Description'], 0, 50); ?>...</td>
                    <td><span class="badge badge-<?php echo $r['Status']; ?>"><?php echo $r['Status']; ?></span></td>
                    <td><?php echo date('Y-m-d', strtotime($r['DateReported'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>