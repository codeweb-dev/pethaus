<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="../assets/custom-nav.css">

<ul class="custom-nav">
    <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
        <a href="dashboard.php">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
    </li>
    <li class="<?= $current_page === 'pet-records.php' ? 'active' : '' ?>">
        <a href="pet-records.php">
            <i class="fa-solid fa-box"></i> Pet Records
        </a>
    </li>
    <li class="<?= $current_page === 'pet-owner-profiles.php' ? 'active' : '' ?>">
        <a href="pet-owner-profiles.php">
            <i class="fa-solid fa-user"></i> Pet Owner Profiles
        </a>
    </li>
    <li class="<?= $current_page === 'medical-records.php' ? 'active' : '' ?>">
        <a href="medical-records.php">
            <i class="fa-solid fa-file-medical"></i>
            Medical Records
        </a>
    </li>
    <li class="<?= $current_page === 'medical-bills.php' ? 'active' : '' ?>">
        <a href="medical-bills.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> 
            Medical Bills
        </a>
    </li>
    <li class="<?= $current_page === 'product-inventory.php' ? 'active' : '' ?>">
        <a href="product-inventory.php">
            <i class="fa-solid fa-boxes-stacked"></i> Product Inventory
        </a>
    </li>
    <li class="<?= $current_page === 'sales-transactions.php' ? 'active' : '' ?>">
        <a href="sales-transactions.php">
            <i class="fa-solid fa-chart-line"></i>
            Sales Transactions
        </a>
    </li>
    <li class="<?= $current_page === 'open-cart.php' ? 'active' : '' ?>">
        <a href="open-cart.php">
            <i class="fa-solid fa-cart-plus"></i> 
            Open Cart
        </a>
    </li>
    <li class="<?= $current_page === 'pet-queue.php' ? 'active' : '' ?>">
        <a href="pet-queue.php">
            <i class="fa-solid fa-clipboard-list"></i>
            Pet Queue
        </a>
    </li>
    <?php if ($_SESSION['type'] === 'admin'): ?>
        <li class="<?= $current_page === 'pethaus-staff.php' ? 'active' : '' ?>">
            <a href="pethaus-staff.php">
                <i class="fa-solid fa-user-gear"></i> Pethaus Staff
            </a>
        </li>
    <?php endif; ?>
    <li>
        <a href="../logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </li>
</ul>