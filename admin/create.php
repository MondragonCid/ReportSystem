<?php
require_once '../config/database.php';
require_once '../includes/validation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$first_name = $last_name = $username = $admin_id = $department = '';
$errors = [];
$success = '';
$base_path = '../';

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
    
    // Duplicate checking
    $check_username = mysqli_query($conn, "SELECT UserID FROM user WHERE Username = '$username'");
    if (mysqli_num_rows($check_username) > 0) $errors[] = "Username '$username' already exists!";
    
    $check_email = mysqli_query($conn, "SELECT UserID FROM user WHERE Email = '$email'");
    if (mysqli_num_rows($check_email) > 0) $errors[] = "Email '$email' already exists!";
    
    $check_admin_id = mysqli_query($conn, "SELECT AdminID FROM system_administrator WHERE AdminID = '$admin_id'");
    if (mysqli_num_rows($check_admin_id) > 0) $errors[] = "Admin ID '$admin_id' already exists!";
    
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
                    $success = "✅ Admin created successfully!";
                    $first_name = $last_name = $username = $admin_id = $department = '';
                } else { throw new Exception("Admin insert failed"); }
            } else { throw new Exception("User insert failed"); }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { 
            height: 100%; 
            font-family: 'Segoe UI', sans-serif; 
            background-color: #f4f4f4; 
            display: flex; align-items: center; justify-content: center;
            padding: 20px 0;
        }

        /* 1/2 Screen Width for Desktop */
        .admin-card {
            width: 50%; 
            min-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-top: 6px solid #800000;
        }

        .header-box { text-align: center; margin-bottom: 30px; }
        .logo { height: 70px; margin-bottom: 15px; }
        .header-box h2 { color: #800000; font-size: 22px; text-transform: uppercase; }
        
        /* Grid for Name Row */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; font-size: 14px; }
        .form-group input {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none;
        }
        .form-group input:focus { border-color: #800000; box-shadow: 0 0 5px rgba(128,0,0,0.1); }
        .form-group input[readonly] { background: #f9f9f9; color: #777; cursor: not-allowed; border-style: dashed; }

        .btn-submit {
            width: 100%; padding: 14px; background: #800000; color: white; border: none; 
            border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s;
        }
        .btn-submit:hover { background: #600000; transform: translateY(-2px); }
        
        .btn-cancel { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #888; font-size: 14px; }
        .btn-cancel:hover { color: #800000; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; text-align: left; }

        .info-label { font-size: 12px; color: #666; font-style: italic; display: block; margin-top: 5px; }

        /* Responsive */
        @media (max-width: 1024px) { .admin-card { width: 70%; } }
        @media (max-width: 768px) { 
            .admin-card { width: 95%; min-width: unset; padding: 25px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="admin-card">
        <div class="header-box">
            <img src="<?= $base_path ?>citu_logo.png" alt="CIT Logo" class="logo">
            <h2>Register New Administrator</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Correct the following:</strong><br>
                <?php foreach($errors as $error): ?> • <?= $error; ?><br> <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($first_name); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($last_name); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>System Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username); ?>" placeholder="e.g. j.doe" required>
            </div>

            <div class="form-group">
                <label>Temporary Password *</label>
                <input type="password" name="password" required>
                <span class="info-label">Minimum 6 characters required.</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Admin ID *</label>
                    <input type="text" name="admin_id" value="<?= htmlspecialchars($admin_id); ?>" placeholder="ADMXXX" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($department); ?>" placeholder="e.g. Finance">
                </div>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
            <a href="index.php" class="btn-cancel">Return to Admin List</a>
        </form>
    </div>

</body>
</html>