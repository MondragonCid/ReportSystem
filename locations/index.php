<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$query = "SELECT * FROM location ORDER BY BuildingName, ClassRoomNum";
$result = mysqli_query($conn, $query);

$base_path = '../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations - CIT University</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; overflow-x: hidden; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }

        .sidebar { width: 250px; background-color: #800000; color: white; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; flex-shrink: 0; }
        .sidebar-brand { padding: 25px; font-weight: bold; font-size: 14px; text-align: center; background-color: #600000; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        .nav-item { padding: 15px 20px; color: white; text-decoration: none; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s; display: block; }
        .nav-item:hover, .nav-item.active { background-color: #a00000; padding-left: 30px; }
        .nav-item.logout-btn { margin-top: auto; background-color: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }

        .content-area { flex: 1; padding: 40px; overflow-y: auto; min-width: 0; }
        .header-container { display: flex; align-items: center; margin-bottom: 5px; }
        .logo { height: 60px; margin-right: 15px; } 
        .header-text h1 { color: #800000; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-text p { font-size: 16px; color: #555; font-weight: 500; }
        .red-line { border: 0; height: 3px; background-color: #800000; margin: 15px 0 25px 0; }

        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; width: 100%; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section h2 { color: #333; border-left: 4px solid #800000; padding-left: 15px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .data-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-weight: bold; font-size: 14px; color: #333; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #555; vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word; }
        .data-table tr:hover { background: #fcfcfc; }

        /* Structural width assignments to ensure alignment continuity */
        .col-id { width: 15%; }
        .col-bname { width: 40%; }
        .col-room { width: 25%; }
        .col-actions { width: 20%; }

        .btn { display: inline-block; padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.2s; border: none; cursor: pointer; text-align: center; }
        .btn-add { background-color: #800000; color: white; }
        .btn-add:hover { background-color: #600000; }
        .btn-edit { background-color: #ffc107; color: #000; margin-right: 5px; }
        .btn-edit:hover { background-color: #e0a800; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #a71d2a; }

        /* MODAL Layout UI Architecture */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal { background: white; border-radius: 10px; padding: 30px; width: 400px; max-width: 95vw; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
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
                    <p>System Administration | Location Management</p>
                </div>
            </div>
            <hr class="red-line">

            <div class="section">
                <div class="section-header">
                    <h2>📍 Campus Locations</h2>
                    <a href="create.php" class="btn btn-add">➕ Add New Location</a>
                </div>

                <table class="data-table" id="locationsTable">
                    <thead>
                        <tr>
                            <th class="col-id">Location ID</th>
                            <th class="col-bname">Building Name</th>
                            <th class="col-room">Room / Classroom</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($location = mysqli_fetch_assoc($result)): ?>
                                <tr class="location-row">
                                    <td><strong>#<?php echo $location['LocationID']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($location['BuildingName']); ?></td>
                                    <td><?php echo htmlspecialchars($location['ClassRoomNum']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $location['LocationID']; ?>" class="btn btn-edit">Edit</a>
                                        <button type="button" class="btn btn-delete" onclick="openDeleteModal(<?php echo $location['LocationID']; ?>, '<?php echo htmlspecialchars(addslashes($location['BuildingName'] . " - " . $location['ClassRoomNum'])); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr id="noDataRow">
                                <td colspan="4" style="text-align: center; padding: 30px;">No campus locations registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination-container" id="paginationControls" style="display: none;">
                    <button class="btn-page" id="btnPrev" onclick="prevPage()">❮ Previous</button>
                    <span id="pageInfo" class="page-info">Page 1 of 1</span>
                    <button class="btn-page" id="btnNext" onclick="nextPage()">Next ❯</button>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>Delete Location?</h3>
            <p class="modal-sub" id="deleteModalText">Are you sure you want to delete this location? It may affect existing reports linked to this room.</p>
            <hr class="modal-divider">
            
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn-modal-delete">Yes, Delete It</a>
            </div>
        </div>
    </div>

    <script>
        const rowsPerPage = 8;
        let currentPage = 1;
        let masterRowsArray = [];

        document.addEventListener('DOMContentLoaded', () => {
            // Read out rows matching configuration selectors
            masterRowsArray = Array.from(document.querySelectorAll('#locationsTable tbody tr.location-row'));
            
            if (masterRowsArray.length > 0) {
                document.getElementById('paginationControls').style.display = 'flex';
                updateTableDisplay();
            }
        });

        function updateTableDisplay() {
            const tableBody = document.getElementById('tableBody');
            
            // Mask layout views completely
            masterRowsArray.forEach(row => row.style.display = 'none');

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;

            // Re-render matching slice configuration limits onto DOM view tree
            for (let i = startIndex; i < endIndex && i < masterRowsArray.length; i++) {
                const row = masterRowsArray[i];
                row.style.display = '';
                tableBody.appendChild(row);
            }

            const totalPages = Math.ceil(masterRowsArray.length / rowsPerPage) || 1;
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
            const totalPages = Math.ceil(masterRowsArray.length / rowsPerPage) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                updateTableDisplay();
            }
        }

        // --- DYNAMIC CONFIRMATION POP-UP SYSTEM MODAL LOGIC ---
        function openDeleteModal(locationId, locationInfo) {
            document.getElementById('deleteModalText').innerHTML = `Are you sure you want to delete <strong>${locationInfo}</strong>?<br><br>Warning: This action cannot be undone and may break visibility links on older damage reports registered to this specific area.`;
            document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + locationId;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Overlay backing viewport execution shield
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>
</body>
</html>