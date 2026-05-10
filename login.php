<?php

require_once 'config/database.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_input = mysqli_real_escape_string($conn, $_POST['email_or_username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM user WHERE (Username = '$user_input' OR Email = '$user_input') AND IsActive = 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['user_type'] = $user['UserType'];
            $_SESSION['fullname'] = $user['FirstName'] . ' ' . $user['LastName'];
            
            // Redirect based on user type
            if ($user['UserType'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                // Students and employees go to reporting page
                header("Location: report_damage.php");
            }
            exit();
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Account not found or is currently inactive.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CIT University Fault Report Process</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .header-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-logo img {
            height: 60px;
            margin-right: 15px;
        }
        .header-logo h1 {
            color: #7b181b;
            font-size: 20px;
            line-height: 1.2;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .instructions {
            font-size: 14px;
            color: #333;
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-clear {
            background-color: #8c3b40;
        }
        .btn-login {
            background-color: #7b181b;
        }
        .footer-links {
            margin-top: 20px;
            font-size: 13px;
            text-align: left;
        }
        .footer-links a {
            color: #007bff;
            text-decoration: none;
        }
        .inquiries {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        .test-accounts {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
        }
        .test-accounts h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .test-accounts ul {
            margin: 0;
            padding-left: 20px;
            color: #444;
        }
        .test-accounts li {
            margin-bottom: 5px;
        }
    </style>
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