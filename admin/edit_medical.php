<?php
session_start();
include('../conn.php');

// Check if 'id' parameter is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: medical-records.php');
    exit;
}

$medical_record_id = $_GET['id'];

// Fetch the record data from the database
$query = "
    SELECT 
        mr.*, 
        p.name AS pet_name, 
        po.first_name, po.middle_name, po.last_name
    FROM medical_records mr
    LEFT JOIN pet_records p ON mr.pet_id = p.pet_id
    LEFT JOIN pet_owner_records po ON mr.owner_id = po.owner_id
    WHERE mr.medical_record_id = $medical_record_id
";
$result = mysqli_query($conn, $query);
$record = mysqli_fetch_assoc($result);

if (!$record) {
    echo "Record not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../assets/images/pethaus_logo.png" />
    <title>Edit Medical Record</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .sidebar {
            background: #fff;
            border-right: 1px solid #dee2e6;
            height: 100vh;
            transition: width 0.3s;
            overflow: hidden;
            flex-shrink: 0;
            width: 255px;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar .custom-nav li a {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            padding: 10px 15px;
            text-decoration: none;
            color: #000;
        }

        .sidebar .custom-nav li a:hover {
            background-color: #296849;
            color: white;
        }

        .sidebar.collapsed .custom-nav li a span {
            display: none;
        }

        .toggle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            background-color: #296849;
            color: white;
            z-index: 1000;
        }

        .no-transition {
            transition: none !important;
        }

        #main-content {
            flex-grow: 1;
            transition: margin-left 0.3s;
            padding: 2rem;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar expanded position-relative d-none d-md-block">
            <div class="toggle-btn" onclick="toggleSidebar()">
                <i class="fa fa-angle-left" id="toggle-icon"></i>
            </div>
            <div class="p-3 pt-5">
                <ul class="custom-nav">
                    <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                        <a href="dashboard.php">
                            <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'pet-records.php' ? 'active' : '' ?>">
                        <a href="pet-records.php">
                            <i class="fa-solid fa-box"></i> <span>Pet Records</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'pet-owner-profiles.php' ? 'active' : '' ?>">
                        <a href="pet-owner-profiles.php">
                            <i class="fa-solid fa-user"></i> <span>Pet Owner Profiles</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'medical-records.php' || 'edit_medical.php' ? 'active' : '' ?>">
                        <a href="medical-records.php">
                            <i class="fa-solid fa-file-medical"></i> <span>Medical Records</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'medical-bills.php' ? 'active' : '' ?>">
                        <a href="medical-bills.php">
                            <i class="fa-solid fa-file-invoice-dollar"></i> <span>Medical Bills</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'product-inventory.php' ? 'active' : '' ?>">
                        <a href="product-inventory.php">
                            <i class="fa-solid fa-boxes-stacked"></i> <span>Product Inventory</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'sales-transactions.php' ? 'active' : '' ?>">
                        <a href="sales-transactions.php">
                            <i class="fa-solid fa-chart-line"></i> <span>Sales Transactions</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'open-cart.php' ? 'active' : '' ?>">
                        <a href="open-cart.php">
                            <i class="fa-solid fa-cart-plus"></i> <span>Open Cart</span>
                        </a>
                    </li>
                    <li class="<?= $current_page === 'pet-queue.php' ? 'active' : '' ?>">
                        <a href="pet-queue.php">
                            <i class="fa-solid fa-clipboard-list"></i> <span>Pet Queue</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['type'] === 'admin'): ?>
                        <li class="<?= $current_page === 'pethaus-staff.php' ? 'active' : '' ?>">
                            <a href="pethaus-staff.php">
                                <i class="fa-solid fa-user-gear"></i> <span>Pethaus Staff</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="../logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div id="main-content" class="p-4">
            <h3 class="fw-bold">Edit Medical Record</h3>
            <form action="../actions/process_update_medical.php?id=<?php echo $medical_record_id; ?>" method="POST">
                <!-- Medical Record Form (Step 2) -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type" required>
                            <optgroup label="Diagnostic Tests">
                                <option value="3DX">3DX</option>
                                <option value="4DX">4DX</option>
                                <option value="5DX">5DX</option>
                                <option value="CBC">CBC</option>
                                <option value="CDV">CDV</option>
                                <option value="CPV">CPV</option>
                                <option value="BLOOD CHEM">BLOOD CHEM</option>
                                <option value="LEPTO TEST">LEPTO TEST</option>
                                <option value="PARVO">PARVO</option>
                                <option value="DISTEMPER">DISTEMPER</option>
                            </optgroup>

                            <optgroup label="Imaging">
                                <option value="ULTRASOUND">ULTRASOUND</option>
                                <option value="X-RAY">X-RAY</option>
                            </optgroup>

                            <optgroup label="Laboratory Tests">
                                <option value="FECALYSIS">FECALYSIS</option>
                                <option value="SKIN SCRAPPING">SKIN SCRAPPING
                                </option>
                                <option value="BLOOD SMEAR">BLOOD SMEAR</option>
                            </optgroup>

                            <optgroup label="Procedures">
                                <option value="CEAZARIAN">CEAZARIAN</option>
                                <option value="DENTAL CLEANING">DENTAL CLEANING
                                </option>
                                <option value="EAR CLEANING">EAR CLEANING</option>
                                <option value="WOUND CLEANING">WOUND CLEANING
                                </option>
                                <option value="SEDATION">SEDATION</option>
                                <option value="OTOSCOPE">OTOSCOPE</option>
                                <option value="WOUND LAMP">WOUND LAMP</option>
                            </optgroup>

                            <optgroup label="Other Services">
                                <option value="GROOMING">GROOMING</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Attending Vet</label>
                        <select class="form-control" name="attending_vet" required>
                            <option value="Dr. Dayanna N. Sipin" <?php echo ($record['attending_vet'] == 'Dr. Dayanna N. Sipin') ? 'selected' : ''; ?>>Dr. Dayanna N. Sipin</option>
                            <option value="Dr. Kean Tiodanco" <?php echo ($record['attending_vet'] == 'Dr. Kean Tiodanco') ? 'selected' : ''; ?>>Dr. Kean Tiodanco</option>
                            <option value="Dr. Marjorie S. Combrero" <?php echo ($record['attending_vet'] == 'Dr. Marjorie S. Combrero') ? 'selected' : ''; ?>>Dr. Marjorie S. Combrero</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="date_started" value="<?php echo $record['date_started']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="date_ended" value="<?php echo $record['date_ended']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weight</label>
                        <input type="text" class="form-control" name="weight" value="<?php echo $record['weight']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Temperature (°C)</label>
                        <input type="text" class="form-control" name="temperature" value="<?php echo $record['temperature']; ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Complaint/History</label>
                        <textarea class="form-control" name="complaint" rows="2" required><?php echo $record['complaint']; ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Tentative Diagnosis</label>
                        <textarea class="form-control" name="description" rows="2" required><?php echo $record['description']; ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn text-black mt-3" style="background-color: #FFD531;">Update Record</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleIcon = document.getElementById('toggle-icon');

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('expanded');

            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');

            toggleIcon.classList.toggle('fa-angle-left', !isCollapsed);
            toggleIcon.classList.toggle('fa-angle-right', isCollapsed);

            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
            document.body.classList.toggle('sidebar-expanded', !isCollapsed);
        }

        window.addEventListener('DOMContentLoaded', () => {
            sidebar.classList.add('no-transition');

            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.remove('expanded');
                sidebar.classList.add('collapsed');
                toggleIcon.classList.remove('fa-angle-left');
                toggleIcon.classList.add('fa-angle-right');
                document.body.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('expanded');
                toggleIcon.classList.remove('fa-angle-right');
                toggleIcon.classList.add('fa-angle-left');
                document.body.classList.add('sidebar-expanded');
            }

            setTimeout(() => {
                sidebar.classList.remove('no-transition');
            }, 10);
        });
    </script>
</body>

</html>