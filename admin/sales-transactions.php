<?php
session_start();
include '../conn.php';

$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;

$dateFilter = '';
if ($fromDate && $toDate) {
    $dateFilter = "WHERE others_date BETWEEN '$fromDate' AND '$toDate'";
} elseif ($fromDate) {
    $dateFilter = "WHERE others_date >= '$fromDate'";
} elseif ($toDate) {
    $dateFilter = "WHERE others_date <= '$toDate'";
}

$salesQuery = mysqli_query($conn, "
    SELECT * FROM sales 
    $dateFilter 
    ORDER BY sale_id DESC
");

$sales = [];
while ($sale = mysqli_fetch_assoc($salesQuery)) {
    $sale_id = $sale['sale_id'];
    $items = mysqli_query($conn, "
        SELECT p.name AS product_name, ps.sale_quantity, ps.sale_price, ps.total_amount
        FROM product_sale ps
        JOIN products p ON p.product_id = ps.product_id
        WHERE ps.sale_id = $sale_id
    ");

    $total = 0;
    $products = [];

    while ($row = mysqli_fetch_assoc($items)) {
        $products[] = $row;
        $total += $row['total_amount'];
    }

    $sales[] = [
        'sale_id' => $sale_id,
        'date' => $sale['others_date'],
        'total' => $total,
        'items' => $products
    ];
}

$totalSales = count($sales);
$totalAmount = 0;
$totalProductsSold = 0;

foreach ($sales as $sale) {
    $totalAmount += $sale['total'];
    foreach ($sale['items'] as $item) {
        $totalProductsSold += $item['sale_quantity'];
    }
}
$averageSale = $totalSales > 0 ? ($totalAmount / $totalSales) : 0;

$grandQuery = mysqli_query($conn, "
    SELECT COUNT(DISTINCT s.sale_id) AS total_sales,
           SUM(ps.sale_quantity) AS total_products,
           SUM(ps.total_amount) AS total_revenue
    FROM sales s
    JOIN product_sale ps ON ps.sale_id = s.sale_id
");

$grandData = mysqli_fetch_assoc($grandQuery);
$grandTotalSales = $grandData['total_sales'] ?? 0;
$grandProducts = $grandData['total_products'] ?? 0;
$grandRevenue = $grandData['total_revenue'] ?? 0;
$grandAverage = $grandTotalSales > 0 ? ($grandRevenue / $grandTotalSales) : 0;
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
    <title>Sales Transaction</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-3">Sales Transaction</h3>

                <div class="d-flex justify-content-between mb-5">
                    <div class="w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-chart-line"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search sales transaction...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <h5 class="fw-bold">Sales Records</h5>
                        <table class="table table-bordered table-hover" id="salesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Total Amount (₱)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="salesBody">
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?= $sale['sale_id'] ?></td>
                                        <td><?= $sale['date'] ?></td>
                                        <td>₱<?= number_format($sale['total'], 2) ?></td>
                                        <td>
                                            <button
                                                class="btn bg-black text-white open-cart-btn"
                                                data-sale-id="<?= $sale['sale_id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal"
                                                data-items='<?= json_encode($sale['items']) ?>'
                                                data-total="<?= $sale['total'] ?>">
                                                <i class="fas fa-cart-plus"></i> Open cart
                                            </button>
                                        </td>
                                    </tr>

                                    <?php if (count($sales) === 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No sales found for selected date range.</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-5">
                        <h5 class="fw-bold">Sales Summary</h5>

                        <div class="card p-3 mb-3 shadow-sm">
                            <form class="mb-3" method="GET">
                                <div class="mb-2">
                                    <label for="fromDate" class="form-label">From:</label>
                                    <input type="date" id="fromDate" name="fromDate" class="form-control" value="<?= $_GET['fromDate'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="toDate" class="form-label">To:</label>
                                    <input type="date" id="toDate" name="toDate" class="form-control" value="<?= $_GET['toDate'] ?? '' ?>">
                                </div>
                                <button type="submit" class="btn btn-dark w-100">Filter</button>
                            </form>

                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>No. of Sales:</span> <strong><?= $totalSales ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Product Sold:</span> <strong><?= $totalProductsSold ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total (₱):</span> <strong>₱<?= number_format($totalAmount, 2) ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Average (₱):</span> <strong>₱<?= number_format($averageSale, 2) ?></strong>
                                </li>
                            </ul>

                            <h6 class="fw-bold mt-3">Overall Summary</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total No. of Sales:</span> <strong><?= $grandTotalSales ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Product Sold:</span> <strong><?= $grandProducts ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Grand Total (₱):</span> <strong>₱<?= number_format($grandRevenue, 2) ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Average (₱):</span> <strong>₱<?= number_format($grandAverage, 2) ?></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="detailsModalLabel">Cart Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price (₱)</th>
                                            <th>Subtotal (₱)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartItemsBody"></tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td class="fw-bold" id="cartTotal"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.open-cart-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const items = JSON.parse(btn.getAttribute('data-items'));
                const total = btn.getAttribute('data-total');
                const tbody = document.getElementById('cartItemsBody');
                const totalEl = document.getElementById('cartTotal');

                tbody.innerHTML = '';
                items.forEach(item => {
                    tbody.innerHTML += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.sale_quantity}</td>
                    <td>₱${parseFloat(item.sale_price).toFixed(2)}</td>
                    <td>₱${parseFloat(item.total_amount).toFixed(2)}</td>
                </tr>`;
                });

                totalEl.textContent = '₱' + parseFloat(total).toFixed(2);
            });
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#salesTable tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
    <?php include('../components/script.php'); ?>
</body>

</html>