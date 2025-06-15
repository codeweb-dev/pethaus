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
                                <!-- Sample row -->
                                <tr>
                                    <td>Dog Shampoo</td>
                                    <td class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-secondary">-</button>
                                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" value="1">
                                        <button class="btn btn-sm btn-outline-secondary">+</button>
                                    </td>
                                    <td>₱150</td>
                                    <td>Bottle</td>
                                    <td>₱150</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                <!-- Repeat for other products -->
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td colspan="2">₱150</td>
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
                                    <p class="mb-1"><strong>Prepared By:</strong> Dr. Marlon Santos</p>
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
                                        <tr>
                                            <td>Dog Shampoo</td>
                                            <td>1</td>
                                            <td>₱150</td>
                                            <td>₱150</td>
                                        </tr>
                                        <!-- More rows -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td class="fw-bold">₱150</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button class="btn bg-black text-white">Place Order</button>
                                <button class="btn bg-black text-white" data-bs-dismiss="modal">Close</button>
                                <button class="btn bg-black text-white"><i class="fas fa-print"></i> Print</button>
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