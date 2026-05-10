<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$query = "SELECT * FROM location ORDER BY BuildingName, ClassRoomNum";
$result = mysqli_query($conn, $query);

$base_path = '../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
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
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section h2 { color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-weight: bold; font-size: 14px; color: #333; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #555; }
        .data-table tr:hover { background: #fcfcfc; }

        .btn { display: inline-block; padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-add { background-color: #800000; color: white; }
        .btn-add:hover { background-color: #600000; }
        .btn-edit { background-color: #ffc107; color: #000; margin-right: 5px; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #a71d2a; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <nav class="sidebar">
            <div class="sidebar-brand">ADMIN PANEL</div>
            <a href="<?= $base_path ?>index.php" class="nav-item">Home</a>
            <a href="<?= $base_path ?>dashboard.php" class="nav-item">Dashboard</a>
            <a href="<?= $base_path ?>report_damage.php" class="nav-item">Report Damage</a>
            <a href="<?= $base_path ?>my_reports.php" class="nav-item">My Reports</a>
            <a href="<?= $base_path ?>admin/index.php" class="nav-item">Manage Admins</a>
            <a href="<?= $base_path ?>locations/index.php" class="nav-item active">Manage Locations</a>
            <a href="<?= $base_path ?>logout.php" class="nav-item logout-btn">Logout</a>
        </nav>

        <main class="content-area">
            <div class="header-container">
                <img src="<?= $base_path ?>citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>System Administration | Location Management</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="section">
                <div class="section-header">
                    <h2>📍 Campus Locations</h2>
                    <a href="create.php" class="btn btn-add">➕ Add New Location</a>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Location ID</th>
                            <th>Building Name</th>
                            <th>Room / Classroom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($location = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $location['LocationID']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($location['BuildingName']); ?></td>
                                    <td><?php echo htmlspecialchars($location['ClassRoomNum']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $location['LocationID']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete.php?id=<?php echo $location['LocationID']; ?>" 
                                           class="btn btn-delete" 
                                           onclick="return confirm('Delete this location? It may affect existing reports linked to this room.')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px;">No campus locations registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>