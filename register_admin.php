<?php
require_once 'config/database.php';

// Check if admin already exists
$check = mysqli_query($conn, "SELECT * FROM user WHERE Username = 'admin.cit'");
if (mysqli_num_rows($check) > 0) {
    // Delete existing admin
    mysqli_query($conn, "DELETE FROM system_administrator WHERE UserID = (SELECT UserID FROM user WHERE Username = 'admin.cit')");
    mysqli_query($conn, "DELETE FROM user WHERE Username = 'admin.cit'");
    echo "<p>Removed existing admin...</p>";
}

// Create new admin with working password
$username = 'admin.cit';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@cit.edu';
$firstname = 'System';
$lastname = 'Administrator';
$usertype = 'admin';

$query = "INSERT INTO user (Username, Password, Email, FirstName, LastName, UserType, IsActive) 
          VALUES ('$username', '$password', '$email', '$firstname', '$lastname', '$usertype', 1)";

if (mysqli_query($conn, $query)) {
    $user_id = mysqli_insert_id($conn);
    
    // Add to system_administrator table
    $admin_id = 'ADM001';
    $department = 'IT Department';
    $query2 = "INSERT INTO system_administrator (UserID, AdminID, Department) VALUES ('$user_id', '$admin_id', '$department')";
    
    if (mysqli_query($conn, $query2)) {
        echo "<h2 style='color:green'>✅ SUCCESS! Admin created!</h2>";
        echo "<p>Username: <strong>admin.cit</strong></p>";
        echo "<p>Password: <strong>admin123</strong></p>";
        echo "<a href='login.php'>Go to Login Page →</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>