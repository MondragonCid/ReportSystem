<?php
require_once '../config/database.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$base_path = '../';

// Handle staff assignment AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_staff'])) {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $staff_id  = $_POST['staff_id'];

    if (!empty($staff_id)) {
        $q = "UPDATE damage_report SET StaffID = '$staff_id', Status = 'in-progress' WHERE ReportID = '$report_id'";
    } else {
        $q = "UPDATE damage_report SET StaffID = NULL WHERE ReportID = '$report_id'";
    }

    if (mysqli_query($conn, $q)) {
        header("Location: dashboard.php?msg=assigned");
        exit();
    } else {
        $error = "Assignment failed: " . mysqli_error($conn);
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $staff_id  = $_POST['staff_id'] ?? '';

    $update_query = "UPDATE damage_report SET Status = '$new_status'";
    if ($new_status == 'resolved') {
        $update_query .= ", DateResolved = NOW()";
    } else {
        $update_query .= ", DateResolved = NULL";
    }
    if (!empty($staff_id)) {
        $update_query .= ", StaffID = '$staff_id'";
    } else {
        $update_query .= ", StaffID = NULL";
    }
    $update_query .= " WHERE ReportID = '$report_id'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: dashboard.php?msg=updated");
        exit();
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $report_id = $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM damage_report WHERE ReportID = '$report_id'")) {
        header("Location: dashboard.php?msg=deleted");
        exit();
    }
}

// Stats
$stats_query = "SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN Status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN Status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM damage_report";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// ORIGINAL PHP QUERY UNTOUCHED
$reports_query = "SELECT dr.*, 
                  CONCAT(u.FirstName, ' ', u.LastName) as ReporterName,
                  CONCAT(l.BuildingName, ' - ', l.ClassRoomNum) as LocationName,
                  CONCAT(s.FirstName, ' ', s.LastName) as StaffName,
                  ms.UserID as StaffUserID
                  FROM damage_report dr
                  JOIN user u ON dr.ReporterID = u.UserID
                  JOIN location l ON dr.LocationID = l.LocationID
                  LEFT JOIN maintainance_staff ms ON dr.StaffID = ms.UserID
                  LEFT JOIN user s ON ms.UserID = s.UserID
                  ORDER BY dr.DateReported DESC";
$reports_result = mysqli_query($conn, $reports_query);

// Staff list
$staff_query = "SELECT ms.UserID, CONCAT(u.FirstName, ' ', u.LastName) as StaffName, ms.Specialization
                FROM maintainance_staff ms
                JOIN user u ON ms.UserID = u.UserID
                WHERE u.IsActive = 1";
