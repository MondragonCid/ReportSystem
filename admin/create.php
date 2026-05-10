<?php
require_once '../config/database.php';
require_once '../includes/validation.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Determine what we're creating: 'admin' or 'staff'
$type = isset($_GET['type']) ? $_GET['type'] : 'admin';
if (!in_array($type, ['admin', 'staff'])) $type = 'admin';

$first_name = $last_name = $username = $role_id = $department = $specialization = $contact = '';
$errors  = [];
$success = '';
$base_path = '../';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type       = mysqli_real_escape_string($conn, $_POST['type'] ?? 'admin');
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name  = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $username   = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password   = $_POST['password'];
    $role_id    = mysqli_real_escape_string($conn, trim($_POST['role_id']));

    // Type-specific fields
    $department     = mysqli_real_escape_string($conn, trim($_POST['department'] ?? ''));
    $specialization = mysqli_real_escape_string($conn, trim($_POST['specialization'] ?? ''));
    $contact        = mysqli_real_escape_string($conn, trim($_POST['contact'] ?? ''));

    // Auto-generate email
    $email = generateCITEmail($first_name, $last_name);

    // Validations
    if (empty($first_name))   $errors[] = "First name is required.";
    if (empty($last_name))    $errors[] = "Last name is required.";
    if (empty($username))     $errors[] = "Username is required.";
    if (empty($password))     $errors[] = "Password is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (empty($role_id))      $errors[] = ($type == 'staff' ? "Staff ID" : "Admin ID") . " is required.";

    // Duplicate checks
    if (mysqli_num_rows(mysqli_query($conn, "SELECT UserID FROM user WHERE Username = '$username'")) > 0)
        $errors[] = "Username '$username' is already taken.";
    if (mysqli_num_rows(mysqli_query($conn, "SELECT UserID FROM user WHERE Email = '$email'")) > 0)
        $errors[] = "Email '$email' is already in use.";

    if ($type == 'admin') {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT AdminID FROM system_administrator WHERE AdminID = '$role_id'")) > 0)
            $errors[] = "Admin ID '$role_id' already exists.";
    } else {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT StaffID FROM maintainance_staff WHERE StaffID = '$role_id'")) > 0)
            $errors[] = "Staff ID '$role_id' already exists.";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $user_type_val = ($type == 'staff') ? 'staff' : 'admin';

        // Insert into user table
        $q1 = "INSERT INTO user (Username, Password, Email, FirstName, LastName, UserType, IsActive)
               VALUES ('$username', '$hashed', '$email', '$first_name', '$last_name', '$user_type_val', 1)";

        if (mysqli_query($conn, $q1)) {
            $new_id = mysqli_insert_id($conn);

            // Insert into role table
            if ($type == 'staff') {
                $q2 = "INSERT INTO maintainance_staff (UserID, StaffID, Specialization, ContactNumber)
                       VALUES ('$new_id', '$role_id', '$specialization', '$contact')";
            } else {
                $q2 = "INSERT INTO system_administrator (UserID, AdminID, Department)
                       VALUES ('$new_id', '$role_id', '$department')";
            }

            if (mysqli_query($conn, $q2)) {
                header("Location: index.php?tab=$type&created=1");
                exit();
            } else {
                // Rollback: remove the user we just created
                mysqli_query($conn, "DELETE FROM user WHERE UserID = '$new_id'");
                $errors[] = "Role record failed: " . mysqli_error($conn);
            }
        } else {
            $errors[] = "User creation failed: " . mysqli_error($conn);
        }
    }
}

$is_staff_form = ($type == 'staff');
$title  = $is_staff_form ? 'Register New Maintenance Staff' : 'Register New Administrator';
$id_label     = $is_staff_form ? 'Staff ID' : 'Admin ID';
$id_placeholder = $is_staff_form ? 'STF002' : 'ADM002';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { min-height: 100%; font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4; display: flex; align-items: center; justify-content: center; padding: 30px 20px; }

        .card { width: 55%; min-width: 500px; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-top: 6px solid #800000; }
        .header-box { text-align: center; margin-bottom: 30px; }
        .logo { height: 70px; margin-bottom: 15px; }
        .header-box h2 { color: #800000; font-size: 20px; text-transform: uppercase; }
        .header-box p { color: #888; font-size: 13px; margin-top: 5px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 7px; color: #444; font-size: 14px; }
        .form-group input, .form-group select {
            width: 100%; padding: 11px 13px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none;
        }
        .form-group input:focus { border-color: #800000; box-shadow: 0 0 0 3px rgba(128,0,0,0.08); }
        .form-group .hint { font-size: 12px; color: #888; font-style: italic; margin-top: 4px; }

        .btn-submit { width: 100%; padding: 13px; background: #800000; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; }
        .btn-submit:hover { background: #600000; }
        .btn-cancel { display: block; text-align: center; margin-top: 12px; color: #888; text-decoration: none; font-size: 14px; }
        .btn-cancel:hover { color: #800000; }

        .alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: #fdecea; border-left: 5px solid #e53935; color: #c62828; }
        .alert-danger ul { margin-top: 6px; padding-left: 18px; }
        .alert-danger li { margin-bottom: 3px; }

        .divider { border: 0; height: 1px; background: #eee; margin: 20px 0; }
        .section-label { font-size: 12px; font-weight: bold; color: #800000; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }

        @media (max-width: 768px) { .card { width: 95%; min-width: unset; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="card">
    <div class="header-box">
        <img src="<?= $base_path ?>citu_logo.png" alt="CIT Logo" class="logo">
        <h2><?= $title ?></h2>
        <p><?= $is_staff_form ? 'This will create a staff login account and register them in the maintenance staff table.' : 'This will create an admin login account with full system access.' ?></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

        <div class="section-label">Personal Information</div>
        <div class="form-row">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
            </div>
        </div>

        <hr class="divider">
        <div class="section-label">Login Credentials</div>

        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="e.g. juan.dela" required>
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" placeholder="Minimum 6 characters" required>
            <div class="hint">The user can change this after logging in.</div>
        </div>

        <hr class="divider">
        <div class="section-label"><?= $is_staff_form ? 'Staff Details' : 'Admin Details' ?></div>

        <div class="form-row">
            <div class="form-group">
                <label><?= $id_label ?> *</label>
                <input type="text" name="role_id" value="<?= htmlspecialchars($role_id) ?>" placeholder="<?= $id_placeholder ?>" required>
            </div>

            <?php if ($is_staff_form): ?>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" value="<?= htmlspecialchars($specialization) ?>" placeholder="e.g. Electrical, Plumbing">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($department) ?>" placeholder="e.g. IT Department">
                </div>
            <?php endif; ?>
        </div>

        <?php if ($is_staff_form): ?>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact" value="<?= htmlspecialchars($contact) ?>" placeholder="e.g. 09123456789">
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">✅ Create Account</button>
        <a href="index.php?tab=<?= $type ?>" class="btn-cancel">← Return to list</a>
    </form>
</div>
</body>
</html>
