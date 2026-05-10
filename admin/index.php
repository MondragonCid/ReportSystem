<?php
require_once '../config/database.php';
include '../includes/navbar.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// FIXED QUERY - Get all admins correctly
$query = "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
                 sa.AdminID, sa.Department
          FROM user u
          INNER JOIN system_administrator sa ON u.UserID = sa.UserID
          WHERE u.UserType = 'admin'
          ORDER BY u.CreatedAt DESC";

$result = mysqli_query($conn, $query);

// Check if query failed
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Debug: Show how many admins found
$admin_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin CRUD - Testing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>👑 Admin Management</h1>
        
        <!-- Debug info (remove after testing) -->
        <div class="info-box" style="background: #e8f4fd;">
            <strong>🔍 Debug Info:</strong>
            Logged in as: <?php echo $_SESSION['username']; ?> (UserID: <?php echo $_SESSION['user_id']; ?>)
            <br>Admins found in database: <?php echo $admin_count; ?>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="create.php" class="btn">➕ CREATE New Admin</a>
            <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        
        <h2>📋 List of Administrators</h2>
        
        <?php if ($admin_count > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admin ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($admin = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $admin['AdminID']; ?></td>
                            <td><?php echo $admin['FirstName'] . ' ' . $admin['LastName']; ?></td>
                            <td><?php echo $admin['Username']; ?></td>
                            <td><?php echo $admin['Email']; ?></td>
                            <td><?php echo $admin['Department'] ?? 'N/A'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($admin['CreatedAt'])); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $admin['UserID']; ?>" class="btn-edit">✏️ Edit</a>
                                <?php if($admin['UserID'] != $_SESSION['user_id']): ?>
                                    <a href="delete.php?id=<?php echo $admin['UserID']; ?>" class="btn-delete" onclick="return confirm('Delete this admin?')">🗑️ Delete</a>
                                <?php else: ?>
                                    <span style="color:gray;">(Current)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-danger">
                ❌ No administrators found in database! 
                <br>This is a problem. Run the fix SQL below.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>