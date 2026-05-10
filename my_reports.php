<?php
require_once 'config/database.php';
require_once 'includes/auth.php';  // Add this for login check
include 'includes/header.php';      // Add this
include 'includes/navbar.php';      // Keep this

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum 
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          WHERE dr.ReporterID = '$user_id'
          ORDER BY dr.DateReported DESC";

$result = mysqli_query($conn, $query);
?>

<!-- REMOVE the duplicate HTML head section since header.php already has it -->
<!-- Your content starts here -->

<h1>📋 My Damage Reports</h1>
<p>View all reports you have submitted</p>

<hr>

<div style="margin: 20px 0;">
    <a href="report_damage.php" class="btn">➕ Report New Damage</a>
    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<table border="1" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Report #</th>
            <th>Location</th>
            <th>Category</th>
            <th>Description</th>
            <th>Date Reported</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($report = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                    <td><?php echo $report['BuildingName'] . ' ' . $report['ClassRoomNum']; ?></td>
                    <td><?php echo $report['Category']; ?></td>
                    <td><?php echo substr($report['Description'], 0, 60); ?>...</td>
                    <td><?php echo date('Y-m-d h:i A', strtotime($report['DateReported'])); ?></td>
                    <td>
                        <?php
                        $status = $report['Status'];
                        $badge_class = '';
                        if ($status == 'pending') $badge_class = 'status-pending';
                        elseif ($status == 'in-progress') $badge_class = 'status-in-progress';
                        elseif ($status == 'resolved') $badge_class = 'status-resolved';
                        else $badge_class = 'status-pending';
                        ?>
                        <span class="<?php echo $badge_class; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;">
                    ❌ No reports yet. 
                    <a href="report_damage.php">Click here to submit a report</a>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="info-box" style="margin-top: 20px;">
        <strong>📊 Summary:</strong> You have submitted <?php echo mysqli_num_rows($result); ?> report(s).
        <br>For questions about your reports, contact the Maintenance Department.
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>