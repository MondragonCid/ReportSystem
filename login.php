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
            
            if ($user['UserType'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
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

    <div class="header-logo">
        <img src="citu_logo.png" alt="CIT Logo">
        <h1>CIT University<br>Fault Report System</h1>
    </div>

    <div class="login-container">
        <p class="instructions">Login using your CIT University credentials to access the Fault Report System.</p>

        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email or Username:</label>
                <input type="text" id="email" name="email_or_username" placeholder="juan.delacruz@cit.edu" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="button-group">
                <button type="reset" class="btn btn-clear">CLEAR ENTRIES</button>
                <button type="submit" class="btn btn-login">LOGIN</button>
            </div>
        </form>

        <div class="footer-links">
            Forgot Password? <a href="#">Click here</a>
        </div>

        <div class="inquiries">
            For authorized CIT University personnel only<br>
            Contact IT Department for account assistance<br>
        </div>

        <!-- Integrated Test Accounts section from your image -->
        <div class="test-accounts">
            <h4>Test Accounts:</h4>
            <ul>
                <li><strong>Admin:</strong> admin.cit / admin123</li>
                <li><strong>Employee:</strong> john.smith / admin123</li>
                <li><strong>Staff:</strong> mike.staff / admin123</li>
            </ul>

            <p><strong> User Access Levels:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li> <strong>Admin</strong> → Admin Dashboard (CRUD, Reports, Staff)</li>
                    <li> <strong>Staff</strong> → Staff Dashboard (Assignments, Updates)</li>
                    <li><strong>Student/Employee</strong> → Submit Reports, Track Status</li>
        </div>
    </div>

</body>
</html>