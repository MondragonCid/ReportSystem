<?php
require_once 'config/database.php';

// --- LOGIC FROM NAVBAR.PHP ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/locations/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/staff/') !== false) {
    $base_path = '../';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIT Damage Reporting System - Home</title>
    <style>
        /* MATCHING DASHBOARD.PHP EXACTLY */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } 
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 16px; color: #555; font-weight: 500; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* ALERTS */
        .alert-banner { background-color: #e8f5e9; border-left: 5px solid #4caf50; padding: 20px; margin-bottom: 30px; border-radius: 4px; color: #2e7d32; font-size: 14px; }
        .alert-banner strong { font-size: 16px; }

        /* SECTION CARDS */
        .white-card { background: white; padding: 30px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .white-card h3 { margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; border-left: 4px solid #800000; padding-left: 15px; }
        
        .action-list { display: grid; gap: 15px; margin-top: 20px; }
        .action-item { display: flex; align-items: center; padding: 18px; background-color: #fcfcfc; border: 1px solid #eee; border-radius: 10px; text-decoration: none; color: #333; transition: all 0.3s ease; }
        .action-item:hover { background-color: #fff5f5; border-color: #800000; transform: translateX(8px); box-shadow: 0 4px 12px rgba(128,0,0,0.1); }
        .action-item .icon { font-size: 24px; margin-right: 20px; min-width: 40px; text-align: center; }
        .action-item .label { font-weight: bold; color: #800000; font-size: 16px; display: block; }
        .action-item .subtext { font-size: 13px; color: #777; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">MENU</div>
            
            <a href="<?= $base_path ?>index.php" class="nav-item active">Home</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $base_path ?>dashboard.php" class="nav-item">Dashboard</a>
                <a href="<?= $base_path ?>report_damage.php" class="nav-item">Report Damage</a>
                <a href="<?= $base_path ?>my_reports.php" class="nav-item">My Reports</a>
                
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <a href="<?= $base_path ?>admin/index.php" class="nav-item">Manage Admins</a>
                    <a href="<?= $base_path ?>locations/index.php" class="nav-item">Manage Locations</a>
                <?php endif; ?>
                
                <a href="<?= $base_path ?>logout.php" class="nav-item logout-btn">Logout</a>
            <?php else: ?>
                <a href="<?= $base_path ?>login.php" class="nav-item">Login to System</a>
            <?php endif; ?>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="<?= $base_path ?>citu_logo.png" alt="CIT-U Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="main-content">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="alert-banner">
                        <strong>Welcome back, <?= htmlspecialchars($_SESSION['fullname']); ?>!</strong><br>
                        Account Type: <?= ucfirst($_SESSION['user_type']); ?>
                    </div>
                <?php endif; ?>

                <div class="white-card">
                    <h3>About This System</h3>
                    <p>Welcome to the official CIT-U Damage Reporting tool. Please use the options below to navigate:</p>
                    
                    <div class="action-list">
                        <a href="<?= $base_path ?>report_damage.php" class="action-item">
                            <span class="icon">📝</span>
                            <div class="details">
                                <span class="label">Submit a New Report</span>
                                <span class="subtext">Report maintenance issues or equipment damage in any room.</span>
                            </div>
                        </a>

                        <a href="<?= $base_path ?>my_reports.php" class="action-item">
                            <span class="icon">📍</span>
                            <div class="details">
                                <span class="label">Track Your Submissions</span>
                                <span class="subtext">Check the real-time status and history of your reports.</span>
                            </div>
                        </a>

                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
                        <a href="<?= $base_path ?>admin/index.php" class="action-item">
                            <span class="icon">🔧</span>
                            <div class="details">
                                <span class="label">System Administration</span>
                                <span class="subtext">Manage user accounts, locations, and global reporting data.</span>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>