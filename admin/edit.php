<?php
require_once '../config/database.php';
require_once '../includes/validation.php';
include '../includes/navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);
$errors = [];
$success = '';

// Get current admin data
$query = "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName,
                 sa.AdminID, sa.Department
          FROM user u
          INNER JOIN system_administrator sa ON u.UserID = sa.UserID
          WHERE u.UserID = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$admin = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $new_password = $_POST['new_password'];
    
    $email = generateCITEmail($first_name, $last_name);
    
    if (empty($first_name)) $errors[] = "First name required";
    if (empty($last_name)) $errors[] = "Last name required";
    if (empty($username)) $errors[] = "Username required";
    if (empty($admin_id)) $errors[] = "Admin ID required";
    
    if (isUsernameExists($conn, $username, $user_id)) {
        $errors[] = "Username already taken";
    }
    
    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        
        try {
            $update_user = "UPDATE user 
                           SET FirstName='$first_name', LastName='$last_name', 
                               Username='$username', Email='$email'";
            
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_user .= ", Password='$hashed'";
            }
            
            $update_user .= " WHERE UserID='$user_id'";
            mysqli_query($conn, $update_user);
            
            $update_admin = "UPDATE system_administrator 
                           SET AdminID='$admin_id', Department='$department' 
                           WHERE UserID='$user_id'";
            mysqli_query($conn, $update_admin);
            
            mysqli_commit($conn);
            $success = "Admin updated successfully!";
            
            // Refresh data
            $admin['FirstName'] = $first_name;
            $admin['LastName'] = $last_name;
            $admin['Username'] = $username;
            $admin['Email'] = $email;
            $admin['AdminID'] = $admin_id;
            $admin['Department'] = $department;
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Update failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin - Testing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>✏️ EDIT Administrator</h1>
        
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
            <div class="form-row">
                <div>
                    <label>First Name *</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($admin['FirstName']); ?>" required>
                </div>
                <div>
                    <label>Last Name *</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($admin['LastName']); ?>" required>
                </div>
            </div>
            
            <div class="info-box" style="background: #e8f4fd;">
                <label>Email (Auto-generated):</label>
                <input type="text" value="<?php echo generateCITEmail($admin['FirstName'], $admin['LastName']); ?>" readonly style="background:#eee;">
            </div>
            
            <label>Username *</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($admin['Username']); ?>" required>
            
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="new_password">
            
            <label>Admin ID *</label>
            <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin['AdminID']); ?>" required>
            
            <label>Department</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($admin['Department'] ?? ''); ?>">
            
            <button type="submit">💾 Update Admin</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>