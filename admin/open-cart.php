<?php
session_start();
include '../conn.php';

$query = "
    SELECT 
        ps.product_sale_id,
        p.name AS product_name,
        ps.sale_quantity AS quantity,
        ps.sale_price AS price,
        ps.unit_of_measure,
        ps.total_amount AS subtotal
    FROM product_sale ps
    JOIN products p ON ps.product_id = p.product_id
    WHERE ps.sale_id IS NULL
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$staff = $_SESSION['first_name'] ?? '';
$staff .= (isset($_SESSION['middle_name']) && $_SESSION['middle_name'] !== '')
    ? ' ' . $_SESSION['middle_name'][0] . '.'
    : '';
$staff .= ' ' . ($_SESSION['last_name'] ?? '');

$cartItems = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $cartItems[] = $row;
    $total += $row['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../assets/images/pethaus_logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <title>Open Cart</title>
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
                <h3 class="fw-bold mb-3">Open Cart</h3>

                <div class="d-flex justify-content-between mb-5">
                    <div class="w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-cart-plus"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search carts...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Cart Items</h5>

                        <table class="table table-bordered align-middle">
                            <thead class="table-white">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price (₱)</th>
                                    <th>Unit</th>
                                    <th>Subtotal (₱)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td class="d-flex align-items-center gap-2">
                                            <form method="post" action="../actions/update_cart.php" class="d-flex gap-2">
                                                <input type="hidden" name="product_sale_id"
                                                    value="<?= $item['product_sale_id'] ?>">
                                                <button name="action" value="decrease"
                                                    class="btn btn-sm btn-outline-secondary">-</button>
                                                <input type="number" class="form-control form-control-sm text-center"
                                                    style="width: 60px;" value="<?= $item['quantity'] ?>" readonly>
                                                <button name="action" value="increase"
                                                    class="btn btn-sm btn-outline-secondary">+</button>
                                            </form>
                                        </td>
                                        <td>₱<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['unit_of_measure'] ?></td>
                                        <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                        <td>
                                            <form method="post" action="../actions/remove_cart_item.php">
                                                <input type="hidden" name="product_sale_id"
                                                    value="<?= $item['product_sale_id'] ?>">
                                                <button class="btn btn-sm btn-danger"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td colspan="2">₱<?= number_format($total, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="text-end">
                            <button class="btn text-black" style="background-color: #FFD531;" data-bs-toggle="modal"
                                data-bs-target="#placeOrderModal">Check Out</button>
                        </div>
                    </div>
                </div>

                <!-- Place Order Modal -->
                <div class="modal fade" id="placeOrderModal" tabindex="-1" aria-labelledby="placeOrderModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="placeOrderModalLabel">View Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Clinic:</strong> PetHaus Veterinary Clinic</p>
                                    <p class="mb-1"><strong>Prepared By:</strong> <?php echo $staff ?></p>
                                    <p class="mb-3"><strong>Date:</strong> <?= date('F j, Y'); ?></p>
                                </div>

                                <table class="table table-bordered">
                                    <thead class="table-white">
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Price (₱)</th>
                                            <th>Subtotal (₱)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                                <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-white">
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td class="fw-bold">₱<?= number_format($total, 2) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button class="btn bg-black text-white" data-bs-dismiss="modal">Close</button>
                                <form action="../actions/place_order.php" method="POST" class="d-inline">
                                    <button type="submit" class="btn bg-black text-white">Place Order & Print</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>
</body>

</html>