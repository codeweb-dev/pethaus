<?php session_start(); ?>

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
                    <!-- LEFT SIDE: Sales Transactions Table -->
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
                            <tbody>
                                <!-- Sample row -->
                                <tr>
                                    <td>1001</td>
                                    <td>2025-06-15</td>
                                    <td>₱1,850.00</td>
                                    <td>
                                        <button class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#detailsModal">
                                            <i class="fas fa-cart-plus"></i> Open cart
                                        </button>
                                    </td>
                                </tr>
                                <!-- Additional rows will go here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- RIGHT SIDE: Sales Summary -->
                    <div class="col-md-5">
                        <h5 class="fw-bold">Sales Summary</h5>

                        <div class="card p-3 mb-3 shadow-sm">
                            <div class="mb-2">
                                <label for="fromDate" class="form-label">From:</label>
                                <input type="date" id="fromDate" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="toDate" class="form-label">To:</label>
                                <input type="date" id="toDate" class="form-control">
                            </div>
                            <button class="btn btn-dark w-100 mb-3">Filter</button>

                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>No. of Sales:</span> <strong>12</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Product Sold:</span> <strong>56</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total (₱):</span> <strong>₱8,460.00</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Average (₱):</span> <strong>₱705.00</strong>
                                </li>
                            </ul>

                            <h6 class="fw-bold mt-3">Overall Summary</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total No. of Sales:</span> <strong>143</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Product Sold:</span> <strong>692</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Grand Total (₱):</span> <strong>₱143,880.00</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Average (₱):</span> <strong>₱1,005.45</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Cart Modal -->
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
                                    <tbody>
                                        <tr>
                                            <td>Dog Food</td>
                                            <td>2</td>
                                            <td>₱400</td>
                                            <td>₱800</td>
                                        </tr>
                                        <tr>
                                            <td>Vitamin Syrup</td>
                                            <td>1</td>
                                            <td>₱450</td>
                                            <td>₱450</td>
                                        </tr>
                                        <!-- More rows dynamically -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td class="fw-bold">₱1,250</td>
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

    <?php include('../components/script.php'); ?>
</body>

</html>