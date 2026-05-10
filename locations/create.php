<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$building_name = $room_number = '';
$errors = [];
$success = '';
$base_path = '../';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $building_name = mysqli_real_escape_string($conn, $_POST['building_name']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    if (empty($building_name)) { $errors[] = "Building name is required"; }
    if (empty($room_number)) { $errors[] = "Room/Classroom number is required"; }
    
    if (empty($errors)) {
        $check_query = "SELECT LocationID FROM location 
                        WHERE BuildingName = '$building_name' AND ClassRoomNum = '$room_number'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "This location already exists!";
        }
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO location (BuildingName, ClassRoomNum) 
                  VALUES ('$building_name', '$room_number')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Location added successfully!";
            $building_name = $room_number = ''; 
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Location - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body, html { 
            height: 100%; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f4f4; 
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* DYNAMIC WIDTH: 1/3 of the screen on desktop */
        .login-card {
            width: 35%; 
            min-width: 400px; /* Prevents it from getting too skinny */
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-top: 6px solid #800000;
            transition: width 0.3s ease;
        }

        .header-box { text-align: center; margin-bottom: 35px; }
        .logo { height: 85px; margin-bottom: 20px; }
        .header-box h2 { color: #800000; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .header-box p { color: #666; font-size: 15px; margin-top: 5px; }

        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 10px; color: #444; font-size: 15px; }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
        }
        .form-group input:focus { border-color: #800000; box-shadow: 0 0 8px rgba(128,0,0,0.1); }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background-color: #800000;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: 15px;
        }
        .btn-submit:hover { background-color: #600000; transform: translateY(-2px); }
        
        .btn-cancel {
            display: block;
            text-align: center;
            text-decoration: none;
            color: #888;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-cancel:hover { color: #800000; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .example-box {
            margin-top: 30px;
            background: #fafafa;
            border: 1px dashed #bbb;
            padding: 20px;
            border-radius: 10px;
            font-size: 13px;
            color: #777;
            line-height: 1.6;
        }

        /* RESPONSIVENESS for smaller screens */
        @media (max-width: 1200px) {
            .login-card { width: 50%; }
        }
        @media (max-width: 768px) {
            .login-card { width: 90%; min-width: unset; padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header-box">
            <img src="<?= $base_path ?>citu_logo.png" alt="CIT Logo" class="logo">
            <h2>Add New Location</h2>
            <p>Admin Maintenance Portal</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?>
                    ❌ <?= $error; ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Building Name</label>
                <input type="text" name="building_name" 
                       value="<?= htmlspecialchars($building_name); ?>" 
                       placeholder="e.g., Main Building" required>
            </div>
            
            <div class="form-group">
                <label>Room / Classroom Number</label>
                <input type="text" name="room_number" 
                       value="<?= htmlspecialchars($room_number); ?>" 
                       placeholder="e.g., 101" required>
            </div>
            
            <button type="submit" class="btn-submit">Add Location</button>
            <a href="index.php" class="btn-cancel">← Back to Location List</a>
        </form>

        <div class="example-box">
            <strong>📌 Format Examples:</strong><br>
            • Main Building, 101<br>
            • Science Center, Lab 4<br>
            • CIT Gym, Court B
        </div>
    </div>

</body>
</html>