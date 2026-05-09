<?php
require_once 'config/database.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CIT Damage Reporting System - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1> CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
        <h2>Damage Reporting System</h2>
        
        <hr>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="alert alert-success">
                 Welcome back, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!
                <br>You are logged in as: <strong><?php echo ucfirst($_SESSION['user_type']); ?></strong>
            </div>
            
            <div style="margin: 20px 0;">
                <a href="dashboard.php" class="btn"> Go to Dashboard</a>
                <a href="report_damage.php" class="btn"> Report New Damage</a>
                <a href="my_reports.php" class="btn"> View My Reports</a>
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <a href="admin/index.php" class="btn"> Manage Admins</a>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="info-box">
                <p>Welcome to the CIT University Damage Reporting System.</p>
                <p>Please login to report issues or track your existing reports.</p>
            </div>
            
            <div style="margin: 20px 0;">
                <a href="login.php" class="btn"> Login to System</a>
            </div>
            
            <hr>
            
            <h3>Test Accounts:</h3>
            <table border="1" cellpadding="8">
                <tr>
                    <th>User Type</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Email</th>
                </tr>
                <tr>
                    <td> Admin</td>
                    <td>admin.cit</td>
                    <td>admin123</td>
                    <td>admin@cit.edu</td>
                </tr>
                <tr>
                    <td> Employee</td>
                    <td>john.smith</td>
                    <td>admin123</td>
                    <td>john.smith@cit.edu</td>
                </tr>
                <tr>
                    <td>🔧 Staff</td>
                    <td>mike.staff</td>
                    <td>admin123</td>
                    <td>mike.johnson@cit.edu</td>
                </tr>
            </table>
        <?php endif; ?>
        
        <hr>
        
        <h3>About This System</h3>
        <p>This system allows CIT University students, faculty, and staff to report damage or issues within the campus.</p>
        <ul>
            <li>📝 Submit damage reports with photos</li>
            <li>📍 Track report status in real-time</li>
            <li>🔧 Assign to maintenance staff</li>
            <li>📊 Generate reports for administration</li>
        </ul>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>