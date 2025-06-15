<?php
session_start();
include('../conn.php');

// Count total pets
$petResult = $conn->query("SELECT COUNT(*) AS total_pets FROM pet_records");
$totalPets = ($petResult && $petResult->num_rows > 0) ? $petResult->fetch_assoc()['total_pets'] : 0;

// Count total pet owners
$ownerResult = $conn->query("SELECT COUNT(*) AS total_owners FROM pet_owner_records");
$totalOwners = ($ownerResult && $ownerResult->num_rows > 0) ? $ownerResult->fetch_assoc()['total_owners'] : 0;

// Get today's total sales amount
$salesTodayQuery = $conn->query("
    SELECT SUM(ps.total_amount) AS total_sales_today
    FROM sales s
    JOIN product_sale ps ON ps.sale_id = s.sale_id
    WHERE DATE(s.others_date) = CURDATE()
");

$totalSalesToday = 0;
if ($salesTodayQuery && $salesTodayQuery->num_rows > 0) {
  $totalSalesToday = $salesTodayQuery->fetch_assoc()['total_sales_today'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="../assets/images/pethaus_logo.png" />
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/style.css">

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
      background-color: black;
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
      background-color: black;
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
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <div id="main-content">
      <h3 class="fw-bold">Dashboard</h3>
      <h6 class="text-muted mb-4">Welcome to PetHaus Animal Clinic Management System</h6>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card text-white bg-black">
            <div class="card-body">
              <p class="mb-2">Total Pets Registered</p>
              <h4 class="mb-0"><?= $totalPets ?></h4>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card text-white bg-black">
            <div class="card-body">
              <p class="mb-2">Total Pet Owners</p>
              <h4 class="mb-0"><?= $totalOwners ?></h4>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card text-white bg-black">
            <div class="card-body">
              <p class="mb-2">Total Sales Today</p>
              <h4 class="mb-0">₱<?= number_format($totalSalesToday, 2) ?></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar JS -->
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
</body>

</html>