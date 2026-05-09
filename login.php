<?php
require_once 'config/database.php';
require_once 'includes/validation.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_or_username = mysqli_real_escape_string($conn, $_POST['email_or_username']);
    $password = $_POST['password'];
    
    // Check if input is email or username
    if (strpos($email_or_username, '@') !== false) {
        $query = "SELECT u.*, 
                  CASE 
                      WHEN sa.UserID IS NOT NULL THEN 'admin'
                      WHEN e.UserID IS NOT NULL THEN 'employee'
                      WHEN ms.UserID IS NOT NULL THEN 'staff'
                  END as role
                  FROM user u
                  LEFT JOIN system_administrator sa ON u.UserID = sa.UserID
                  LEFT JOIN employee e ON u.UserID = e.UserID
                  LEFT JOIN maintainance_staff ms ON u.UserID = ms.UserID
                  WHERE u.Email = '$email_or_username' AND u.IsActive = 1";
    } else {
        $query = "SELECT u.*, 
                  CASE 
                      WHEN sa.UserID IS NOT NULL THEN 'admin'
                      WHEN e.UserID IS NOT NULL THEN 'employee'
                      WHEN ms.UserID IS NOT NULL THEN 'staff'
                  END as role
                  FROM user u
                  LEFT JOIN system_administrator sa ON u.UserID = sa.UserID
                  LEFT JOIN employee e ON u.UserID = e.UserID
                  LEFT JOIN maintainance_staff ms ON u.UserID = ms.UserID
                  WHERE u.Username = '$email_or_username' AND u.IsActive = 1";
    }
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['user_type'] = $user['UserType'];
            $_SESSION['fullname'] = $user['FirstName'] . ' ' . $user['LastName'];
            $_SESSION['email'] = $user['Email'];
            
            // Redirect based on user type
            if ($user['UserType'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                // Students and employees go to reporting page
                header("Location: report_damage.php");
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Account not found or inactive";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CIT Damage Reporting System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="cit-logo">
                <h1>🏫 CEBU INSTITUTE OF TECHNOLOGY</h1>
                <h2>UNIVERSITY</h2>
                <h3>Damage Reporting System</h3>
            </div>
            
            <div class="login-info">
                <p>🔐 Login using your CIT University credentials</p>
                <small>Email format: firstname.lastname@cit.edu</small>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>📧 Email or Username</label>
                    <input type="text" name="email_or_username" 
                           placeholder="juan.delacruz@cit.edu or username" required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Login to System</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #7f8c8d;">
                <p>⚠️ For authorized CIT University personnel only</p>
                <p>Contact IT Department for account assistance</p>
            </div>
            
            <!-- Test Accounts Section -->
            <hr style="margin: 20px 0;">
            <div style="font-size: 12px;">
                <p><strong>📋 Test Accounts:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li><strong>👑 Admin</strong> (Can see dashboard & manage)</li>
                    <li style="margin-left: 15px;">Username: admin.cit / Password: admin123</li>
                    <li style="margin-top: 5px;"><strong>🔧 Staff</strong> (Maintenance - Can see dashboard)</li>
                    <li style="margin-left: 15px;">Username: mike.staff / Password: admin123</li>
                    <li style="margin-top: 5px;"><strong>👨‍🎓 Student / Employee</strong> (Can ONLY submit reports)</li>
                    <li style="margin-left: 15px;">Username: juana.delacruz / Password: admin123</li>
                    <li style="margin-left: 15px;">Username: john.smith / Password: admin123</li>
                </ul>
                <hr style="margin: 10px 0;">
                <p><strong>⚠️ User Access Levels:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>👑 <strong>Admin</strong> → Admin Dashboard (CRUD, Reports, Staff)</li>
                    <li>🔧 <strong>Staff</strong> → Staff Dashboard (Assignments, Updates)</li>
                    <li>👨‍🎓 <strong>Student/Employee</strong> → Submit Reports, Track Status</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>