<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #296849;">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-center" style="height: 50px;">
            <img src="../assets/images/pethaus_logo.png" alt="website-logo" style="width: 50px;">
            <p class="text-white fs-5 fw-bold mb-0 ms-2">PetHaus</p>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-2">
            <i class="fa-solid fa-user-tie text-white fs-4"></i>
            <p class="text-white mb-0 text-uppercase">Hello <?= htmlspecialchars($_SESSION['type']) ?>, <?= htmlspecialchars($_SESSION['username']) ?></p>
        </div>


        <button class="btn text-white d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title fw-bold">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body">
                <?php include('../components/sidebar-content.php'); ?>
            </div>
        </div>
    </div>
</nav>