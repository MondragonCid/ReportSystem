<?php
/**
 * Database Configuration File
 * Connects PHP to MySQL database
 */

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'cit_reporting_system';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>