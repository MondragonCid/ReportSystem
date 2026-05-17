<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$base_path = '../';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'admins';

// Get all admins
$admins_result = mysqli_query($conn,
    "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
            sa.AdminID, sa.Department
     FROM user u
     INNER JOIN system_administrator sa ON u.UserID = sa.UserID
     ORDER BY u.CreatedAt DESC"
);

// Get all staff
$staff_result = mysqli_query($conn,
    "SELECT u.UserID, u.Username, u.Email, u.FirstName, u.LastName, u.CreatedAt,
            ms.StaffID, ms.Specialization, ms.ContactNumber
     FROM user u
     INNER JOIN maintainance_staff ms ON u.UserID = ms.UserID
     ORDER BY u.CreatedAt DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CIT University</title>
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

        /* Tabs */
        .tabs { display: flex; gap: 5px; margin-bottom: 25px; border-bottom: 2px solid #ddd; }
        .tab-btn { padding: 12px 24px; background: none; border: none; font-size: 15px; font-weight: bold; cursor: pointer; color: #888; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: 0.2s; text-decoration: none; display: inline-block; }
        .tab-btn:hover { color: #800000; }
        .tab-btn.active { color: #800000; border-bottom-color: #800000; }

        /* Section */
        .section { background: white; padding: 25px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: none; width: 100%; }
        .section.active { display: block; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section h2 { color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-table th { background: #f8f9fa; padding: 13px 15px; text-align: left; border-bottom: 2px solid #eee; font-size: 13px; font-weight: bold; }
        .data-table td { padding: 13px 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #555; vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word; }
        .data-table tr:hover { background: #fafafa; }

        /* Explicit structural width mappings for proportional columns */
        .col-admin-id { width: 12%; }
        .col-admin-name { width: 20%; }
        .col-admin-user { width: 15%; }
        .col-admin-email { width: 22%; }
        .col-admin-dept { width: 16%; }
        .col-admin-date { width: 15%; }
        .col-admin-act { width: 12%; }

        .col-staff-id { width: 10%; }
        .col-staff-name { width: 18%; }
        .col-staff-user { width: 13%; }
        .col-staff-email { width: 20%; }
        .col-staff-spec { width: 14%; }
        .col-staff-phone { width: 13%; }
        .col-staff-date { width: 12%; }
        .col-staff-act { width: 12%; }

        .btn { display: inline-block; padding: 8px 16px; border-radius: 5px; font-weight: bold; font-size: 13px; text-decoration: none; border: none; cursor: pointer; transition: 0.2s; text-align: center; }
        .btn-create  { background: #800000; color: white; padding: 10px 18px; }
        .btn-create:hover  { background: #600000; }
        .btn-delete  { background: #dc3545; color: white; }
        .btn-delete:hover  { background: #a71d2a; }

        .alert-success { background: #e8f5e9; border-left: 5px solid #4caf50; padding: 12px 15px; border-radius: 4px; color: #2e7d32; margin-bottom: 20px; font-size: 14px; }
        .empty-msg { text-align: center; padding: 30px; color: #888; font-size: 14px; }

        /* MODAL Layout UI Architecture */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal { background: white; border-radius: 10px; padding: 30px; width: 420px; max-width: 95vw; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .modal h3 { color: #dc3545; font-size: 22px; margin-bottom: 5px; }
        .modal .modal-sub { color: #555; font-size: 14px; margin-top: 15px; line-height: 1.5; }
        .modal-divider { border: 0; height: 1px; background: #eee; margin: 20px 0; }

        .modal-actions { display: flex; gap: 15px; justify-content: center; }
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
            <img src="<?= $base_path ?>citu_logo.png" alt="Logo" class="logo">
            <div class="header-text">
                <h1>CEBU INSTITUTE OF TECHNOLOGY - UNIVERSITY</h1>
                <p>System Administration | User Management</p>
            </div>
        </div>
        <hr class="red-line">

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert-success">✅ Account deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['created'])): ?>
            <div class="alert-success">✅ Account created successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self'): ?>
            <div class="alert-success" style="background:#fdecea;border-color:#e53935;color:#c62828;">❌ You cannot delete your own account.</div>
        <?php endif; ?>

        <div class="tabs">
            <a href="?tab=admins" class="tab-btn <?= $active_tab == 'admins' ? 'active' : '' ?>"> Administrators</a>
            <a href="?tab=staff"  class="tab-btn <?= $active_tab == 'staff'  ? 'active' : '' ?>"> Maintenance Staff</a>
        </div>

        <div class="section <?= $active_tab == 'admins' ? 'active' : '' ?>">
            <div class="section-header">
                <h2> List of Administrators</h2>
                <a href="create.php?type=admin" class="btn btn-create"> Add Administrator</a>
            </div>
            <table class="data-table" id="adminsTable">
                <thead>
                    <tr>
                        <th class="col-admin-id">Admin ID</th>
                        <th class="col-admin-name">Full Name</th>
                        <th class="col-admin-user">Username</th>
                        <th class="col-admin-email">Email</th>
                        <th class="col-admin-dept">Department</th>
                        <th class="col-admin-date">Created</th>
                        <th class="col-admin-act">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminsTableBody">
                    <?php if (mysqli_num_rows($admins_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($admins_result)): ?>
                        <tr class="admin-row">
                            <td><strong><?= htmlspecialchars($row['AdminID']) ?></strong></td>
                            <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                            <td><?= htmlspecialchars($row['Username']) ?></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td><?= htmlspecialchars($row['Department'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-delete" onclick="openUserDeleteModal(<?= $row['UserID'] ?>, 'admin', '<?= htmlspecialchars(addslashes($row['FirstName'] . ' ' . $row['LastName'])) ?>', '<?= htmlspecialchars(addslashes($row['AdminID'])) ?>')">🗑️ Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="noAdminRow"><td colspan="7" class="empty-msg">No administrators found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="pagination-container" id="adminPaginationControls" style="display: none;">
                <button class="btn-page" id="btnAdminPrev" onclick="changeAdminPage(-1)">❮ Previous</button>
                <span id="adminPageInfo" class="page-info">Page 1 of 1</span>
                <button class="btn-page" id="btnAdminNext" onclick="changeAdminPage(1)">Next ❯</button>
            </div>
        </div>

        <div class="section <?= $active_tab == 'staff' ? 'active' : '' ?>">
            <div class="section-header">
                <h2>List of Maintenance Staff</h2>
                <a href="create.php?type=staff" class="btn btn-create"> Add Staff</a>
            </div>
            <table class="data-table" id="staffTable">
                <thead>
                    <tr>
                        <th class="col-staff-id">Staff ID</th>
                        <th class="col-staff-name">Full Name</th>
                        <th class="col-staff-user">Username</th>
                        <th class="col-staff-email">Email</th>
                        <th class="col-staff-spec">Specialization</th>
                        <th class="col-staff-phone">Contact</th>
                        <th class="col-staff-date">Created</th>
                        <th class="col-staff-act">Actions</th>
                    </tr>
                </thead>
                <tbody id="staffTableBody">
                    <?php if (mysqli_num_rows($staff_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($staff_result)): ?>
                        <tr class="staff-row">
                            <td><strong><?= htmlspecialchars($row['StaffID']) ?></strong></td>
                            <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                            <td><?= htmlspecialchars($row['Username']) ?></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td><?= htmlspecialchars($row['Specialization'] ?? 'General') ?></td>
                            <td><?= htmlspecialchars($row['ContactNumber'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-delete" onclick="openUserDeleteModal(<?= $row['UserID'] ?>, 'staff', '<?= htmlspecialchars(addslashes($row['FirstName'] . ' ' . $row['LastName'])) ?>', '<?= htmlspecialchars(addslashes($row['StaffID'])) ?>')">🗑️ Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="noStaffRow"><td colspan="8" class="empty-msg">No maintenance staff found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination-container" id="staffPaginationControls" style="display: none;">
                <button class="btn-page" id="btnStaffPrev" onclick="changeStaffPage(-1)">❮ Previous</button>
                <span id="staffPageInfo" class="page-info">Page 1 of 1</span>
                <button class="btn-page" id="btnStaffNext" onclick="changeStaffPage(1)">Next ❯</button>
            </div>
        </div>

    </main>
</div>

<div class="modal-overlay" id="userDeleteModal">
    <div class="modal">
        <h3>Delete User Account?</h3>
        <p class="modal-sub" id="userDeleteModalText">Are you sure you want to permanently delete this account? This action cannot be revoked.</p>
        <hr class="modal-divider">
        
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeUserDeleteModal()">Cancel</button>
            <a href="#" id="confirmUserDeleteBtn" class="btn-modal-delete">Yes, Delete Account</a>
        </div>
    </div>
</div>

<script>
    const rowsPerPage = 8;
    
    // Page tracking context
    let currentAdminPage = 1;
    let currentStaffPage = 1;
    
    let masterAdminsArray = [];
    let masterStaffArray = [];

    document.addEventListener('DOMContentLoaded', () => {
        // Parse current tracking arrays
        masterAdminsArray = Array.from(document.querySelectorAll('#adminsTable tbody tr.admin-row'));
        masterStaffArray = Array.from(document.querySelectorAll('#staffTable tbody tr.staff-row'));

        // Handle Admin table pagination state
        if (masterAdminsArray.length > 0) {
            document.getElementById('adminPaginationControls').style.display = 'flex';
            updateAdminTableDisplay();
        }

        // Handle Staff table pagination state
        if (masterStaffArray.length > 0) {
            document.getElementById('staffPaginationControls').style.display = 'flex';
            updateStaffTableDisplay();
        }
    });

    // --- ADMIN TABLE SLICER IMPLEMENTATION ---
    function updateAdminTableDisplay() {
        const tbody = document.getElementById('adminsTableBody');
        masterAdminsArray.forEach(row => row.style.display = 'none');

        const start = (currentAdminPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        for (let i = start; i < end && i < masterAdminsArray.length; i++) {
            masterAdminsArray[i].style.display = '';
            tbody.appendChild(masterAdminsArray[i]);
        }

        const totalPages = Math.ceil(masterAdminsArray.length / rowsPerPage) || 1;
        document.getElementById('adminPageInfo').innerText = `Page ${currentAdminPage} of ${totalPages}`;
        document.getElementById('btnAdminPrev').disabled = currentAdminPage === 1;
        document.getElementById('btnAdminNext').disabled = currentAdminPage === totalPages;
    }

    function changeAdminPage(direction) {
        const totalPages = Math.ceil(masterAdminsArray.length / rowsPerPage) || 1;
        const targetPage = currentAdminPage + direction;
        if (targetPage >= 1 && targetPage <= totalPages) {
            currentAdminPage = targetPage;
            updateAdminTableDisplay();
        }
    }

    // --- STAFF TABLE SLICER IMPLEMENTATION ---
    function updateStaffTableDisplay() {
        const tbody = document.getElementById('staffTableBody');
        masterStaffArray.forEach(row => row.style.display = 'none');

        const start = (currentStaffPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        for (let i = start; i < end && i < masterStaffArray.length; i++) {
            masterStaffArray[i].style.display = '';
            tbody.appendChild(masterStaffArray[i]);
        }

        const totalPages = Math.ceil(masterStaffArray.length / rowsPerPage) || 1;
        document.getElementById('staffPageInfo').innerText = `Page ${currentStaffPage} of ${totalPages}`;
        document.getElementById('btnStaffPrev').disabled = currentStaffPage === 1;
        document.getElementById('btnStaffNext').disabled = currentStaffPage === totalPages;
    }

    function changeStaffPage(direction) {
        const totalPages = Math.ceil(masterStaffArray.length / rowsPerPage) || 1;
        const targetPage = currentStaffPage + direction;
        if (targetPage >= 1 && targetPage <= totalPages) {
            currentStaffPage = targetPage;
            updateStaffTableDisplay();
        }
    }

    // --- DYNAMIC SEAMLESS MODAL INTERACTION ---
    function openUserDeleteModal(userId, type, fullName, profileId) {
        const label = type === 'admin' ? 'Administrator' : 'Maintenance Staff';
        
        document.getElementById('userDeleteModalText').innerHTML = 
            `Are you sure you want to delete the ${label} profile for <strong>${fullName}</strong> (${profileId})?<br><br>` +
            `Warning: This completely deletes their authentication login data and system role mappings immediately.`;
            
        document.getElementById('confirmUserDeleteBtn').href = `delete.php?id=${userId}&type=${type}`;
        document.getElementById('userDeleteModal').classList.add('active');
    }

    function closeUserDeleteModal() {
        document.getElementById('userDeleteModal').classList.remove('active');
    }

    // Escape click-out trap boundary setup
    document.getElementById('userDeleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeUserDeleteModal();
    });
</script>
</body>
</html>