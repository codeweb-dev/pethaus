<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');
include('../actions/check_admin_role.php');

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$users = [];
$query = "SELECT * FROM users ORDER BY user_id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
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
    <title>Pethaus Staff</title>
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
                <h3 class="fw-bold mb-3">Pethaus Staff</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class=" w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user-gear"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for staff...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn text-black" style="background-color: #FFD531;" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn text-black" style="background-color: #FFD531;" data-bs-toggle="modal" data-bs-target="#addNewStaff">
                            <i class="fa-solid fa-plus"></i> Add new staff
                        </button>

                        <div class="modal fade" id="addNewStaff" tabindex="-1" aria-labelledby="addNewStaffLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_staff.php">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new staff</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First name</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your first name"
                                                    name="first_name"
                                                    id="first_name"
                                                    value="<?php echo htmlspecialchars($old['first_name'] ?? ''); ?>">
                                                <?php if (isset($errors['first_name'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="middle_name" class="form-label">Middle name</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($errors['middle_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your middle name (optional)"
                                                    name="middle_name"
                                                    id="middle_name"
                                                    value="<?php echo htmlspecialchars($old['middle_name'] ?? ''); ?>">
                                                <?php if (isset($errors['middle_name'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['middle_name']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last name</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your last name"
                                                    name="last_name"
                                                    id="last_name"
                                                    value="<?php echo htmlspecialchars($old['last_name'] ?? ''); ?>">
                                                <?php if (isset($errors['last_name'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="username" class="form-label">Username</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your username"
                                                    name="username"
                                                    id="username"
                                                    value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>">
                                                <?php if (isset($errors['username'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-4">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password"
                                                    class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter your password"
                                                    name="password"
                                                    id="password">
                                                <?php if (isset($errors['password'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Register</button>
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
                                <th class="p-3" scope="col">First name</th>
                                <th class="p-3" scope="col">Middle name</th>
                                <th class="p-3" scope="col">Last name</th>
                                <th class="p-3" scope="col">Type</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $user['first_name']; ?></td>
                                    <td class="p-3"><?php echo $user['middle_name']; ?></td>
                                    <td class="p-3"><?php echo $user['last_name']; ?></td>
                                    <td class="p-3">
                                        <button type="button" style="background-color: #FFD531;" class="btn text-black btn-sm rounded-5">
                                            <?php echo $user['type']; ?>
                                        </button>
                                    </td class="p-3">
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

    <?php if (isset($_GET['modal']) && $_GET['modal'] === 'add' && !empty($errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addNewStaff'));
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