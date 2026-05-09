<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// Don't allow self-deletion
if ($user_id == $_SESSION['user_id']) {
    header("Location: index.php?error=cannot_delete_self");
    exit();
}

// Get admin info
$query = "SELECT u.FirstName, u.LastName, sa.AdminID 
          FROM user u 
          INNER JOIN system_administrator sa ON u.UserID = sa.UserID 
          WHERE u.UserID = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$admin = mysqli_fetch_assoc($result);

if (isset($_POST['confirm'])) {
    mysqli_begin_transaction($conn);
    
    try {
        mysqli_query($conn, "DELETE FROM system_administrator WHERE UserID='$user_id'");
        mysqli_query($conn, "DELETE FROM user WHERE UserID='$user_id'");
        mysqli_commit($conn);
        header("Location: index.php?deleted=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Deletion failed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Admin - Testing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; text-align: center;">
        <h1>⚠️ Confirm Deletion</h1>
        
        <div class="info-box" style="background: #fff3cd;">
            <p>Are you sure you want to delete this administrator?</p>
            <p><strong><?php echo $admin['FirstName'] . ' ' . $admin['LastName']; ?></strong></p>
            <p><strong>Admin ID:</strong> <?php echo $admin['AdminID']; ?></p>
            <p style="color: red; font-size: 12px;">This action cannot be undone!</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-danger">🗑️ Yes, Delete Admin</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>