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
            
            if ($user['UserType'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
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
    <div class="container" style="max-width: 500px; margin: 50px auto;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1> CEBU INSTITUTE OF TECHNOLOGY</h1>
            <h2>UNIVERSITY</h2>
            <h3>Damage Reporting System</h3>
        </div>
        
        <div class="info-box" style="text-align: center;">
            <p>🔐 Login using your CIT University credentials</p>
            <small>Email format: firstname.lastname@cit.edu</small>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label> Email or Username</label>
                <input type="text" name="email_or_username" 
                       placeholder="juan.delacruz@cit.edu or username" required>
            </div>
            
            <div class="form-group">
                <label> Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" style="width: 100%;">Login to System</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center; font-size: 12px;">
            <p> For authorized CIT University personnel only</p>
            <p>Contact IT Department for account assistance</p>
        </div>
        
        <hr>
        
        <div style="margin-top: 20px;">
            <h4>Test Accounts:</h4>
            <ul>
                <li><strong>Admin:</strong> admin.cit / admin123</li>
                <li><strong>Employee:</strong> john.smith / admin123</li>
                <li><strong>Staff:</strong> mike.staff / admin123</li>
            </ul>
        </div>
    </div>
</body>
</html>