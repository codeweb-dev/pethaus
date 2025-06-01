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
  <title>Admin Dashboard</title>
</head>

<body>
  <?php include('../components/navbar.php'); ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 text-white min-vh-100 p-0 d-none d-md-block">
        <?php include('../components/sidebar.php'); ?>
      </div>

      <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
        <h3 class="fw-bold">Dashboard</h3>
        <h6 class="text-muted mb-4">Welcome to PetHaus Animal Clinic Management System</h6>

        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card text-white bg-black text-white">
              <div class="card-body">
                <p class="mb-2">Total Pets Registered</p>
                <h4 class="mb-0">0</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card text-white bg-black text-white">
              <div class="card-body">
                <p class="mb-2">Total Pet Owners</p>
                <h4 class="mb-0">0</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card text-white bg-black text-white">
              <div class="card-body">
                <p class="mb-2">Queued Pets Today</p>
                <h4 class="mb-0">0</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card text-white bg-black text-white">
              <div class="card-body">
                <p class="mb-2">Total Sales Today</p>
                <h4 class="mb-0">0</h4>
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