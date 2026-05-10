<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$base_path = '';

// --- HANDLE FORM SUBMISSION ---
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category    = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $location_id = mysqli_real_escape_string($conn, trim($_POST['location_id'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

    if (empty($category) || empty($location_id) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        $insert = "INSERT INTO damage_report (ReporterID, LocationID, Category, Description, Status, DateReported)
                   VALUES ('$user_id', '$location_id', '$category', '$description', 'pending', NOW())";
        if (mysqli_query($conn, $insert)) {
            header('Location: my_reports.php?success=1');
            exit();
        } else {
            $error = 'Failed to submit report: ' . mysqli_error($conn);
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

        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .section h2 { margin-bottom: 20px; color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-box { margin-bottom: 20px; }
        .input-box label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 14px; color: #444; }

        select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; background: #fff; }

        .btn { display: inline-block; padding: 12px 25px; background: #800000; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
        .alert-error { background: #fdecea; border-left: 5px solid #e53935; padding: 15px; border-radius: 4px; color: #c62828; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="main-wrapper">

        <?php include 'includes/sidebar.php'; ?>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System</p>
                </div>
            </div>
            <hr class="red-line">

            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="section">
                <h2>➕ Report New Damage</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="input-box">
                            <label>Category</label>
                            <select name="category" required>
    <option value="">Select Category</option>
    
    
    <!-- Electrical & Utilities -->
    <option value="Electrical - Lights">Electrical - Lights</option>
    <option value="Electrical - Outlets/Sockets">Electrical - Outlets/Sockets</option>
    <option value="Electrical - Fans">Electrical - Fans</option>
    <option value="Electrical - Air Conditioning">Electrical - Air Conditioning</option>
    <option value="Electrical - Wiring">Electrical - Wiring</option>

    <!-- Plumbing -->
    <option value="Plumbing - Faucets">Plumbing - Faucets</option>
    <option value="Plumbing - Toilets">Plumbing - Toilets</option>
    <option value="Plumbing - Pipes/Leaks">Plumbing - Pipes/Leaks</option>
    <option value="Plumbing - Drainage">Plumbing - Drainage</option>

    <!-- Furniture -->
    <option value="Furniture - Chairs">Furniture - Chairs</option>
    <option value="Furniture - Tables/Desks">Furniture - Tables/Desks</option>
    <option value="Furniture - Cabinets/Shelves">Furniture - Cabinets/Shelves</option>
    <option value="Furniture - Doors">Furniture - Doors</option>
    <option value="Furniture - Windows">Furniture - Windows</option>

    <!-- IT & Equipment -->
    <option value="IT - Computer/Desktop">IT - Computer/Desktop</option>
    <option value="IT - Projector">IT - Projector</option>
    <option value="IT - Printer/Scanner">IT - Printer/Scanner</option>
    <option value="IT - Network/WiFi">IT - Network/WiFi</option>
    <option value="IT - TV/Monitor">IT - TV/Monitor</option>

    <!-- Structural -->
    <option value="Structural - Floor">Structural - Floor</option>
    <option value="Structural - Ceiling">Structural - Ceiling</option>
    <option value="Structural - Walls">Structural - Walls</option>
    <option value="Structural - Stairs">Structural - Stairs</option>

    <!-- Others -->
    <option value="Janitorial/Cleanliness">Janitorial/Cleanliness</option>
    <option value="Safety Hazard">Safety Hazard</option>
    <option value="Other">Other</option>
</select>


                        </div>
                        <div class="input-box">
                            <label>Location</label>
                            <select name="location_id" required>
                                <option value="">Select Building &amp; Room</option>
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
                        <textarea name="description" rows="5" required placeholder="Provide details about the damage..."></textarea>
                    </div>
                    <button type="submit" class="btn">SUBMIT REPORT</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
