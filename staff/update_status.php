<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();
requireStaff();

$report_id = $_GET['id'] ?? null;
$staff_id = $_SESSION['user_id'];

if (!$report_id) {
    header("Location: dashboard.php");
    exit();
}

// Verify this report is assigned to this staff
$check = "SELECT dr.*, 
          CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as Location
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          WHERE dr.ReportID='$report_id' AND dr.StaffID='$staff_id'";
$check_result = mysqli_query($conn, $check);

if (mysqli_num_rows($check_result) == 0) {
    die("❌ You are not authorized to update this report!");
}

$report = mysqli_fetch_assoc($check_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    
    if ($new_status == 'resolved') {
        $query = "UPDATE damage_report 
                  SET Status='$new_status', 
                      DateResolved=NOW()
                  WHERE ReportID='$report_id' AND StaffID='$staff_id'";
    } else {
        $query = "UPDATE damage_report 
                  SET Status='$new_status'
                  WHERE ReportID='$report_id' AND StaffID='$staff_id'";
    }
    
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?updated=1");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
    .report-details {
        background: #f0f0f0;
        padding: 15px;
        margin: 20px 0;
        border-left: 4px solid #3498db;
    }
    .btn-primary {
        background-color: #27ae60;
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-secondary {
        background-color: #95a5a6;
        color: white;
        padding: 8px 20px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
    }
</style>

<h1>Update Report #<?php echo $report_id; ?></h1>

<div class="report-details">
    <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($report['Location']); ?></p>
    <p><strong>📂 Category:</strong> <?php echo htmlspecialchars($report['Category']); ?></p>
    <p><strong>📝 Description:</strong></p>
    <p style="background: white; padding: 10px; border-radius: 5px;">
        <?php echo nl2br(htmlspecialchars($report['Description'])); ?>
    </p>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">❌ <?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
    <label><strong>Update Status:</strong></label><br><br>
    <select name="status" required>
        <?php if($report['Status'] == 'pending'): ?>
            <option value="in-progress">🔄 In Progress (Working on it)</option>
        <?php elseif($report['Status'] == 'in-progress'): ?>
            <option value="resolved">✅ Resolved (Completed)</option>
        <?php endif; ?>
    </select>
    
    <br><br>
    
    <?php if($report['Status'] == 'pending'): ?>
        <button type="submit" class="btn-primary">🔧 Start Working</button>
    <?php elseif($report['Status'] == 'in-progress'): ?>
        <button type="submit" class="btn-primary">✅ Mark as Resolved</button>
    <?php endif; ?>
    
    <a href="dashboard.php" class="btn-secondary">← Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>