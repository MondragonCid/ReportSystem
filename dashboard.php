<?php
require_once 'config/database.php';
include 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Count user's reports
$reports_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM damage_report WHERE ReporterID = '$user_id'";
$reports_result = mysqli_query($conn, $reports_query);
$stats = mysqli_fetch_assoc($reports_result);

// Get recent reports
$recent_query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum 
                 FROM damage_report dr
                 JOIN location l ON dr.LocationID = l.LocationID
                 WHERE dr.ReporterID = '$user_id'
                 ORDER BY dr.DateReported DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - CIT Damage Reporting System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! 👋</h1>
        <p>Manage your damage reports and track their status</p>
        
        <hr>
        
        <div style="display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap;">
            <div class="info-box" style="flex: 1; text-align: center;">
                <h3>📋 Total Reports</h3>
                <h2><?php echo $stats['total'] ?? 0; ?></h2>
            </div>
            <div class="info-box" style="flex: 1; text-align: center; background: #fff3cd;">
                <h3>⏳ Pending</h3>
                <h2><?php echo $stats['pending'] ?? 0; ?></h2>
            </div>
            <div class="info-box" style="flex: 1; text-align: center; background: #d1ecf1;">
                <h3>🔄 In Progress</h3>
                <h2><?php echo $stats['in_progress'] ?? 0; ?></h2>
            </div>
            <div class="info-box" style="flex: 1; text-align: center; background