<?php
// This script will FIX all passwords to work perfectly
$conn = mysqli_connect('localhost', 'root', '', 'cit_reporting_system');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h1>🔧 Fixing Passwords for CIT System</h1>";

// Password to use for ALL accounts
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<p>Using password: <strong>admin123</strong></p>";
echo "<p>Generated hash: <code>" . $hashed_password . "</code></p>";
echo "<hr>";

// First, check what users exist
$result = mysqli_query($conn, "SELECT UserID, Username, UserType FROM user");
echo "<h3>📋 Current users:</h3>";
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>" . $row['Username'] . " (" . $row['UserType'] . ")</li>";
}
echo "</ul>";

// UPDATE ALL PASSWORDS to the correct hash
$update_query = "UPDATE user SET Password = '$hashed_password'";
if (mysqli_query($conn, $update_query)) {
    echo "<p style='color:green; font-size:18px;'>✅ Updated " . mysqli_affected_rows($conn) . " user passwords!</p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
}

// Verify the update worked
$verify = mysqli_query($conn, "SELECT Username, Password FROM user LIMIT 1");
$test = mysqli_fetch_assoc($verify);

echo "<hr>";
echo "<h3>🔐 Testing the password:</h3>";

if (password_verify('admin123', $test['Password'])) {
    echo "<p style='color:green; font-size:20px;'>✅ SUCCESS! Password 'admin123' works with hash: " . substr($test['Password'], 0, 30) . "...</p>";
} else {
    echo "<p style='color:red; font-size:20px;'>❌ Still not working. Let's try another approach...</p>";
    
    // Alternative approach - force update with known working hash
    $known_good_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    mysqli_query($conn, "UPDATE user SET Password = '$known_good_hash'");
    
    $test2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Password FROM user LIMIT 1"));
    if (password_verify('admin123', $test2['Password'])) {
        echo "<p style='color:green;'>✅ Fixed with known good hash!</p>";
    }
}

echo "<hr>";
echo "<h3>🎯 Now try logging in:</h3>";
echo "<p><strong>Username:</strong> admin.cit<br>";
echo "<strong>Password:</strong> admin123</p>";
echo "<a href='login.php' style='display:inline-block; background:#007bff; color:white; padding:10px 20px; text-decoration:none; margin-top:10px;'>Go to Login →</a>";
?>