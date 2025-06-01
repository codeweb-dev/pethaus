<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

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
    <title>Pet Record</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0 d-none d-md-block">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-3">Pet Record</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class=" w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-box"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for pets...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn bg-black text-white" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#addNewPet">
                            <i class="fa-solid fa-plus"></i> Add new pet
                        </button>

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

                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                                    <select name="owner_id" class="form-control" required>
                                                        <option value="" disabled selected>Select Owner</option>
                                                        <?php
                                                        $owners = mysqli_query($conn, "
                                                                        SELECT owner_id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name 
                                                                        FROM pet_owner_records 
                                                                        ORDER BY first_name
                                                                    ");
                                                        while ($row = mysqli_fetch_assoc($owners)) {
                                                            echo '<option value="' . $row['owner_id'] . '">' . htmlspecialchars($row['full_name']) . '</option>';
                                                        }

                                                        ?>
                                                    </select>
                                                    <a href="pet-owner-profiles.php">
                                                        <button type="button" class="btn bg-black text-white">Add new owner</button>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="name" class="form-label">Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter pet name" name="name" id="name" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="species" class="form-label">Species</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <select class="form-select" name="species" id="species" required onchange="toggleOtherSpeciesInput()">
                                                        <option value="" disabled selected>Select species</option>
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
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6 d-none" id="otherSpeciesContainer">
                                                <label for="other_species" class="form-label">Please specify species</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" name="other_species" id="other_species" placeholder="Enter species">
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="breed" class="form-label">Breed</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter breed" name="breed" id="breed" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="color" class="form-label">Color</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter color" name="color" id="color" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="sex" class="form-label">Sex</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <select class="form-select" name="sex" id="sex" required>
                                                        <option value="" disabled selected>Select sex</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="birthdate" class="form-label">Birthdate</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="date" class="form-control" placeholder="Enter birthdate" name="birthdate" id="birthdate" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="age" class="form-label">Age</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter age" name="age" id="age" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="markings" class="form-label">Markings</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter markings" name="markings" id="markings" required>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="photo" class="form-label">Upload Photo</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-paw"></i></span>
                                                    <input type="file" class="form-control" name="photo" id="photo" accept="image/*" required>
                                                </div>
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
                                                        <?php if (!empty($pet['photo']) && file_exists('../' . $pet['photo'])): ?>
                                                            <img src="../<?php echo $pet['photo']; ?>" alt="Pet Photo" class="img-fluid rounded">
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
                                        <!-- <i class="fa-solid fa-notes-medical"></i> -->
                                        <i class="fa-solid fa-x"></i>
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

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>
    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#staffTableBody tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function toggleOtherSpeciesInput() {
            const speciesSelect = document.getElementById('species');
            const otherContainer = document.getElementById('otherSpeciesContainer');

            if (speciesSelect.value === 'Others') {
                otherContainer.classList.remove('d-none');
                document.getElementById('other_species').setAttribute('required', 'required');
            } else {
                otherContainer.classList.add('d-none');
                document.getElementById('other_species').removeAttribute('required');
            }
        }
    </script>
</body>

</html>