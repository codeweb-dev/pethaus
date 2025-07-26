<?php
session_start();
include('../conn.php');

$bills = [];

$query = "
    SELECT 
        mr.medical_record_id,
        mr.date_started,
        mr.treatment_charge,
        mr.prescription_charge,
        mr.others_charge,
        mb.total_amount AS bill_total,
        mb.billing_date,
        mb.status
    FROM medical_records mr
    INNER JOIN medical_bill mb ON mr.medical_record_id = mb.medical_record_id
    ORDER BY mr.medical_record_id DESC
";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $treat = floatval($row['treatment_charge']);
        $presc = floatval($row['prescription_charge']);
        $other = floatval($row['others_charge']);
        $total = $treat + $presc + $other;

        $row['calculated_total'] = $total;
        $row['balance'] = $total - floatval($row['bill_total']);
        $bills[] = $row;
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
    <title>Medical Bills</title>
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
                <h3 class="fw-bold mb-3">Medical Bills</h3>

                <div class="d-flex justify-content-between mb-5">
                    <div class="w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search medical bills...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn text-black" style="background-color: #FFD531;" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>
                    </div>
                </div>

                <div>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">DATE</th>
                                <th scope="col">TREATMENT</th>
                                <th scope="col">PRESCRIPTION</th>
                                <th scope="col">OTHERS</th>
                                <th scope="col">BILL TOTAL</th>
                                <th scope="col">PAYMENT TOTAL</th>
                                <th scope="col">BALANCE</th>
                                <th scope="col">STATUS</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bills)) : ?>
                                <?php foreach ($bills as $bill) : ?>
                                    <tr>
                                        <td><?php echo $bill['medical_record_id']; ?></td>
                                        <td><?php echo date('F j, Y', strtotime($bill['billing_date'] ?? $bill['date_started'])); ?></td>
                                        <td>₱<?php echo number_format($bill['treatment_charge'], 2); ?></td>
                                        <td>₱<?php echo number_format($bill['prescription_charge'], 2); ?></td>
                                        <td>₱<?php echo number_format($bill['others_charge'], 2); ?></td>
                                        <td class="fw-bold text-primary">₱<?php echo number_format($bill['calculated_total'], 2); ?></td>
                                        <td>₱<?php echo number_format($bill['balance'] ?? 0, 2); ?></td>
                                        <td class="<?php echo ($bill['bill_total'] > 0 ? 'text-danger' : 'text-success'); ?>">
                                            ₱<?php echo number_format($bill['bill_total'], 2); ?>
                                        </td>
                                        <td>
                                            <?php if ($bill['bill_total'] <= 0): ?>
                                                <span class="badge rounded-pill bg-success">FULLY PAID</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger">PENDING BALANCE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="d-flex gap-3 align-items-center">
                                            <i
                                                class="text-black fa-solid fa-money-bill"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addPaymentModal"
                                                data-id="<?php echo $bill['medical_record_id']; ?>"
                                                data-balance="<?php echo $bill['balance']; ?>"
                                                data-total="<?php echo $bill['bill_total']; ?>">
                                            </i>

                                            <i
                                                class="fa-solid fa-scroll view-history"
                                                data-id="<?php echo $bill['medical_record_id']; ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentHistoryModal">
                                            </i>

                                            <a href="../actions/view_bill.php?record_id=<?php echo $bill['medical_record_id']; ?>" class="text-black"><i class="fa-solid fa-file-medical"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center">No medical bills found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="paymentForm" action="../actions/process_add_payment.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="medical_record_id" id="modalRecordId">

                    <div class="mb-3">
                        <label class="form-label">Payment Amount</label>
                        <input
                            type="number"
                            step="0.01"
                            name="payment_amount"
                            id="modalPaymentAmount"
                            class="form-control"
                            required>
                        <div class="form-text text-danger d-none" id="overpayWarning">Payment exceeds current balance!</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn text-black" style="background-color: #FFD531;">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount (₱)</th>
                                <th>Date Paid</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="3" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll("table tbody tr").forEach(row => {
                const match = row.textContent.toLowerCase().includes(val);
                row.style.display = match ? '' : 'none';
            });
        });

        const modal = document.getElementById('addPaymentModal');
        const recordIdInput = document.getElementById('modalRecordId');
        const paymentInput = document.getElementById('modalPaymentAmount');
        const overpayWarning = document.getElementById('overpayWarning');

        const totalInput = document.createElement('input');
        totalInput.type = 'hidden';
        totalInput.id = 'calculatedTotalInput';
        document.body.appendChild(totalInput);

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const recordId = button.getAttribute('data-id');
            const total = parseFloat(button.getAttribute('data-total')) || 0;

            recordIdInput.value = recordId;
            totalInput.value = total.toFixed(2);
            paymentInput.value = '';
            overpayWarning.classList.add('d-none');
        });

        paymentInput.addEventListener('input', function() {
            const total = parseFloat(totalInput.value);
            const payment = parseFloat(paymentInput.value) || 0;

            if (payment > total) {
                overpayWarning.classList.remove('d-none');
            } else {
                overpayWarning.classList.add('d-none');
            }
        });

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const total = parseFloat(totalInput.value); // bill_total
            const payment = parseFloat(paymentInput.value) || 0;

            if (total <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: 'Already Paid',
                    text: 'This bill is already fully paid. No payment is needed.',
                });
                return;
            }

            if (payment <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Payment',
                    text: 'Payment amount must be greater than ₱0.00.',
                });
                return;
            }

            if (payment > total) {
                e.preventDefault();
                overpayWarning.classList.remove('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Overpayment',
                    text: 'Payment exceeds the allowed total bill.',
                });
            }
        });

        document.querySelectorAll('.view-history').forEach(button => {
            button.addEventListener('click', () => {
                const recordId = button.getAttribute('data-id');
                const tbody = document.getElementById('historyTableBody');

                tbody.innerHTML = `<tr><td colspan="3" class="text-center">Loading...</td></tr>`;

                fetch(`../actions/fetch_payment_history.php?id=${recordId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="3" class="text-center">No history found.</td></tr>`;
                        } else {
                            tbody.innerHTML = '';
                            data.forEach((entry, index) => {
                                tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>₱${parseFloat(entry.payment_amount).toFixed(2)}</td>
                                <td>${entry.payment_date}</td>
                            </tr>`;
                            });
                        }
                    })
                    .catch(() => {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Failed to load history.</td></tr>`;
                    });
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

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>
</body>

</html>