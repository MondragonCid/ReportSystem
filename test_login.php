<?php
$conn = mysqli_connect('localhost', 'root', '', 'cit_reporting_system');

echo "<h1>Login Test</h1>";

// Get all users and test their passwords
$users = mysqli_query($conn, "SELECT UserID, Username, Password FROM user");

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Username</th><th>Password Hash (first 30 chars)</th><th>Test with 'admin123'</th></tr>";

while ($user = mysqli_fetch_assoc($users)) {
    $works = password_verify('admin123', $user['Password']) ? "✅ YES" : "❌ NO";
    echo "<tr>";
    echo "<td>" . $user['Username'] . "</td>";
    echo "<td><code>" . substr($user['Password'], 0, 30) . "...</code></td>";
    echo "<td>" . $works . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Manual test for admin.cit:</h3>";

$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE Username='admin.cit'"));
if ($admin) {
    if (password_verify('admin123', $admin['Password'])) {
        echo "<p style='color:green'>✅ admin.cit with password 'admin123' works!</p>";
    } else {
        echo "<p style='color:red'>❌ admin.cit with password 'admin123' does NOT work</p>";
        echo "<p>Current hash: " . $admin['Password'] . "</p>";
    }
}
?>