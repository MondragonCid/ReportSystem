<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

// Get all reports with assignment info
$query = "SELECT dr.*, 
          CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as Location,
          CONCAT(u.FirstName, ' ', u.LastName) as ReporterName,
          CONCAT(s.FirstName, ' ', s.LastName) as StaffName
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          JOIN user u ON dr.ReporterID = u.UserID
          LEFT JOIN user s ON dr.StaffID = s.UserID
          ORDER BY dr.DateReported DESC";

$result = mysqli_query($conn, $query);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<h1>📋 All Damage Reports</h1>

<a href="index.php" class="btn">← Back to Dashboard</a>

<table border="1" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Report #</th>
            <th>Location</th>
            <th>Category</th>
            <th>Reporter</th>
            <th>Description</th>
            <th>Date</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td>#<?php echo $row['ReportID']; ?></td>
            <td><?php echo $row['Location']; ?></td>
            <td><?php echo $row['Category']; ?></d>
            <td><?php echo $row['ReporterName']; ?></d>
            <td><?php echo substr($row['Description'], 0, 50); ?>...</d>
            <td><?php echo date('Y-m-d', strtotime($row['DateReported'])); ?></d>
            <d>
                <?php
                $status = $row['Status'];
                if($status == 'pending') echo "<span class='status-pending'>Pending</span>";
                elseif($status == 'in-progress') echo "<span class='status-in-progress'>In Progress</span>";
                elseif($status == 'resolved') echo "<span class='status-resolved'>Resolved</span>";
                ?>
            </d>
            <d>
                <?php 
                if($row['StaffName']) {
                    echo "✅ " . $row['StaffName'];
                } else {
                    echo "❌ Not assigned";
                }
                ?>
            </d>
            <d>
                <?php if($row['Status'] != 'resolved'): ?>
                    <a href="assign_staff.php?id=<?php echo $row['ReportID']; ?>" class="btn">
                        Assign Staff
                    </a>
                <?php else: ?>
                    Completed
                <?php endif; ?>
            </d>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>