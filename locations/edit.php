<?php
require_once '../config/database.php';
include '../includes/navbar.php';

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
$errors = [];
$success = '';

// Get current location data
$query = "SELECT * FROM location WHERE LocationID = '$location_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$location = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $building_name = mysqli_real_escape_string($conn, $_POST['building_name']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    // Validation
    if (empty($building_name)) {
        $errors[] = "Building name is required";
    }
    if (empty($room_number)) {
        $errors[] = "Room/Classroom number is required";
    }
    
    // Check for duplicate (excluding current location)
    if (empty($errors)) {
        $check_query = "SELECT LocationID FROM location 
                        WHERE BuildingName = '$building_name' 
                        AND ClassRoomNum = '$room_number' 
                        AND LocationID != '$location_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "Another location with this name already exists!";
        }
    }
    
    // Update if no errors
    if (empty($errors)) {
        $update_query = "UPDATE location 
                         SET BuildingName = '$building_name', 
                             ClassRoomNum = '$room_number' 
                         WHERE LocationID = '$location_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $success = "Location updated successfully!";
            $location['BuildingName'] = $building_name;
            $location['ClassRoomNum'] = $room_number;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Location - CIT Reporting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>✏️ Edit Location</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $error): ?>
                        ❌ <?php echo $error; ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>🏢 Building Name *</label>
                    <input type="text" name="building_name" 
                           value="<?php echo htmlspecialchars($location['BuildingName']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>📚 Room/Classroom Number *</label>
                    <input type="text" name="room_number" 
                           value="<?php echo htmlspecialchars($location['ClassRoomNum']); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
            
            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>⚠️ Note:</strong> Changing a location name will affect all reports associated with it.
            </div>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>