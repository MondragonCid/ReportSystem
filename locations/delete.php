<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$location_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get location info
$query = "SELECT * FROM location WHERE LocationID = '$location_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$location = mysqli_fetch_assoc($result);

// Check if location is used in any reports
$check_reports = "SELECT COUNT(*) as report_count FROM damage_report WHERE LocationID = '$location_id'";
$reports_result = mysqli_query($conn, $check_reports);
$reports_count = mysqli_fetch_assoc($reports_result)['report_count'];

$error = '';

if (isset($_POST['confirm'])) {
    if ($reports_count > 0) {
        $error = "Cannot delete this location because it is used in $reports_count report(s).";
    } else {
        $delete_query = "DELETE FROM location WHERE LocationID = '$location_id'";
        
        if (mysqli_query($conn, $delete_query)) {
            header("Location: index.php?deleted=1");
            exit();
        } else {
            $error = "Deletion failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Location - CIT Reporting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin: 50px auto;">
        <div class="form-container">
            <h1>⚠️ Confirm Deletion</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-box" style="background: #fff3cd;">
                <p>Are you sure you want to delete this location?</p>
                <p><strong>🏢 Building:</strong> <?php echo htmlspecialchars($location['BuildingName']); ?></p>
                <p><strong>📚 Room:</strong> <?php echo htmlspecialchars($location['ClassRoomNum']); ?></p>
                
                <?php if ($reports_count > 0): ?>
                    <p style="color: red; font-weight: bold;">
                        ⚠️ This location is used in <?php echo $reports_count; ?> report(s)!
                        You must reassign or delete those reports first.
                    </p>
                <?php else: ?>
                    <p style="color: red;">This action cannot be undone!</p>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="">
                <?php if ($reports_count == 0): ?>
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="btn btn-danger">🗑️ Yes, Delete Location</button>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>