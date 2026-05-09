<?php
require_once 'config/database.php';

$username = 'admin.cit';
$entered_password = 'admin123';

$query = "SELECT * FROM user WHERE Username = '$username'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo "<h2>Password Test Results:</h2>";
    echo "Username: " . $row['Username'] . "<br>";
    echo "Stored Hash: " . $row['Password'] . "<br>";
    echo "Entered Password: " . $entered_password . "<br>";
    
    if (password_verify($entered_password, $row['Password'])) {
        echo "<p style='color:green; font-size:20px;'> PASSWORD IS CORRECT! Login should work.</p>";
    } else {
        echo "<p style='color:red; font-size:20px;'> PASSWORD IS INCORRECT! Need to reset password.</p>";
    }
} else {
    echo "User not found!";
}
?>