<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$base_path = '';

$query = "SELECT dr.*, l.BuildingName, l.ClassRoomNum
          FROM damage_report dr
          JOIN location l ON dr.LocationID = l.LocationID
          WHERE dr.ReporterID = '$user_id'
          ORDER BY dr.DateReported DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .main-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
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
        .section h2 { margin-bottom: 20px; color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-weight: bold; font-size: 14px; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .data-table tr:hover { background: #f5f5f5; }

        .status-pending   { color: #f39c12; font-weight: bold; }
        .status-in-progress { color: #3498db; font-weight: bold; }
        .status-resolved  { color: #27ae60; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }

        .empty-msg { text-align: center; padding: 40px; color: #888; font-size: 15px; }
        .btn { display: inline-block; padding: 10px 20px; background: #800000; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }
        .alert-success { background: #e8f5e9; border-left: 5px solid #4caf50; padding: 15px; border-radius: 4px; color: #2e7d32; margin-bottom: 20px; }
        
        /* Pagination Styles */
        .pagination-container { display: none; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .pagination-info { font-size: 14px; color: #555; font-weight: 500; }
        .btn:disabled { background: #cccccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="main-wrapper">

        <?php include 'includes/sidebar.php'; ?>

        <main class="content-area">
            <div class="header-container">
                <img src="citu_logo.png" alt="Logo" class="logo">
                <div class="header-text">
                    <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                    <p>Damage Reporting System</p>
                </div>
            </div>
            <hr class="red-line">

            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success">✅ Your report was submitted successfully!</div>
            <?php endif; ?>

            <div class="section">
                <h2>📋 My Report History</h2>
                <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="data-table" id="reportsTable">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($report = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong>#<?php echo $report['ReportID']; ?></strong></td>
                            <td><?php echo htmlspecialchars($report['BuildingName'] . ' - ' . $report['ClassRoomNum']); ?></td>
                            <td><?php echo htmlspecialchars($report['Category']); ?></td>
                            <td><?php echo htmlspecialchars(substr($report['Description'], 0, 60)) . (strlen($report['Description']) > 60 ? '...' : ''); ?></td>
                            <td><?php echo date('M d, Y', strtotime($report['DateReported'])); ?></td>
                            <td class="status-<?php echo $report['Status']; ?>">
                                <?php echo ucfirst(str_replace('-', ' ', $report['Status'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="pagination-container" id="paginationControls">
                    <button class="btn" id="prevBtn">Previous</button>
                    <span class="pagination-info" id="pageInfo">Page 1 of 1</span>
                    <button class="btn" id="nextBtn">Next</button>
                </div>

                <?php else: ?>
                    <div class="empty-msg">
                        <p>📭 You have no reports yet.</p>
                        <br>
                        <a href="report_damage.php" class="btn">➕ Submit Your First Report</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById('reportsTable');
            if (!table) return; // Exit if no table is present (empty state)

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const rowsPerPage = 8; // Change this number to show more/less rows per page
            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / rowsPerPage);

            const paginationControls = document.getElementById('paginationControls');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const pageInfo = document.getElementById('pageInfo');

            // Only show pagination if there is more than 1 page
            if (totalPages > 1) {
                paginationControls.style.display = 'flex';
            }

            function displayRows() {
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                // Loop through all rows and hide/show based on current page
                rows.forEach((row, index) => {
                    if (index >= start && index < end) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update text and button states
                pageInfo.innerText = `Page ${currentPage} of ${totalPages}`;
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages;
            }

            // Button Event Listeners
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows();
                }
            });

            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayRows();
                }
            });

            // Initialize the first view
            displayRows();
        });
    </script>
</body>
</html>