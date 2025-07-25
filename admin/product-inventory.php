<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

$form_errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$products = [];
$query = "SELECT * FROM products ORDER BY product_id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

$categoryFilter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$stockFilter = isset($_GET['stock']) ? mysqli_real_escape_string($conn, $_GET['stock']) : '';

$whereParts = [];
if (!empty($categoryFilter)) {
    $whereParts[] = "category = '$categoryFilter'";
}
if (!empty($stockFilter)) {
    $whereParts[] = "stock = '$stockFilter'";
}
$whereClause = "";
if (!empty($whereParts)) {
    $whereClause = "WHERE " . implode(' AND ', $whereParts);
}

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM products $whereClause");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$query = "SELECT * FROM products $whereClause ORDER BY product_id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
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
    <title>Product Inventory</title>
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

        .stock {
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .stock-out {
            background-color: #ffcccc;
            color: #891723ff;
        }

        .stock-low {
            background-color: #fff3cd;
            color: #cfa219ff;
        }

        .stock-good {
            background-color: #d4edda;
            color: #1eb140ff;
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
                <h3 class="fw-bold mb-3">Product Inventory</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class="w-auto d-flex gap-2">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text "><i class="fa-solid fa-boxes-stacked"></i></span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Search for products...">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <form class="d-flex gap-2" method="GET">
                                <select name="category" class="form-select w-25">
                                    <option value="" <?php echo empty($categoryFilter) ? 'selected' : ''; ?>>All
                                        Categories</option>
                                    <option value="Veterinary Medicines & Treatments" <?php echo ($categoryFilter == "Veterinary Medicines & Treatments") ? 'selected' : ''; ?>>
                                        Veterinary Medicines & Treatments</option>
                                    <option value="Pet Food & Treats" <?php echo ($categoryFilter == "Pet Food & Treats") ? 'selected' : ''; ?>>Pet Food & Treats</option>
                                    <option value="Pet Accessories" <?php echo ($categoryFilter == "Pet Accessories") ? 'selected' : ''; ?>>Pet Accessories</option>
                                    <option value="Pet Housing & Bedding" <?php echo ($categoryFilter == "Pet Housing & Bedding") ? 'selected' : ''; ?>>Pet Housing & Bedding</option>
                                    <option value="Grooming Supplies" <?php echo ($categoryFilter == "Grooming Supplies") ? 'selected' : ''; ?>>Grooming Supplies</option>
                                    <option value="Cleaning & Sanitation" <?php echo ($categoryFilter == "Cleaning & Sanitation") ? 'selected' : ''; ?>>Cleaning & Sanitation</option>
                                    <option value="Pet Toys & Enrichment" <?php echo ($categoryFilter == "Pet Toys & Enrichment") ? 'selected' : ''; ?>>Pet Toys & Enrichment</option>
                                </select>

                                <select name="stock" class="form-select w-25">
                                    <option value="" <?php echo empty($_GET['stock']) ? 'selected' : ''; ?>>All Stock
                                        Status</option>
                                    <option value="In Stock" <?php echo (isset($_GET['stock']) && $_GET['stock'] == "In Stock") ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="Low Stock" <?php echo (isset($_GET['stock']) && $_GET['stock'] == "Low Stock") ? 'selected' : ''; ?>>Low Stock</option>
                                    <option value="Out of Stock" <?php echo (isset($_GET['stock']) && $_GET['stock'] == "Out of Stock") ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>

                                <button type="submit" class="btn text-black" style="background-color: #FFD531;">
                                    <i class="fa-solid fa-filter"></i> Filter
                                </button>
                                <?php if (!empty($_GET['category']) || !empty($_GET['stock'])): ?>
                                    <a href="product-inventory.php" class="btn bg-black text-white">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn text-black" style="background-color: #FFD531;" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn text-black" style="background-color: #FFD531;" data-bs-toggle="modal"
                            data-bs-target="#addProduct">
                            <i class="fa-solid fa-plus"></i> Add new product
                        </button>

                        <div class="modal fade" id="addProduct" tabindex="-1" aria-labelledby="addProductLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_product.php" novalidate>
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new product
                                            </h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Product Name</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($form_errors['name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter product name" name="name" id="name"
                                                    value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>">
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['name'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Product Description</label>
                                                <div class="input-group">
                                                    <textarea
                                                        class="form-control <?php echo isset($form_errors['description']) ? 'is-invalid' : ''; ?>"
                                                        id="description" placeholder="Enter product description"
                                                        name="description" rows="3"
                                                        required><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                                                    <div class="invalid-feedback">
                                                        <?php echo $form_errors['description'] ?? ''; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price</label>
                                                <input type="number"
                                                    class="form-control <?php echo isset($form_errors['price']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter product price" name="price" id="price"
                                                    step="0.01" min="0"
                                                    value="<?php echo htmlspecialchars($old['price'] ?? ''); ?>"
                                                    required>
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['price'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="unit_of_measure" class="form-label">Unit of measure</label>
                                                <input type="text"
                                                    class="form-control <?php echo isset($form_errors['unit_of_measure']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter product unit measure" name="unit_of_measure"
                                                    id="unit_of_measure"
                                                    value="<?php echo htmlspecialchars($old['unit_of_measure'] ?? ''); ?>"
                                                    required>
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['unit_of_measure'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="category" class="form-label">Product Category</label>
                                                <select
                                                    class="form-control <?php echo isset($form_errors['category']) ? 'is-invalid' : ''; ?>"
                                                    name="category" id="category" required>
                                                    <option value="" disabled <?php echo empty($old['category']) ? 'selected' : ''; ?>>Select product category</option>
                                                    <option value="Veterinary Medicines & Treatments" <?php echo (isset($old['category']) && $old['category'] == 'Veterinary Medicines & Treatments') ? 'selected' : ''; ?>>Veterinary
                                                        Medicines & Treatments</option>
                                                    <option value="Pet Food & Treats" <?php echo (isset($old['category']) && $old['category'] == 'Pet Food & Treats') ? 'selected' : ''; ?>>Pet Food & Treats</option>
                                                    <option value="Pet Accessories" <?php echo (isset($old['category']) && $old['category'] == 'Pet Accessories') ? 'selected' : ''; ?>>
                                                        Pet Accessories</option>
                                                    <option value="Pet Housing & Bedding" <?php echo (isset($old['category']) && $old['category'] == 'Pet Housing & Bedding') ? 'selected' : ''; ?>>Pet Housing & Bedding</option>
                                                    <option value="Grooming Supplies" <?php echo (isset($old['category']) && $old['category'] == 'Grooming Supplies') ? 'selected' : ''; ?>>Grooming Supplies</option>
                                                    <option value="Cleaning & Sanitation" <?php echo (isset($old['category']) && $old['category'] == 'Cleaning & Sanitation') ? 'selected' : ''; ?>>Cleaning & Sanitation
                                                    </option>
                                                    <option value="Pet Toys & Enrichment" <?php echo (isset($old['category']) && $old['category'] == 'Pet Toys & Enrichment') ? 'selected' : ''; ?>>Pet Toys & Enrichment
                                                    </option>
                                                </select>

                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['category'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity</label>
                                                <input type="number"
                                                    class="form-control <?php echo isset($form_errors['quantity']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter product quantity" name="quantity" id="quantity"
                                                    min="0"
                                                    value="<?php echo htmlspecialchars($old['quantity'] ?? ''); ?>"
                                                    required>
                                                <div class="invalid-feedback">
                                                    <?php echo $form_errors['quantity'] ?? ''; ?>
                                                </div>
                                            </div>

                                            <?php if (isset($form_errors['general'])): ?>
                                                <div class="alert alert-danger">
                                                    <?php echo htmlspecialchars($form_errors['general']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Add new
                                                product</button>
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
                                <th class="p-3" scope="col">Product Name</th>
                                <th class="p-3" scope="col">Product Description</th>
                                <th class="p-3" scope="col">Price</th>
                                <th class="p-3" scope="col">Unit of measure</th>
                                <th class="p-3" scope="col">Product Category</th>
                                <th class="p-3" scope="col">Quantity</th>
                                <th class="p-3" scope="col">Stock</th>
                                <th class="p-3" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $product['product_code']; ?></td>
                                    <td class="p-3"><?php echo $product['name']; ?></td>
                                    <td class="p-3"><?php echo $product['description']; ?></td>
                                    <td class="p-3">₱<?php echo $product['price']; ?></td>
                                    <td class="p-3"><?php echo $product['unit_of_measure']; ?></td>
                                    <td class="p-3"><?php echo $product['category']; ?></td>
                                    <td class="p-3"><?php echo $product['quantity']; ?></td>
                                    <td class="p-3">
                                        <?php if ($product['stock'] === 'Out of Stock'): ?>
                                            <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                                        <?php elseif ($product['stock'] === 'Low Stock'): ?>
                                            <span class="badge bg-warning text-dark"><?php echo $product['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 d-flex align-items-center gap-3">
                                        <i class="fa-solid fa-cart-shopping text-black" title="Add to Cart"
                                            style="cursor: pointer;" data-bs-toggle="modal"
                                            data-bs-target="#addToCartModal<?php echo $product['product_id']; ?>"></i>

                                        <div class="modal fade" id="addToCartModal<?php echo $product['product_id']; ?>"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/add_to_cart.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Add to Cart -
                                                                <?php echo htmlspecialchars($product['name']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="product_id"
                                                                value="<?php echo $product['product_id']; ?>">

                                                            <div class="mb-3">
                                                                <label class="form-label">Product Name</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?php echo htmlspecialchars($product['name']); ?>"
                                                                    readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Available Quantity</label>
                                                                <input type="number" class="form-control"
                                                                    value="<?php echo $product['quantity']; ?>" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Original Price</label>
                                                                <input type="text" class="form-control"
                                                                    id="price_<?php echo $product['product_id']; ?>"
                                                                    value="<?php echo $product['price']; ?>" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Sell By (Unit of Measure)</label>
                                                                <select name="unit_of_measure" class="form-select" required>
                                                                    <option
                                                                        value="<?php echo $product['unit_of_measure']; ?>">
                                                                        <?php echo $product['unit_of_measure']; ?></option>
                                                                    <!-- Optional: You can offer more units if applicable -->
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Sale Quantity</label>
                                                                <input type="number" name="sale_quantity"
                                                                    class="form-control" min="1"
                                                                    max="<?php echo $product['quantity']; ?>" value="1"
                                                                    id="saleQty_<?php echo $product['product_id']; ?>"
                                                                    oninput="updateTotalPrice(<?php echo $product['product_id']; ?>)"
                                                                    required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Actual Price Sold</label>
                                                                <input type="text" name="actual_price" class="form-control"
                                                                    id="totalPrice_<?php echo $product['product_id']; ?>"
                                                                    value="<?php echo $product['price']; ?>" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn text-black" style="background-color: #ABD29B;" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn text-black" style="background-color: #FFD531;">Add to Cart</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <i class="fa-solid fa-pen-to-square" title="edit pet" style="cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editProductModal<?php echo $product['product_id']; ?>"></i>

                                        <div class="modal fade" id="editProductModal<?php echo $product['product_id']; ?>"
                                            tabindex="-1"
                                            aria-labelledby="editProductModalLabel<?php echo $product['product_id']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/update_product.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Product:
                                                                <?php echo htmlspecialchars($product['name']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="product_id"
                                                                value="<?php echo $product['product_id']; ?>">

                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Name</label>
                                                                <input type="text" class="form-control" name="name"
                                                                    value="<?php echo htmlspecialchars($product['name']); ?>"
                                                                    required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="description"
                                                                    class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3"
                                                                    required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="price" class="form-label">Price</label>
                                                                <input type="number" step="0.01" class="form-control"
                                                                    name="price"
                                                                    value="<?php echo htmlspecialchars($product['price']); ?>"
                                                                    required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="unit_of_measure" class="form-label">Unit of
                                                                    Measure</label>
                                                                <input type="text" class="form-control"
                                                                    name="unit_of_measure"
                                                                    value="<?php echo htmlspecialchars($product['unit_of_measure']); ?>"
                                                                    required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="category" class="form-label">Category</label>
                                                                <select class="form-control" name="category" required>
                                                                    <option value="Veterinary Medicines & Treatments" <?php echo ($product['category'] == 'Veterinary Medicines & Treatments') ? 'selected' : ''; ?>>Veterinary
                                                                        Medicines & Treatments</option>
                                                                    <option value="Pet Food & Treats" <?php echo ($product['category'] == 'Pet Food & Treats') ? 'selected' : ''; ?>>Pet Food & Treats</option>
                                                                    <option value="Pet Accessories" <?php echo ($product['category'] == 'Pet Accessories') ? 'selected' : ''; ?>>Pet Accessories</option>
                                                                    <option value="Pet Housing & Bedding" <?php echo ($product['category'] == 'Pet Housing & Bedding') ? 'selected' : ''; ?>>Pet Housing & Bedding</option>
                                                                    <option value="Grooming Supplies" <?php echo ($product['category'] == 'Grooming Supplies') ? 'selected' : ''; ?>>Grooming Supplies</option>
                                                                    <option value="Cleaning & Sanitation" <?php echo ($product['category'] == 'Cleaning & Sanitation') ? 'selected' : ''; ?>>Cleaning & Sanitation</option>
                                                                    <option value="Pet Toys & Enrichment" <?php echo ($product['category'] == 'Pet Toys & Enrichment') ? 'selected' : ''; ?>>Pet Toys & Enrichment</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="quantity" class="form-label">Quantity</label>
                                                                <input type="number" class="form-control" name="quantity"
                                                                    value="<?php echo htmlspecialchars($product['quantity']); ?>"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn text-black" style="background-color: #ABD29B;" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn text-black" style="background-color: #FFD531;">Update Product</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <form method="POST" action="../actions/delete_product.php" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" class="btn btn-sm" title="Delete Product">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Table page navigation">
                    <ul class="pagination justify-content-end">
                        <li class="page-item <?php if ($page <= 1)
                                                    echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page)
                                                        echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php if ($page >= $totalPages)
                                                    echo 'disabled'; ?>">
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
                var myModal = new bootstrap.Modal(document.getElementById('addProduct'));
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

        function updateTotalPrice(productId) {
            const price = parseFloat(document.getElementById('price_' + productId).value);
            const qty = parseInt(document.getElementById('saleQty_' + productId).value) || 0;
            const total = (price * qty).toFixed(2);
            document.getElementById('totalPrice_' + productId).value = total;
        }

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