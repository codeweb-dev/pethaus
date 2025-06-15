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
$staff .= isset($_SESSION['middle_name']) ? ' ' . $_SESSION['middle_name'][0] . '.' : '';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <title>Open Cart</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
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
                            <thead class="table-dark">
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
                                                <input type="hidden" name="product_sale_id" value="<?= $item['product_sale_id'] ?>">
                                                <button name="action" value="decrease" class="btn btn-sm btn-outline-secondary">-</button>
                                                <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" value="<?= $item['quantity'] ?>" readonly>
                                                <button name="action" value="increase" class="btn btn-sm btn-outline-secondary">+</button>
                                            </form>
                                        </td>
                                        <td>₱<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['unit_of_measure'] ?></td>
                                        <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                        <td>
                                            <form method="post" action="../actions/remove_cart_item.php">
                                                <input type="hidden" name="product_sale_id" value="<?= $item['product_sale_id'] ?>">
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
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
                            <button class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#placeOrderModal">Check Out</button>
                        </div>
                    </div>
                </div>

                <!-- Place Order Modal -->
                <div class="modal fade" id="placeOrderModal" tabindex="-1" aria-labelledby="placeOrderModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="placeOrderModalLabel">View Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Clinic:</strong> PetHaus Veterinary Clinic</p>
                                    <p class="mb-1"><strong>Prepared By:</strong> <?php echo $staff ?></p>
                                    <p class="mb-3"><strong>Date:</strong> <?= date('F j, Y'); ?></p>
                                </div>

                                <table class="table table-bordered">
                                    <thead class="table-secondary">
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
                                        <tr class="table-dark">
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

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>
</body>

</html>