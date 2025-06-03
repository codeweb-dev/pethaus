<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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

$whereClause = "";
if (!empty($categoryFilter)) {
    $whereClause = "WHERE category = '$categoryFilter'";
}

// Update the count query
$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM products $whereClause");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

// Update the main data query
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <title>Product Inventory</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0 d-none d-md-block">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-3">Product Inventory</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class="w-auto d-flex gap-2">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for products...">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <form class="d-flex gap-2" method="GET">
                                <select name="category" class="form-select w-auto">
                                    <option value="">All Categories</option>
                                    <?php
                                    $categoryResult = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
                                    while ($cat = mysqli_fetch_assoc($categoryResult)) {
                                        $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($cat['category']) . "' $selected>" . htmlspecialchars($cat['category']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" class="btn bg-black text-white">
                                    <i class="fa-solid fa-filter"></i> Filter
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn bg-black text-white" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#addProduct">
                            <i class="fa-solid fa-plus"></i> Add new product
                        </button>

                        <div class="modal fade" id="addProduct" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_product.php">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new product</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter product name" name="name" id="name" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <div class="input-group">
                                                    <textarea class="form-control" id="description" placeholder="Enter product description" name="description" rows="3" required></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                                    <input type="number" class="form-control" placeholder="Enter product price" name="price" id="price" step="0.01" min="0" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="unit_of_measure" class="form-label">Unit of measure</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter product unit measure" name="unit_of_measure" id="unit_of_measure" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter product unit measure" name="category" id="category" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-boxes-stacked"></i></span>
                                                    <input type="number" class="form-control" placeholder="Enter product quantity" name="quantity" id="quantity" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Add new product</button>
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
                                <th class="p-3" scope="col">Name</th>
                                <th class="p-3" scope="col">Description</th>
                                <th class="p-3" scope="col">Price</th>
                                <th class="p-3" scope="col">Unit of measure</th>
                                <th class="p-3" scope="col">Category</th>
                                <th class="p-3" scope="col">Quantity</th>
                                <th class="p-3" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $product['product_id']; ?></td>
                                    <td class="p-3"><?php echo $product['name']; ?></td>
                                    <td class="p-3"><?php echo $product['description']; ?></td>
                                    <td class="p-3">₱<?php echo $product['price']; ?></td>
                                    <td class="p-3"><?php echo $product['unit_of_measure']; ?></td>
                                    <td class="p-3"><?php echo $product['category']; ?></td>
                                    <td class="p-3"><?php echo $product['quantity']; ?></td>
                                    <td class="p-3">
                                        <i class="fa-solid fa-pen-to-square" title="edit pet" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['product_id']; ?>"></i>

                                        <div class="modal fade" id="editProductModal<?php echo $product['product_id']; ?>" tabindex="-1" aria-labelledby="editProductModalLabel<?php echo $product['product_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/update_product.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Name</label>
                                                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="price" class="form-label">Price</label>
                                                                <input type="number" step="0.01" class="form-control" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="unit_of_measure" class="form-label">Unit of Measure</label>
                                                                <input type="text" class="form-control" name="unit_of_measure" value="<?php echo htmlspecialchars($product['unit_of_measure']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="category" class="form-label">Category</label>
                                                                <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="quantity" class="form-label">Quantity</label>
                                                                <input type="number" class="form-control" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn bg-black text-white">Update Product</button>
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
    </script>
</body>

</html>