<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();
requireStaff();

$staff_id = $_SESSION['user_id'];

$query = "SELECT dr.*, 
          CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as Location,
          CONCAT(u.FirstName, ' ', u.LastName) as Reporter
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          JOIN user u ON dr.ReporterID = u.UserID
          WHERE dr.StaffID = '$staff_id' 
          ORDER BY dr.DateReported DESC";

$result = mysqli_query($conn, $query);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .status-pending {
        background-color: #f39c12;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        font-size: 12px;
        font-weight: bold;
    }
    .status-in-progress {
        background-color: #3498db;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        font-size: 12px;
        font-weight: bold;
    }
    .status-resolved {
        background-color: #27ae60;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        font-size: 12px;
        font-weight: bold;
    }
    .note {
        background-color: #e8f4fd;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #3498db;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    th {
        background-color: #2c3e50;
        color: white;
    }
    tr:hover {
        background-color: #f5f5f5;
    }
</style>

<h1>🔧 My Assigned Tasks</h1>

<div class="note">
    <strong>📋 Note:</strong> These are the reports assigned to you. 
    Once you complete the repair, please notify the Admin to mark it as resolved.
</div>

<table>
    <thead>
        <tr>
            <th>Report #</th>
            <th>Location</th>
            <th>Category</th>
            <th>Reported By</th>
            <th>Description</th>
            <th>Date Reported</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($task = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><strong>#<?php echo $task['ReportID']; ?></strong></td>
                    <td><?php echo htmlspecialchars($task['Location']); ?></td>
                    <td><?php echo htmlspecialchars($task['Category']); ?></td>
                    <td><?php echo htmlspecialchars($task['Reporter']); ?></td>
                    <td><?php echo htmlspecialchars(substr($task['Description'], 0, 60)); ?>...</td>
                    <td><?php echo date('M d, Y', strtotime($task['DateReported'])); ?></td>
                    <td>
                        <?php
                        $status = $task['Status'];
                        if($status == 'pending') {
                            echo "<span class='status-pending'>⏳ PENDING</span>";
                        } elseif($status == 'in-progress') {
                            echo "<span class='status-in-progress'>🔄 IN PROGRESS</span>";
                        } elseif($status == 'resolved') {
                            echo "<span class='status-resolved'>✅ RESOLVED</span>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    ✅ No tasks assigned yet. Check back later!
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>