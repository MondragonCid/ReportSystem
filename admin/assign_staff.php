<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$report_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$report_id) {
    header("Location: reports.php");
    exit();
}

// Get report details
$report_query = "SELECT dr.*, 
                CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as Location
                FROM damage_report dr
                JOIN location l ON dr.LocationID = l.LocationID
                WHERE dr.ReportID = '$report_id'";
$report_result = mysqli_query($conn, $report_query);
$report = mysqli_fetch_assoc($report_result);

// Get all staff members
$staff_query = "SELECT ms.StaffID, ms.Specialization, 
                CONCAT(u.FirstName, ' ', u.LastName) as StaffName
                FROM maintainance_staff ms
                JOIN user u ON ms.UserID = u.UserID
                WHERE u.IsActive = 1";
$staff_result = mysqli_query($conn, $staff_query);

// Process assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_user_id = $_POST['staff_id'];
    
    $update_query = "UPDATE damage_report 
                     SET StaffID = '$staff_user_id', 
                         Status = 'in-progress'
                     WHERE ReportID = '$report_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Staff assigned successfully!";
        // Refresh report data
        $report_result = mysqli_query($conn, $report_query);
        $report = mysqli_fetch_assoc($report_result);
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<h1>Assign Staff to Report #<?php echo $report_id; ?></h1>

<?php if($success): ?>
    <div class="alert-success">✅ <?php echo $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert-danger">❌ <?php echo $error; ?></div>
<?php endif; ?>

<div style="background: #f0f0f0; padding: 15px; margin: 20px 0;">
    <h3>Report Details:</h3>
    <p><strong>Location:</strong> <?php echo $report['Location']; ?></p>
    <p><strong>Category:</strong> <?php echo $report['Category']; ?></p>
    <p><strong>Description:</strong> <?php echo $report['Description']; ?></p>
    <p><strong>Current Status:</strong> <?php echo ucfirst($report['Status']); ?></p>
    <?php if($report['StaffID']): ?>
        <p><strong>Currently Assigned to:</strong> Staff ID #<?php echo $report['StaffID']; ?></p>
    <?php endif; ?>
</div>

<form method="POST">
    <label>Select Staff Member:</label>
    <select name="staff_id" required>
        <option value="">-- Select Staff --</option>
        <?php while($staff = mysqli_fetch_assoc($staff_result)): ?>
            <option value="<?php echo $staff['StaffID']; ?>">
                <?php echo $staff['StaffName']; ?> - <?php echo $staff['Specialization']; ?>
            </option>
        <?php endwhile; ?>
    </select>
    
    <button type="submit">Assign Staff</button>
    <a href="reports.php" class="btn-secondary">Back to Reports</a>
</form>

<?php include '../includes/footer.php'; ?>