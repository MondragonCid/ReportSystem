<?php
require_once '../config/database.php';
require_once '../includes/validation.php';
include '../includes/navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$first_name = $last_name = $username = $admin_id = $department = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    
    // Auto-generate email
    $email = generateCITEmail($first_name, $last_name);
    
    // Validations
    if (empty($first_name)) $errors[] = "First name required";
    if (empty($last_name)) $errors[] = "Last name required";
    if (empty($username)) $errors[] = "Username required";
    if (empty($password)) $errors[] = "Password required";
    if (strlen($password) < 6) $errors[] = "Password must be 6+ characters";
    if (empty($admin_id)) $errors[] = "Admin ID required";
    
    // BETTER DUPLICATE CHECKING
    $check_username = mysqli_query($conn, "SELECT UserID FROM user WHERE Username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = "Username '$username' already exists! Choose a different username.";
    }
    
    $check_email = mysqli_query($conn, "SELECT UserID FROM user WHERE Email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email '$email' already exists!";
    }
    
    $check_admin_id = mysqli_query($conn, "SELECT AdminID FROM system_administrator WHERE AdminID = '$admin_id'");
    if (mysqli_num_rows($check_admin_id) > 0) {
        $errors[] = "Admin ID '$admin_id' already exists!";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        mysqli_begin_transaction($conn);
        
        try {
            $insert_user = "INSERT INTO user (Username, Password, Email, FirstName, LastName, UserType) 
                            VALUES ('$username', '$hashed_password', '$email', '$first_name', '$last_name', 'admin')";
            
            if (mysqli_query($conn, $insert_user)) {
                $user_id = mysqli_insert_id($conn);
                
                $insert_admin = "INSERT INTO system_administrator (UserID, AdminID, Department) 
                                 VALUES ('$user_id', '$admin_id', '$department')";
                
                if (mysqli_query($conn, $insert_admin)) {
                    mysqli_commit($conn);
                    $success = "✅ Admin created successfully!<br>Username: $username<br>Email: $email<br>Admin ID: $admin_id";
                    $first_name = $last_name = $username = $admin_id = $department = '';
                } else {
                    throw new Exception("Admin insert failed: " . mysqli_error($conn));
                }
            } else {
                throw new Exception("User insert failed: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin - Testing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>➕ CREATE New Administrator</h1>
        
        <div class="info-box">
            <strong>📧 Email Rule:</strong> Email will be auto-generated as: <code>firstname.lastname@cit.edu</code>
            <br><strong>⚠️ Note:</strong> Username, Email, and Admin ID must be unique.
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>❌ Errors:</strong><br>
                <?php foreach($errors as $error): ?>
                    • <?php echo $error; ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-row">
                <div>
                    <label>First Name *</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                </div>
                <div>
                    <label>Last Name *</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                </div>
            </div>
            
            <div class="info-box" style="background: #e8f4fd;">
                <label>Generated Email:</label>
                <input type="text" value="<?php echo generateCITEmail($first_name, $last_name); ?>" readonly style="background:#eee;">
            </div>
            
            <label>Username * (unique)</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            <small>Cannot be 'admin.cit' or existing username</small>
            
            <label>Password * (min 6 characters)</label>
            <input type="password" name="password" required>
            
            <label>Admin ID * (unique, e.g., ADM002, ADM003)</label>
            <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>" placeholder="ADM002" required>
            <small>Cannot be ADM001 (already exists)</small>
            
            <label>Department</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>" placeholder="e.g., Registrar, Finance">
            
            <button type="submit" class="btn">✅ Create Admin</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
        
        <hr>
        <div class="info-box">
            <strong>💡 Tip:</strong> To create another admin, use different credentials:<br>
            Example: First: Juan, Last: Santos, Username: juan.santos, Admin ID: ADM002
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>