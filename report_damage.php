<?php
define('NAVBAR_LOGIC_ONLY', true); 
require_once 'config/database.php';
include 'includes/navbar.php'; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get locations for dropdown
$locations_query = "SELECT * FROM location ORDER BY BuildingName, ClassRoomNum";
$locations_result = mysqli_query($conn, $locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - CIT University</title>
    <style>
        /* MATCHING DASHBOARD.PHP EXACTLY */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } 
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 16px; color: #555; font-weight: 500; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* FORM BOX DESIGN */
        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .section h2 { margin-bottom: 20px; color: #333; border-left: 4px solid #800000; padding-left: 15px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-box { margin-bottom: 20px; }
        .input-box label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 14px; color: #444; }
        
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: #fff;
        }

        .btn { display: inline-block; padding: 12px 25px; background: #800000; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">STAFF PANEL</div>
            <a href="index.php" class="nav-item">Home</a>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="report_damage.php" class="nav-item active">Report Damage</a>
            <a href="my_reports.php" class="nav-item">My Reports</a>
            <a href="logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="section">
                <h2>➕ Report New Damage</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="input-box">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="">Select Category</option>
                                <option>Electrical</option>
                                <option>Furniture</option>
                                <option>IT Equipment</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <label>Location</label>
                            <select name="location_id" required>
                                <option value="">Select Building & Room</option>
                                <?php while($loc = mysqli_fetch_assoc($locations_result)): ?>
                                    <option value="<?php echo $loc['LocationID']; ?>">
                                        <?php echo htmlspecialchars($loc['BuildingName'] . ' - ' . $loc['ClassRoomNum']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="input-box">
                        <label>Detailed Description</label>
                        <textarea name="description" rows="5" required placeholder="Provide details..."></textarea>
                    </div>
                    <button type="submit" class="btn">SUBMIT REPORT</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>