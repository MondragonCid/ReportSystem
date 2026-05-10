<?php
require_once 'config/database.php';
include 'includes/navbar.php'; 

// Ensure session is started for user tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Handle the form submission logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location_id = mysqli_real_escape_string($conn, $_POST['location_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    if (empty($location_id) || empty($category) || empty($description)) {
        $error = "Please fill in all required fields";
    } else {
        $query = "INSERT INTO damage_report (ReporterID, LocationID, Category, Description, Status, DateReported) 
                  VALUES ('$user_id', '$location_id', '$category', '$description', 'pending', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $report_id = mysqli_insert_id($conn);
            $success = "✅ Report submitted successfully! Reference #: " . $report_id;
        } else {
            $error = "❌ Failed to submit report: " . mysqli_error($conn);
        }
    }
}

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
        /* CORE LAYOUT - ZERO FOOTPRINT RESET */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f4f4; overflow: hidden; }
        
        .main-wrapper { 
            display: flex; 
            height: 100vh; /* Viewport height for full vertical coverage */
            width: 100vw;
        }

        /* SIDEBAR - Fixed height coverage */
        .sidebar { 
            width: 260px; 
            background-color: #800000; 
            color: white; 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
            flex-shrink: 0; 
        }
        
        .sidebar-brand { padding: 25px; font-weight: bold; text-align: center; background-color: #600000; font-size: 1.1rem; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; }
        .logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); }

        /* CONTENT AREA */
        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; }
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        /* FORM BOX DESIGN */
        .info-entry-card {
            background: #d9d9d9; 
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .input-group { background: white; padding: 15px; border-radius: 8px; border: 1px solid #ccc; }
        .input-group label { display: block; font-weight: bold; margin-bottom: 10px; color: #333; }
        
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 14px;
            outline: none;
        }

        .description-box { grid-column: span 2; background: white; padding: 15px; border-radius: 8px; border: 1px solid #ccc; }
        .btn-container { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        
        .btn-submit { background-color: #800000; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #a00000; }
        .btn-back { background-color: #600000; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-size: 14px; text-align: center; }
        
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand"><?php echo strtoupper($user_type); ?> PANEL</div>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="report_damage.php" class="nav-item active">Report Issue</a>
            <a href="my_reports.php" class="nav-item">View My Reports</a>
            <a href="logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System | Information Entry</p>
                </div>
            </div>
            <hr class="red-line">

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="info-entry-card">
                <h3 style="margin-bottom: 20px; color: #444;">Input Information of Related Issues</h3>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="input-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="">Select Category</option>
                                <option>Electrical (lights, outlets, fans, ACU)</option>
                                <option>Furniture (chairs, tables, cabinets)</option>
                                <option>IT Equipment (computers, projectors, printers)</option>
                                <option>Plumbing (faucets, toilets, pipes)</option>
                                <option>Structural (walls, ceilings, floors)</option>
                                <option>Other Facilities</option>
                            </select>
                        </div>

                        <div class="input-group">
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

                        <div class="description-box">
                            <label>Description</label>
                            <textarea id="descriptionInput" name="description" rows="6" maxlength="500" required placeholder="Describe the damage or issue in detail..."></textarea>
                            <div id="charCounter" style="text-align: right; font-size: 12px; color: #666; margin-top: 5px;">0 / 500</div>
                        </div>
                    </div>

                    <div class="btn-container">
                        <a href="dashboard.php" class="btn-back">PREVIOUS</a>
                        <button type="submit" class="btn-submit">SUBMIT REPORT</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const descriptionInput = document.getElementById('descriptionInput');
        const charCounter = document.getElementById('charCounter');

        descriptionInput.addEventListener('input', function() {
            const currentLength = descriptionInput.value.length;
            charCounter.textContent = `${currentLength} / 500`;

            // Change color to CIT Maroon if limit is reached
            if (currentLength >= 500) {
                charCounter.style.color = "#800000";
                charCounter.style.fontWeight = "bold";
            } else {
                charCounter.style.color = "#666";
                charCounter.style.fontWeight = "normal";
            }
        });
    </script>
</body>
</html>