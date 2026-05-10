<?php
require_once '../config/database.php';
include '../includes/navbar.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get all locations
$query = "SELECT * FROM location ORDER BY BuildingName, ClassRoomNum";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Locations - CIT Reporting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>📍 Location Management</h1>
        
        <div style="margin: 20px 0;">
            <a href="create.php" class="btn">➕ ADD New Location</a>
            <a href="../admin/index.php" class="btn btn-secondary">← Back to Admin Panel</a>
        </div>
        
        <h2>📋 List of Locations</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Location ID</th>
                    <th>Building Name</th>
                    <th>Room/Classroom Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($location = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $location['LocationID']; ?></td>
                            <td><?php echo htmlspecialchars($location['BuildingName']); ?></td>
                            <td><?php echo htmlspecialchars($location['ClassRoomNum']); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $location['LocationID']; ?>" class="btn-edit">✏️ Edit</a>
                                <a href="delete.php?id=<?php echo $location['LocationID']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Delete this location? It may affect existing reports.')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No locations found. <a href="create.php">Add your first location</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>