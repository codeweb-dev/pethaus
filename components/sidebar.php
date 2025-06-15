<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="../assets/custom-nav.css">
<script defer src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome -->
<style>
    /* Responsive Sidebar */
    .sidebar {
        height: 100vh;
        transition: width 0.3s;
        overflow-x: hidden;
        background: #fff;
        border-right: 1px solid #dee2e6;
    }

    .sidebar.collapsed {
        width: 80px;
    }

    .sidebar.expanded {
        width: 255px;
    }

    .sidebar .custom-nav li a {
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        overflow: hidden;
    }

    .sidebar.collapsed .custom-nav li a span {
        display: none;
    }

    .toggle-btn {
        cursor: pointer;
        padding: 0.2rem 0.7rem;
        border-radius: 999px;
        background-color: black;
    }

    .sidebar.no-transition {
        transition: none !important;
    }
</style>

<!-- Sidebar with Toggle -->
<div class="sidebar expanded d-none d-md-block position-relative" id="sidebar">
    <div class="toggle-btn" style="position: absolute; right: 10px; top: 10px" onclick="toggleSidebar()">
        <i class="fa fa-angle-left text-white" id="toggle-icon"></i>
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

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('toggle-icon');

    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        sidebar.classList.toggle('expanded');

        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');

        // Toggle icon direction
        toggleIcon.classList.toggle('fa-angle-left');
        toggleIcon.classList.toggle('fa-angle-right');
    }

    // Load saved state on page load, without animation
    window.addEventListener('DOMContentLoaded', () => {
        sidebar.classList.add('no-transition'); // Prevent animation on load

        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.remove('expanded');
            sidebar.classList.add('collapsed');
            toggleIcon.classList.remove('fa-angle-left');
            toggleIcon.classList.add('fa-angle-right');
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
            toggleIcon.classList.remove('fa-angle-right');
            toggleIcon.classList.add('fa-angle-left');
        }

        // Allow transitions for future toggles
        setTimeout(() => {
            sidebar.classList.remove('no-transition');
        }, 10); // 10ms delay is enough
    });
</script>