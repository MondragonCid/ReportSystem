<?php
require_once 'config/database.php';
include 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location_id = mysqli_real_escape_string($conn, $_POST['location_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($location_id) || empty($category) || empty($description)) {
        $error = "Please fill in all required fields";
    } else {
        $query = "INSERT INTO damage_report (ReporterID, LocationID, Category, Description, Status, DateReported) 
                  VALUES ('$user_id', '$location_id', '$category', '$description', 'pending', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $report_id = mysqli_insert_id($conn);
            $success = " Report submitted successfully! Reference #: " . $report_id;
        } else {
            $error = " Failed to submit report: " . mysqli_error($conn);
        }
    }
}

// Get locations for dropdown
$locations_query = "SELECT * FROM location ORDER BY BuildingName, ClassRoomNum";
$locations_result = mysqli_query($conn, $locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Damage - CIT University</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1> Report Damage / Issue</h1>
        
        <div class="info-box">
            <strong> Reporter Information</strong><br>
            Name: <?php echo htmlspecialchars($_SESSION['fullname']); ?><br>
            Email: <?php echo htmlspecialchars($_SESSION['email']); ?><br>
            User Type: <?php echo ucfirst($_SESSION['user_type']); ?>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label> Location *</label>
                <select name="location_id" required>
                    <option value="">Select Building & Room</option>
                    <?php while($loc = mysqli_fetch_assoc($locations_result)): ?>
                        <option value="<?php echo $loc['LocationID']; ?>">
                            <?php echo $loc['BuildingName'] . ' - Room ' . $loc['ClassRoomNum']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label> Category *</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option>Electrical (lights, outlets, fans, ACU)</option>
                    <option>Furniture (chairs, tables, cabinets)</option>
                    <option>IT Equipment (computers, projectors, printers)</option>
                    <option>Plumbing (faucets, toilets, pipes)</option>
                    <option>Structural (walls, ceilings, floors)</option>
                    <option>Other Facilities</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📝 Description *</label>
                <textarea name="description" rows="5" required 
                          placeholder="Please describe the damage or issue in detail..."></textarea>
                <small>Max 500 characters</small>
            </div>
            
            <button type="submit" class="btn"> Submit Report</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>