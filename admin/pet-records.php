<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM pet_records");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$pets = [];
$query = "
    SELECT pr.*, po.first_name, po.middle_name, po.last_name
    FROM pet_records pr
    LEFT JOIN pet_owner_records po ON pr.owner_id = po.owner_id
    ORDER BY pr.pet_id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pets[] = $row;
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <title>Pet Record</title>
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

        .sidebar.collapsed .custom-nav li a span {
            display: none;
        }

        .sidebar .custom-nav li a:hover {
            background-color: black;
            color: white;
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

    <div class="main-wrapper d-flex">
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

        <div class="main-wrapper d-flex p-4">
            <div class="w-auto">
                <h3 class="fw-bold mb-3">Pet Record</h3>
                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class="input-group mb-3 w-auto">
                        <span class="input-group-text"><i class="fa-solid fa-box"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search for pets...">
                    </div>

                    <div class="d-flex gap-2">
                        <div>
                            <button class="btn bg-black text-white" onclick="location.reload();">
                                <i class="fa-solid fa-arrows-rotate"></i> Refresh
                            </button>

                            <button type="button" class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#addNewPet">
                                <i class="fa-solid fa-plus"></i> Add new pet
                            </button>
                        </div>

                        <div class="modal fade" id="addNewPet" tabindex="-1" aria-labelledby="addNewPetLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_pet.php" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new pet</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body row">
                                            <div class="mb-3 col-md-6">
                                                <label for="owner_id" class="form-label">Owner</label>

                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <select name="owner_id" class="form-control <?php echo isset($errors['owner_id']) ? 'is-invalid' : ''; ?>">
                                                            <option value="" disabled <?php echo empty($old['owner_id']) ? 'selected' : ''; ?>>Select Owner</option>
                                                            <?php
                                                            $owners = mysqli_query($conn, "
                                                                    SELECT owner_id, CONCAT_WS(' ', first_name, middle_name, last_name) AS full_name 
                                                                    FROM pet_owner_records 
                                                                    ORDER BY first_name
                                                                ");

                                                            while ($row = mysqli_fetch_assoc($owners)) {
                                                                $selected = ($old['owner_id'] ?? '') == $row['owner_id'] ? 'selected' : '';
                                                                echo '<option value="' . $row['owner_id'] . '" ' . $selected . '>' . htmlspecialchars($row['full_name']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                        <?php if (isset($errors['owner_id'])): ?>
                                                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['owner_id']); ?></div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <a href="pet-owner-profiles.php">
                                                        <button type="button" class="btn bg-black text-white"><i class="fa-solid fa-plus"></i> </button>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="name" class="form-label">Name</label>
                                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                                    placeholder="Enter pet name" name="name" id="name"
                                                    value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>">
                                                <?php if (isset($errors['name'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="species" class="form-label">Species</label>
                                                <select class="form-control <?php echo isset($errors['species']) ? 'is-invalid' : ''; ?>" name="species" id="species" onchange="toggleOtherSpeciesInput()">
                                                    <option value="" disabled selecteD>Select species</option>
                                                    <option value="Dog">Dog</option>
                                                    <option value="Cat">Cat</option>
                                                    <option value="Chicken">Chicken</option>
                                                    <option value="Bird">Bird</option>
                                                    <option value="Fish">Fish</option>
                                                    <option value="Duck">Duck</option>
                                                    <option value="Rabbit">Rabbit</option>
                                                    <option value="Guinea Pig">Guinea Pig</option>
                                                    <option value="Hamster">Hamster</option>
                                                    <option value="Turtle / Tortoise">Turtle / Tortoise</option>
                                                    <option value="Lizard">Lizard</option>
                                                    <option value="Snake">Snake</option>
                                                    <option value="Horse">Horse</option>
                                                    <option value="Pig">Pig</option>
                                                    <option value="Goat">Goat</option>
                                                    <option value="Cow">Cow</option>
                                                    <option value="Carabao">Carabao</option>
                                                    <option value="Others">Others (please specify)</option>
                                                </select>
                                                <?php if (isset($errors['species'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['species']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Hidden input for 'Others' text field -->
                                            <div class="mb-3 col-md-6 <?php echo (isset($old['species']) && $old['species'] === 'Others') ? '' : 'd-none'; ?>" id="otherSpeciesContainer">
                                                <label for="other_species" class="form-label">Please specify species</label>
                                                <input type="text" class="form-control <?php echo isset($errors['other_species']) ? 'is-invalid' : ''; ?>"
                                                    name="other_species" id="other_species" placeholder="Enter species"
                                                    value="<?php echo htmlspecialchars($old['other_species'] ?? ''); ?>">
                                                <?php if (isset($errors['other_species'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['other_species']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6" id="breed-container">
                                                <label for="breed" class="form-label">Breed</label>
                                                <input type="text" class="form-control <?php echo isset($errors['breed']) ? 'is-invalid' : ''; ?>" placeholder="Enter breed" name="breed" id="breed">
                                                <?php if (isset($errors['breed'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['breed']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="color" class="form-label">Color</label>
                                                <input type="text" class="form-control <?php echo isset($errors['color']) ? 'is-invalid' : ''; ?>" placeholder="Enter color" name="color" id="color" value="<?php echo htmlspecialchars($old['color'] ?? ''); ?>">
                                                <?php if (isset($errors['color'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['color']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="sex" class="form-label">Sex</label>
                                                <select class="form-select <?php echo isset($errors['sex']) ? 'is-invalid' : ''; ?>" name="sex" id="sex">
                                                    <option value="" disabled <?php echo empty($old['sex']) ? 'selected' : ''; ?>>Select sex</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Unknown">Unknown</option>
                                                </select>

                                                <?php if (isset($errors['sex'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['sex']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="birthdate" class="form-label">Birthdate</label>
                                                <input type="date" class="form-control <?php echo isset($errors['birthdate']) ? 'is-invalid' : ''; ?>" placeholder="Enter birthdate" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($old['birthdate'] ?? ''); ?>">
                                                <?php if (isset($errors['birthdate'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="age" class="form-label">Age</label>
                                                <input type="text" class="form-control <?php echo isset($errors['age']) ? 'is-invalid' : ''; ?>" placeholder="Enter age" name="age" id="age" value="<?php echo htmlspecialchars($old['age'] ?? ''); ?>">
                                                <?php if (isset($errors['age'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['age']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="markings" class="form-label">Markings</label>
                                                <input type="text" class="form-control <?php echo isset($errors['markings']) ? 'is-invalid' : ''; ?>" placeholder="Enter markings" name="markings" id="markings" value="<?php echo htmlspecialchars($old['markings'] ?? ''); ?>">
                                                <?php if (isset($errors['markings'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['markings']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Photo Option</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="photo_option" id="option_upload" value="upload" checked>
                                                    <label class="form-check-label" for="option_upload">Upload Photo</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="photo_option" id="option_capture" value="capture">
                                                    <label class="form-check-label" for="option_capture">Take Photo</label>
                                                </div>

                                                <div id="upload_section" class="mt-2">
                                                    <input type="file" class="form-control <?php echo isset($errors['photo']) ? 'is-invalid' : ''; ?>" name="photo" id="photo" accept="image/*">
                                                    <?php if (isset($errors['photo'])): ?>
                                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['photo']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="capture_section" class="d-none">
                                            <div class="d-flex align-items-center justify-content-center gap-3">
                                                <button type="button" class="btn bg-black text-white" onclick="startWebcam()">Start Camera</button>
                                                <button type="button" class="btn bg-black text-white" onclick="stopWebcam()">Stop Camera</button>
                                            </div>
                                            <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                                <div id="my_camera" class="mt-2"></div>
                                                <div id="results" class="mt-2"></div>
                                            </div>
                                            <input type="hidden" name="captured_image" id="captured_image">
                                            <div class="mt-2 mb-3 d-flex align-items-center justify-content-center">
                                                <button type="button" class="btn bg-black text-white" onclick="takeSnapshot()">Capture & Use</button>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Add new pet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th class="p-3" scope="col">ID</th>
                                <th class="p-3" scope="col">Name</th>
                                <th class="p-3" scope="col">Species</th>
                                <th class="p-3" scope="col">Breed</th>
                                <th class="p-3" scope="col">Color</th>
                                <th class="p-3" scope="col">Sex</th>
                                <th class="p-3" scope="col">Birthdate</th>
                                <th class="p-3" scope="col">Age</th>
                                <th class="p-3" scope="col">Markings</th>
                                <th class="p-3" scope="col">Owner</th>
                                <th class="p-3" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($pets as $pet): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $pet['pet_id']; ?></td>
                                    <td class="p-3"><?php echo $pet['name']; ?></td>
                                    <td class="p-3"><?php echo $pet['species']; ?></td>
                                    <td class="p-3"><?php echo $pet['breed']; ?></td>
                                    <td class="p-3"><?php echo $pet['color']; ?></td>
                                    <td class="p-3"><?php echo $pet['sex']; ?></td>
                                    <?php
                                    $date = new DateTime($pet['birthdate']);
                                    $formattedDate = $date->format('F j, Y');
                                    ?>
                                    <td class="p-3"><?php echo $formattedDate; ?></td>
                                    <td class="p-3"><?php echo $pet['age']; ?></td>
                                    <td class="p-3"><?php echo $pet['markings']; ?></td>
                                    <td class="p-3">
                                        <?php
                                        echo $pet['first_name'] . ' ';
                                        echo !empty($pet['middle_name']) ? $pet['middle_name'][0] . '. ' : '';
                                        echo $pet['last_name'];
                                        ?>
                                    </td>

                                    <td class="d-flex gap-3 p-3">
                                        <i class="fa-solid fa-images" title="view photo" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#photoModal<?php echo $pet['pet_id']; ?>"></i>

                                        <div class="modal fade" id="photoModal<?php echo $pet['pet_id']; ?>" tabindex="-1" aria-labelledby="photoModalLabel<?php echo $pet['pet_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="photoModalLabel<?php echo $pet['pet_id']; ?>">Photo of <?php echo $pet['name']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <?php if (!empty($pet['photo']) && file_exists($pet['photo'])): ?>
                                                            <img src="<?php echo $pet['photo']; ?>" alt="Pet Photo" class="img-fluid rounded">
                                                        <?php else: ?>
                                                            <p class="text-muted">No photo uploaded for this pet.</p>
                                                        <?php endif; ?>

                                                        <div class="d-flex flex-column gap-2">
                                                            <form method="POST" action="../actions/update_pet_photo.php" enctype="multipart/form-data" class="mt-3 d-flex flex-column gap-2">
                                                                <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">
                                                                <input type="file" name="photo" class="form-control" accept="image/*" required>
                                                                <button type="submit" class="btn bg-black text-white w-100">Upload new photo</button>
                                                            </form>

                                                            <form method="POST" action="../actions/delete_pet_photo.php" onsubmit="return confirm('Are you sure you want to delete the photo?');">
                                                                <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">
                                                                <button type="submit" class="btn btn-danger w-100">Delete Photo</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-file-invoice-dollar text-black view-medical-records"
                                            style="cursor: pointer;"
                                            data-id="<?php echo $pet['pet_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($pet['name']); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#medicalRecordsModal"
                                            title="View Medical Records">
                                        </i>
                                        <i class="fa-solid fa-pen-to-square" title="edit pet" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editPetModal<?php echo $pet['pet_id']; ?>"></i>

                                        <div class="modal fade" id="editPetModal<?php echo $pet['pet_id']; ?>" tabindex="-1" aria-labelledby="editPetModalLabel<?php echo $pet['pet_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/update_pet.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editPetModalLabel<?php echo $pet['pet_id']; ?>">Edit Pet: <?php echo $pet['name']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body row">
                                                            <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">

                                                            <div class="mb-3 col-md-6">
                                                                <label for="name" class="form-label">Name</label>
                                                                <input type="text" class="form-control" name="name" value="<?php echo $pet['name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="species" class="form-label">Species</label>
                                                                <input type="text" class="form-control" name="species" value="<?php echo $pet['species']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="breed" class="form-label">Breed</label>
                                                                <input type="text" class="form-control" name="breed" value="<?php echo $pet['breed']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="color" class="form-label">Color</label>
                                                                <input type="text" class="form-control" name="color" value="<?php echo $pet['color']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="sex" class="form-label">Sex</label>
                                                                <select name="sex" class="form-control" required>
                                                                    <option value="Male" <?php if ($pet['sex'] == 'Male') echo 'selected'; ?>>Male</option>
                                                                    <option value="Female" <?php if ($pet['sex'] == 'Female') echo 'selected'; ?>>Female</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="birthdate" class="form-label">Birthdate</label>
                                                                <input type="date" class="form-control" name="birthdate" value="<?php echo $pet['birthdate']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="age" class="form-label">Age</label>
                                                                <input type="text" class="form-control" name="age" value="<?php echo $pet['age']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="markings" class="form-label">Markings</label>
                                                                <input type="text" class="form-control" name="markings" value="<?php echo $pet['markings']; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn bg-black text-white">Update Pet</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Table page navigation">
                    <ul class="pagination justify-content-end">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="medicalRecordsModal" tabindex="-1" aria-labelledby="medicalRecordsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="modalPetName"></span>'s Medical Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Description</th>
                                <th>Weight</th>
                                <th>Temperature</th>
                                <th>Complaint</th>
                            </tr>
                        </thead>
                        <tbody id="medicalRecordsTable">
                            <tr>
                                <td colspan="8" class="text-center">Select a pet to view medical records</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['modal']) && $_GET['modal'] === 'add' && !empty($errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addNewPet'));
                myModal.show();
            });
        </script>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.view-medical-records').forEach(icon => {
            icon.addEventListener('click', () => {
                const petId = icon.getAttribute('data-id');
                const petName = icon.getAttribute('data-name');
                const modalTitle = document.getElementById('modalPetName');
                const tableBody = document.getElementById('medicalRecordsTable');

                modalTitle.textContent = petName;
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center">Loading...</td></tr>`;

                fetch(`../actions/fetch_medical_records.php?pet_id=${petId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="8" class="text-center">No records found.</td></tr>`;
                        } else {
                            tableBody.innerHTML = '';
                            data.forEach(row => {
                                tableBody.innerHTML += `
                            <tr>
                                <td>${row.id}</td>
                                <td>${row.type}</td>
                                <td>${row.start_date}</td>
                                <td>${row.end_date}</td>
                                <td>${row.description}</td>
                                <td>${row.weight}</td>
                                <td>${row.temperature}</td>
                                <td>${row.complaint}</td>
                            </tr>`;
                            });
                        }
                    })
                    .catch(() => {
                        tableBody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">Error fetching data.</td></tr>`;
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
    <?php include('../components/pet-records-script.php'); ?>
</body>

</html>