$staff_result = mysqli_query($conn, $staff_query);
$staff_list = [];
while ($staff = mysqli_fetch_assoc($staff_result)) {
    $staff_list[] = $staff;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CIT Damage Reporting</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; overflow-x: hidden; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; flex-shrink: 0; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; }
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        .content-area { flex: 1; padding: 40px; overflow-y: auto; min-width: 0; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; }
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 15px; color: #555; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; }
        .stat-card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-card.pending .stat-number { color: #f39c12; }
        .stat-card.progress .stat-number { color: #3498db; }
        .stat-card.resolved .stat-number { color: #27ae60; }

        .filter-bar { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; display: flex; gap: 10px; border: 1px solid #ddd; }
        .filter-bar input, .filter-bar select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }

        /* SCROLL-PROOF UNIFORM TABLE CONFIGURATION */
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); table-layout: fixed; }
        .data-table th { background: #f8f9fa; padding: 12px 10px; text-align: left; border-bottom: 2px solid #eee; font-size: 13px; }
        .data-table td { padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word; }
        .data-table tr:hover { background: #fafafa; }

        /* Custom Sizing Matrix Safeguarding Actions Field */
        .col-id { width: 4%; }
        .col-reporter { width: 12%; }
        .col-location { width: 14%; }
        .col-category { width: 16%; }
        .col-desc { width: 11%; }
        .col-staff { width: 14%; }
        .col-status { width: 10%; }
        .col-actions { width: 19%; } 

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; color: white; display: inline-block; text-align: center; width: 100%; }
        .badge-pending   { background: #f39c12; }
        .badge-progress  { background: #3498db; }
        .badge-resolved  { background: #27ae60; }
        .badge-cancelled { background: #e74c3c; }

        .btn-assign { background: #800000; color: white; border: none; padding: 8px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; white-space: nowrap; display: block; width: 100%; text-align: center; text-overflow: ellipsis; overflow: hidden; transition: 0.2s; }
        .btn-assign:hover { background: #600000; }
        .btn-assign.assigned { background: #28a745; }
        .btn-assign.assigned:hover { background: #218838; }

        .btn-save   { background: #28a745; color: white; border: none; padding: 7px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; flex-shrink: 0; transition: 0.2s; }
        .btn-save:hover { background: #218838; }
        .btn-delete { background: #dc3545; color: white; border: none; padding: 7px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; display: inline-block; flex-shrink: 0; transition: 0.2s; }
        .btn-delete:hover { background: #c82333; }

        .alert-success { background: #e8f5e9; border-left: 5px solid #4caf50; padding: 12px 15px; border-radius: 4px; color: #2e7d32; margin-bottom: 20px; }

        /* MODAL styles */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal { background: white; border-radius: 10px; padding: 30px; width: 440px; max-width: 95vw; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .modal h3 { color: #800000; margin-bottom: 5px; font-size: 18px; }
        .modal .modal-sub { color: #777; font-size: 13px; margin-bottom: 20px; }
        .modal-divider { border: 0; height: 1px; background: #eee; margin: 15px 0; }

        .staff-option { display: flex; align-items: center; padding: 12px; border: 2px solid #eee; border-radius: 8px; margin-bottom: 8px; cursor: pointer; transition: 0.2s; }
        .staff-option:hover { border-color: #800000; background: #fff5f5; }
        .staff-option.selected { border-color: #800000; background: #fff5f5; }
        .staff-option input[type="radio"] { margin-right: 12px; accent-color: #800000; width: 16px; height: 16px; }
        .staff-option .staff-name { font-weight: bold; font-size: 14px; color: #333; }
        .staff-option .staff-spec { font-size: 12px; color: #888; margin-top: 2px; }

        .unassign-option { display: flex; align-items: center; padding: 10px 12px; border: 2px solid #eee; border-radius: 8px; margin-bottom: 15px; cursor: pointer; transition: 0.2s; }
        .unassign-option:hover { border-color: #999; background: #f9f9f9; }
        .unassign-option input[type="radio"] { margin-right: 12px; width: 16px; height: 16px; }
        .unassign-option span { font-size: 13px; color: #888; font-style: italic; }

        .modal-actions { display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end; }
        .btn-modal-save   { background: #800000; color: white; border: none; padding: 10px 22px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none; text-align: center; }
        .btn-modal-save:hover { background: #600000; }
        .btn-modal-delete { background: #dc3545; color: white; border: none; padding: 10px 22px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none; text-align: center; }
        .btn-modal-delete:hover { background: #c82333; }
        .btn-modal-cancel { background: #eee; color: #333; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-modal-cancel:hover { background: #ddd; }

        .pagination-container { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; }
        .btn-page { background: #800000; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; transition: 0.2s; }
        .btn-page:hover { background: #600000; }
        .btn-page:disabled { background: #ccc; cursor: not-allowed; }
        .page-info { font-size: 14px; color: #555; font-weight: bold; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <main class="content-area">
        <div class="header-container">
            <img src="<?= $base_path ?>citu_logo.png" alt="CIT-U Logo" class="logo">
            <div class="header-text">
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>! System Overview</p>
            </div>
        </div>
        <hr class="red-line">

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'assigned'): ?>
                <div class="alert-success">✅ Staff assigned successfully!</div>
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="alert-success">✅ Report status updated!</div>
            <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <div class="alert-success">🗑️ Report deleted.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>📋 Total</h3>
                <div class="stat-number"><?= $stats['total_reports'] ?? 0 ?></div>
            </div>
            <div class="stat-card pending">
                <h3>⏳ Pending</h3>
                <div class="stat-number"><?= $stats['pending'] ?? 0 ?></div>
            </div>
            <div class="stat-card progress">
                <h3>🔄 In Progress</h3>
                <div class="stat-number"><?= $stats['in_progress'] ?? 0 ?></div>
            </div>
            <div class="stat-card resolved">
                <h3>✅ Resolved</h3>
                <div class="stat-number"><?= $stats['resolved'] ?? 0 ?></div>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 10px; border: 1px solid #ddd; width: 100%;">
            <h2 style="margin-bottom: 15px;">📋 All Damage Reports</h2>
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Search reports..." onkeyup="filterTable()" style="flex: 1;">
                <select id="statusFilter" onchange="filterTable()">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <table class="data-table" id="reportsTable">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-reporter">Reporter</th>
                        <th class="col-location">Location</th>
                        <th class="col-category">Category</th>
                        <th class="col-desc">Description</th>
                        <th class="col-staff">Assigned Staff</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (mysqli_num_rows($reports_result) > 0): ?>
                        <?php while($report = mysqli_fetch_assoc($reports_result)): ?>
                            <tr class="report-row" data-status="<?= $report['Status'] ?>">
                                <td><strong>#<?= $report['ReportID'] ?></strong></td>
                                <td><?= htmlspecialchars($report['ReporterName']) ?></td>
                                <td><?= htmlspecialchars($report['LocationName']) ?></td>
                                <td><?= htmlspecialchars($report['Category']) ?></td>
                                <td><?= htmlspecialchars(substr($report['Description'], 0, 35)) ?>...</td>

                                <td>
                                    <?php if ($report['StaffName']): ?>
                                        <button class="btn-assign assigned" title="Click to reassign"
                                            onclick="openAssignModal(<?= $report['ReportID'] ?>, '<?= htmlspecialchars($report['StaffName']) ?>', <?= $report['StaffUserID'] ?? 'null' ?>)">
                                            👤 <?= htmlspecialchars($report['StaffName']) ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-assign"
                                            onclick="openAssignModal(<?= $report['ReportID'] ?>, null, null)">
                                            Assign Staff
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge badge-<?= $report['Status'] == 'in-progress' ? 'progress' : $report['Status'] ?>">
                                        <?= ucfirst(str_replace('-', ' ', $report['Status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <form method="POST" style="display: flex; gap: 5px; align-items: center; width: 100%;">
                                        <input type="hidden" name="report_id" value="<?= $report['ReportID'] ?>">
                                        <input type="hidden" name="staff_id" value="<?= $report['StaffUserID'] ?? '' ?>">
                                        <select name="status" style="font-size: 11px; padding: 5px 2px; border: 1px solid #ddd; border-radius: 4px; flex: 1; min-width: 0;">
                                            <option value="pending"     <?= $report['Status'] == 'pending'     ? 'selected' : '' ?>>Pending</option>
                                            <option value="in-progress" <?= $report['Status'] == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="resolved"    <?= $report['Status'] == 'resolved'    ? 'selected' : '' ?>>Resolved</option>
                                            <option value="cancelled"   <?= $report['Status'] == 'cancelled'   ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-save">Save</button>
                                        
                                        <button type="button" class="btn-delete" onclick="openDeleteModal(<?= $report['ReportID'] ?>)">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="pagination-container">
                <button class="btn-page" id="btnPrev" onclick="prevPage()">❮ Previous</button>
                <span id="pageInfo" class="page-info">Page 1 of 1</span>
                <button class="btn-page" id="btnNext" onclick="nextPage()">Next ❯</button>
            </div>

        </div>
    </main>
</div>

<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <h3>👤 Assign Staff</h3>
        <p class="modal-sub" id="modalSubtitle">Select a staff member to assign to this report.</p>
        <hr class="modal-divider">

        <form method="POST" id="assignForm">
            <input type="hidden" name="report_id" id="modal_report_id">
            <input type="hidden" name="assign_staff" value="1">

            <label class="unassign-option">
                <input type="radio" name="staff_id" value="" id="radio_unassign">
                <span>Remove assignment (Unassigned)</span>
            </label>

            <?php foreach ($staff_list as $staff): ?>
                <label class="staff-option" onclick="selectStaff(this)">
                    <input type="radio" name="staff_id" value="<?= $staff['UserID'] ?>" data-id="<?= $staff['UserID'] ?>">
                    <div>
                        <div class="staff-name"><?= htmlspecialchars($staff['StaffName']) ?></div>
                        <div class="staff-spec">Specialization: <?= htmlspecialchars($staff['Specialization'] ?? 'General') ?></div>
                    </div>
                </label>
            <?php endforeach; ?>

            <?php if (empty($staff_list)): ?>
                <p style="color:#888; text-align:center; padding: 15px;">No staff accounts found in the system.</p>
            <?php endif; ?>

            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn-modal-save">✅ Confirm Assignment</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="width: 380px; text-align: center;">
        <h3 style="color: #dc3545; font-size: 22px;">Delete Report?</h3>
        <p class="modal-sub" id="deleteModalText" style="margin-top: 15px; font-size: 14px; color: #555;">Are you sure you want to delete this report? This action cannot be undone.</p>
        <hr class="modal-divider" style="margin: 20px 0;">
        
        <div class="modal-actions" style="justify-content: center; gap: 15px; margin-top: 0;">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn-modal-delete">Yes, Delete It</a>
        </div>
    </div>
</div>

<script>
    // Exact status hierarchy mapping matrix 
    const statusPriority = { 'pending': 1, 'in-progress': 2, 'resolved': 3, 'cancelled': 4 };

    const rowsPerPage = 5; 
    let currentPage = 1;
    let masterRowsArray = [];
    let filteredRows = [];

    document.addEventListener('DOMContentLoaded', () => {
        // Capture ALL rows printed out by PHP into a persistent master array
        masterRowsArray = Array.from(document.querySelectorAll('#reportsTable tbody tr.report-row'));
        
        // Execute primary global sort & build initial table layout view
        filterTable();
    });

    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const statusValue = document.getElementById('statusFilter').value;

        // 1. Filter against master list elements
        filteredRows = masterRowsArray.filter(row => {
            const text = row.innerText.toLowerCase();
            const status = row.getAttribute('data-status');
            return (text.includes(searchValue) && (statusValue === 'all' || status === statusValue));
        });

        // 2. Sort the entire matching subset globally BEFORE slicing pages
        filteredRows.sort((a, b) => {
            const orderA = statusPriority[a.getAttribute('data-status')] || 99;
            const orderB = statusPriority[b.getAttribute('data-status')] || 99;
            return orderA - orderB;
        });

        currentPage = 1; 
        updateTableDisplay();
    }

    function updateTableDisplay() {
        const tableBody = document.getElementById('tableBody');
        
        // Detach all rows visually
        masterRowsArray.forEach(row => row.style.display = 'none');

        // Calculate strict subset ranges
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;

        // Append and display only elements falling inside the targeted page chunk
        for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
            const row = filteredRows[i];
            row.style.display = '';
            tableBody.appendChild(row); // Physically re-appends row to enforce sorted structure inside Dom
        }

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
        document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages}`;
        
        document.getElementById('btnPrev').disabled = currentPage === 1;
        document.getElementById('btnNext').disabled = currentPage === totalPages;
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            updateTableDisplay();
        }
    }

    function nextPage() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
        if (currentPage < totalPages) {
            currentPage++;
            updateTableDisplay();
        }
    }

    // --- ASSIGN STAFF MODAL LOGIC ---
    function openAssignModal(reportId, currentStaffName, currentStaffId) {
        document.getElementById('modal_report_id').value = reportId;

        const subtitle = document.getElementById('modalSubtitle');
        if (currentStaffName) {
            subtitle.textContent = 'Report #' + reportId + ' — Currently assigned to: ' + currentStaffName;
        } else {
            subtitle.textContent = 'Report #' + reportId + ' — Currently unassigned.';
        }

        const radios = document.querySelectorAll('#assignForm input[type="radio"]');
        radios.forEach(r => {
            r.checked = false;
            r.closest('label').classList.remove('selected');
        });

        if (currentStaffId) {
            const match = document.querySelector('#assignForm input[data-id="' + currentStaffId + '"]');
            if (match) {
                match.checked = true;
                match.closest('label').classList.add('selected');
            }
        } else {
            document.getElementById('radio_unassign').checked = true;
            document.getElementById('radio_unassign').closest('label').classList.add('selected');
        }

        document.getElementById('assignModal').classList.add('active');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.remove('active');
    }

    function selectStaff(label) {
        document.querySelectorAll('#assignForm label').forEach(l => l.classList.remove('selected'));
        label.classList.add('selected');
    }

    // --- DELETE CONFIRMATION MODAL LOGIC ---
    function openDeleteModal(reportId) {
        document.getElementById('deleteModalText').textContent = 'Are you sure you want to delete Report #' + reportId + '? This action cannot be undone.';
        // Set the href of the confirm button to execute the PHP GET request
        document.getElementById('confirmDeleteBtn').href = '?delete=' + reportId;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    // Close modals when clicking outside the box
    document.getElementById('assignModal').addEventListener('click', function(e) {
        if (e.target === this) closeAssignModal();
    });
    
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
</body>
</html>