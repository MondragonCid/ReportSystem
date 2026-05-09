<?php

echo "<h1> password hash generator testing";

$password = "admin123";

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<p>Password: <strong>admin123</strong></p>";
echo "<p>Hash: <code>" . $hash .  "</code></p>";
echo "<hr>";

if(password_verify($password, $hash)){
    echo "<p style='color:green'> Verification Successss! Hash works now. </p>";
} else {
    echo "<p style = 'color:red> Verification Failed</p>";
}

echo "<hr>";
echo "<h2>Copy these SQL commands to phpMyAdmin:</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";
echo "-- Update admin password\n";
echo "UPDATE `user` SET `Password` = '$hash' WHERE `Username` = 'admin.cit';\n\n";
echo "-- Update employee password\n";
echo "UPDATE `user` SET `Password` = '$hash' WHERE `Username` = 'john.smith';\n\n";
echo "-- Update staff password\n";
echo "UPDATE `user` SET `Password` = '$hash' WHERE `Username` = 'mike.staff';\n";
echo "</pre>";
?>
