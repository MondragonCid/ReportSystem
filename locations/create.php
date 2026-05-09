<?php
require_once '../config/database.php';
include '../includes/navbar.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$building_name = $room_number = '';
$errors = [];
$success = '';

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
    
    // Check for duplicate location
    if (empty($errors)) {
        $check_query = "SELECT LocationID FROM location 
                        WHERE BuildingName = '$building_name' AND ClassRoomNum = '$room_number'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "This location already exists!";
        }
    }
    
    // Insert if no errors
    if (empty($errors)) {
        $query = "INSERT INTO location (BuildingName, ClassRoomNum) 
                  VALUES ('$building_name', '$room_number')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Location added successfully!";
            $building_name = $room_number = ''; // Clear form
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
    <title>Add Location - CIT Reporting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>➕ Add New Location</h1>
            
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
                           value="<?php echo htmlspecialchars($building_name); ?>" 
                           placeholder="e.g., Main Building, Library, CIT Gym" required>
                </div>
                
                <div class="form-group">
                    <label>📚 Room/Classroom Number *</label>
                    <input type="text" name="room_number" 
                           value="<?php echo htmlspecialchars($room_number); ?>" 
                           placeholder="e.g., 101, 201, Lab 1, Court A" required>
                </div>
                
                <button type="submit" class="btn btn-primary">✅ Add Location</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
            
            <div class="info-box" style="margin-top: 20px;">
                <strong>📌 Example Locations:</strong><br>
                - Main Building, 101<br>
                - Library, 2F Reading Room<br>
                - CIT Gym, Court A<br>
                - Engineering Building, 301
            </div>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>