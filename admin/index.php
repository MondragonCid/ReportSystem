<?php
require_once '../config/database.php';
include '../includes/navbar.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get all admins
$query = "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
                 sa.AdminID, sa.Department
          FROM user u
          INNER JOIN system_administrator sa ON u.UserID = sa.UserID
          ORDER BY u.CreatedAt DESC";

$result = mysqli_query($conn, $query);
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
        <h1>Admin Management (CRUD Testing)</h1>
        
        <div style="margin: 20px 0;">
            <a href="create.php" class="btn">CREATE New Admin</a>
            <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        
        <!-- READ: Display all admins -->
        <h2>📋 List of Administrators</h2>
        
        <table>
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
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($admin = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $admin['AdminID']; ?></td>
                            <td><?php echo $admin['FirstName'] . ' ' . $admin['LastName']; ?></td>
                            <td><?php echo $admin['Username']; ?></td>
                            <td><?php echo $admin['Email']; ?></td>
                            <td><?php echo $admin['Department'] ?? 'N/A'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($admin['CreatedAt'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $admin['UserID']; ?>" class="btn" style="background: #ffc107; color: #000;">✏️ EDIT</a>
                                <a href="delete.php?id=<?php echo $admin['UserID']; ?>" class="btn btn-danger" onclick="return confirm('Delete this admin?')">🗑️ DELETE</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No administrators found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>