<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

$form_errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM pet_owner_records");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$owners = [];
$query = "
    SELECT o.*, COUNT(p.pet_id) AS pet_count
    FROM pet_owner_records o
    LEFT JOIN pet_records p ON o.owner_id = p.owner_id
    GROUP BY o.owner_id
    ORDER BY o.owner_id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $owners[] = $row;
    }
}

$petRecordsByOwner = [];

$petQuery = "
    SELECT * FROM pet_records
    ORDER BY owner_id DESC
";
$petResult = mysqli_query($conn, $petQuery);
if ($petResult) {
    while ($pet = mysqli_fetch_assoc($petResult)) {
        $petRecordsByOwner[$pet['owner_id']][] = $pet;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../assets/images/pethaus_logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <title>Pet Owner Profiles</title>
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
                    <li class="<?= $current_page === 'medical-records.php' ? 'active' : '' ?>">
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

        <div id="main-content" class="main-content p-4">
            <div class="w-auto">
                <h3 class="fw-bold mb-3">Pet Owner Profiles</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class="w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for owners...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn text-black" style="background-color: #FFD531;" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn text-black" style="background-color: #FFD531;" data-bs-toggle="modal" data-bs-target="#addOwner">
                            <i class="fa-solid fa-plus"></i> Add new owner
                        </button>

                        <div class="modal fade" id="addOwner" tabindex="-1" aria-labelledby="addOwnerLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_owner.php" novalidate>
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new owner</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First name</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['first_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your first name"
                                                    name="first_name"
                                                    id="first_name"
                                                    value="<?php echo isset($old['first_name']) ? htmlspecialchars($old['first_name']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['first_name'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="middle_name" class="form-label">Middle name</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['middle_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your middle name (Optional)"
                                                    name="middle_name"
                                                    id="middle_name"
                                                    value="<?php echo isset($old['middle_name']) ? htmlspecialchars($old['middle_name']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['middle_name'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last name</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['last_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your last name"
                                                    name="last_name"
                                                    id="last_name"
                                                    value="<?php echo isset($old['last_name']) ? htmlspecialchars($old['last_name']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['last_name'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['address']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your address"
                                                    name="address"
                                                    id="address"
                                                    value="<?php echo isset($old['address']) ? htmlspecialchars($old['address']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['address'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['mobile_number']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your mobile number"
                                                    name="mobile_number"
                                                    id="mobile_number"
                                                    value="<?php echo isset($old['mobile_number']) ? htmlspecialchars($old['mobile_number']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['mobile_number'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="messenger_account" class="form-label">Messenger Account</label>
                                                <input
                                                    type="text"
                                                    class="form-control <?php echo isset($form_errors['messenger_account']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your messenger account (Optional)"
                                                    name="messenger_account"
                                                    id="messenger_account"
                                                    value="<?php echo isset($old['messenger_account']) ? htmlspecialchars($old['messenger_account']) : ''; ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['messenger_account'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <?php if (isset($form_errors['general'])): ?>
                                                <div class="alert alert-danger"><?php echo htmlspecialchars($form_errors['general']); ?></div>
                                            <?php endif; ?>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Add new owner</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th class="p-3" scope="col">ID</th>
                                <th class="p-3" scope="col">Full Name</th>
                                <th class="p-3" scope="col">Address</th>
                                <th class="p-3" scope="col">Mobile Number</th>
                                <th class="p-3" scope="col">Messenger Account</th>
                                <th class="p-3" scope="col">Number Pets Owned</th>
                                <th class="p-3" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($owners as $owner): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $owner['owner_id']; ?></td>
                                    <td class="p-3">
                                        <?php
                                        echo $owner['first_name'] . ' ';
                                        echo !empty($owner['middle_name']) ? $owner['middle_name'][0] . '. ' : '';
                                        echo $owner['last_name'];
                                        ?>
                                    </td>
                                    <td class="p-3"><?php echo $owner['address']; ?></td>
                                    <td class="p-3"><?php echo $owner['mobile_number']; ?></td>
                                    <td class="p-3"><?php echo $owner['messenger_account']; ?></td>
                                    <td class="p-3"><?php echo $owner['pet_count']; ?></td>

                                    <td class="d-flex gap-3 p-3">
                                        <i class="fa-solid fa-paw" title="View Pets" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#viewPetsModal<?php echo $owner['owner_id']; ?>"></i>

                                        <div class="modal fade" id="viewPetsModal<?php echo $owner['owner_id']; ?>" tabindex="-1" aria-labelledby="viewPetsModalLabel<?php echo $owner['owner_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewPetsModalLabel<?php echo $owner['owner_id']; ?>">
                                                            Pets of <?php echo $owner['first_name'] . ' ' . $owner['last_name']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (!empty($petRecordsByOwner[$owner['owner_id']])): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="p-3">Name</th>
                                                                            <th class="p-3">Species</th>
                                                                            <th class="p-3">Breed</th>
                                                                            <th class="p-3">Color</th>
                                                                            <th class="p-3">Sex</th>
                                                                            <th class="p-3">Birthdate</th>
                                                                            <th class="p-3">Age</th>
                                                                            <th class="p-3">Markings</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($petRecordsByOwner[$owner['owner_id']] as $pet): ?>
                                                                            <tr>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['name']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['species']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['breed']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['color']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['sex']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['birthdate']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['age']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['markings']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-muted">No pets found for this owner.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <i class="fa-solid fa-pen-to-square" title="Edit owner" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editOwnerModal<?php echo $owner['owner_id']; ?>"></i>

                                        <div class="modal fade" id="editOwnerModal<?php echo $owner['owner_id']; ?>" tabindex="-1" aria-labelledby="editPetModalLabel<?php echo $owner['owner_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/update_owner.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editPetModalLabel<?php echo $owner['owner_id']; ?>">Edit Owner: <?php echo $owner['first_name']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body row">
                                                            <input type="hidden" name="owner_id" value="<?php echo $owner['owner_id']; ?>">

                                                            <div class="mb-3 col-md-6">
                                                                <label for="first_name" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" name="first_name" value="<?php echo $owner['first_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="middle_name" class="form-label">Middle Name</label>
                                                                <input type="text" class="form-control" name="middle_name" value="<?php echo $owner['middle_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="last_name" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" name="last_name" value="<?php echo $owner['last_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="address" class="form-label">Address</label>
                                                                <input type="text" class="form-control" name="address" value="<?php echo $owner['address']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                                                <input type="text" class="form-control" name="mobile_number" value="<?php echo $owner['mobile_number']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="messenger_account" class="form-label">Messenger Account</label>
                                                                <input type="text" class="form-control" name="messenger_account" value="<?php echo $owner['messenger_account']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn bg-black text-white">Update Owner</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Table page navigation">
                    <ul class="pagination justify-content-end">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['modal']) && $_GET['modal'] === 'add' && !empty($form_errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addOwner'));
                myModal.show();
            });
        </script>
    <?php endif; ?>

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>

    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#staffTableBody tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

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