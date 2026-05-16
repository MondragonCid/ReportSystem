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
            
            if ($user["UserType"] == "admin") {
                header("Location: admin/dashboard.php");
            } elseif ($user["UserType"] == "staff") {
                header("Location: staff/dashboard.php");
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
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 30px 20px;
        }

        /* FIX: constrain header width to match the card, and center properly */
        .header-logo {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 400px;
            margin-bottom: 16px;
            gap: 14px;
        }

        /* FIX: give logo a fixed size so it doesn't collapse if image fails to load */
        .header-logo img {
            height: 64px;
            width: 64px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-logo h1 {
            color: #7b181b;
            font-size: 18px;
            line-height: 1.3;
            font-weight: bold;
        }

        .login-container {
            background-color: white;
            padding: 35px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .instructions {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.5;
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
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #7b181b;
            box-shadow: 0 0 0 2px rgba(123,24,27,0.1);
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
            transition: opacity 0.2s;
        }

        .btn:hover { opacity: 0.88; }
        .btn-clear { background-color: #8c3b40; }
        .btn-login { background-color: #7b181b; }

        .footer-links {
            margin-top: 18px;
            font-size: 13px;
        }

        .footer-links a {
            color: #007bff;
            text-decoration: none;
        }

        .inquiries {
            margin-top: 14px;
            font-size: 13px;
            color: #777;
            line-height: 1.6;
            text-align: center;
        }

        .test-accounts {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
        }

        .test-accounts h4 {
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
        }

        .test-accounts ul {
            margin: 0 0 12px 0;
            padding-left: 18px;
            color: #444;
        }

        .test-accounts li { margin-bottom: 4px; }

        .access-list {
            list-style: none;
            padding: 0;
            margin: 6px 0 0 0;
        }

        .access-list li {
            margin-bottom: 4px;
            color: #444;
        }
    </style>
</head>
<body>

    <!-- FIX: logo and title properly aligned and constrained to card width -->
    <div class="header-logo">
        <img src="citu_logo.png" alt="CIT-U Logo" onerror="this.style.display='none'">
        <h1>CIT University<br>Fault Report System</h1>
    </div>

    <div class="login-container">
        <p class="instructions">Login using your CIT University credentials to access the Fault Report System.</p>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
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
            Contact IT Department for account assistance
        </div>

        <!-- FIX: closed all tags properly -->
        <div class="test-accounts">
            <h4>Test Accounts:</h4>
            <ul>
                <li><strong>Admin:</strong> admin.cit / admin123</li>
                <li><strong>Employee:</strong> john.smith / admin123</li>
                <li><strong>Staff:</strong> mike.staff / admin123</li>
                <li><strong>Student:</strong> juan.student / admin123</li>
            </ul>

            <strong>User Access Levels:</strong>
            <ul class="access-list">
                <li><strong>Admin</strong> → Admin Dashboard (CRUD, Reports, Staff)</li>
                <li><strong>Staff</strong> → Staff Dashboard (Assignments, Updates)</li>
                <li><strong>Student/Employee</strong> → Submit Reports, Track Status</li>
            </ul>
        </div>
    </div>

</body>
</html>