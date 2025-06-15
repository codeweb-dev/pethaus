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
    <title>Pet Queue</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-4">Pet Queue</h3>

                <!-- REGISTRATION PART -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">Registration</div>
                    <div class="card-body">
                        <form class="row g-3">
                            <div class="col-md-4">
                                <label for="ownerName" class="form-label">Owner Name</label>
                                <input type="text" class="form-control" id="ownerName" placeholder="Juan Dela Cruz">
                            </div>
                            <div class="col-md-4">
                                <label for="petName" class="form-label">Pet Name</label>
                                <input type="text" class="form-control" id="petName" placeholder="Buddy">
                            </div>
                            <div class="col-md-4">
                                <label for="serviceType" class="form-label">Service Type</label>
                                <select class="form-select" id="serviceType">
                                    <option selected disabled>Select service...</option>
                                    <option value="Checkup">Checkup</option>
                                    <option value="Vaccination">Vaccination</option>
                                    <option value="Grooming">Grooming</option>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MANAGE QUEUE -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">Manage Queue</div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>Owner</th>
                                    <th>Pet</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example row -->
                                <tr>
                                    <td>1</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>Buddy</td>
                                    <td>Checkup</td>
                                    <td><span class="badge bg-warning text-dark">Waiting</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Serve</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Ana Santos</td>
                                    <td>Max</td>
                                    <td>Vaccination</td>
                                    <td><span class="badge bg-success">In Service</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary">Complete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Michael Lee</td>
                                    <td>Snowy</td>
                                    <td>Grooming</td>
                                    <td><span class="badge bg-secondary">Done</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DISPLAY QUEUE -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">Display Queue</div>
                    <div class="card-body">
                        <h4 class="text-primary mb-4">Currently Serving: <span class="fw-bold text-dark">Max (Ana Santos)</span></h4>

                        <div class="row row-cols-1 row-cols-md-3 g-3">
                            <div class="col">
                                <div class="card p-3 shadow-sm">
                                    <h6 class="fw-bold mb-1">Buddy <span class="badge bg-warning text-dark">Waiting</span></h6>
                                    <small>Owner: Juan Dela Cruz</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card p-3 shadow-sm">
                                    <h6 class="fw-bold mb-1">Max <span class="badge bg-success">In Service</span></h6>
                                    <small>Owner: Ana Santos</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card p-3 shadow-sm">
                                    <h6 class="fw-bold mb-1">Snowy <span class="badge bg-secondary">Done</span></h6>
                                    <small>Owner: Michael Lee</small>
                                </div>
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