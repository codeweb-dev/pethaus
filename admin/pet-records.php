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

                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <select name="owner_id" class="form-control <?php echo isset($errors['owner_id']) ? 'is-invalid' : ''; ?>">
                                                            <option value="" disabled <?php echo empty($old['owner_id']) ? 'selected' : ''; ?>>Select Owner</option>
                                                            <?php
                                                            $owners = mysqli_query($conn, "
                                                                                    SELECT owner_id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name 
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
                                                <select class="form-control <?php echo isset($errors['species']) ? 'is-invalid' : ''; ?>" name="species" id="species" required onchange="toggleOtherSpeciesInput()">
                                                    <option value="" disabled <?php echo empty($old['species']) ? 'selected' : ''; ?>>Select species</option>
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
                                                <input type="text" class="form-control <?php echo isset($errors['breed']) ? 'is-invalid' : ''; ?>" placeholder="Enter breed" name="breed" id="breed" required>
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
                                                <label for="photo" class="form-label">Upload Photo</label>
                                                <input type="file" class="form-control <?php echo isset($errors['photo']) ? 'is-invalid' : ''; ?>" name="photo" id="photo" accept="image/*" value="<?php echo htmlspecialchars($old['photo'] ?? ''); ?>">
                                                <?php if (isset($errors['photo'])): ?>
                                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['photo']); ?></div>
                                                <?php endif; ?>
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

    <?php if (isset($_GET['modal']) && $_GET['modal'] === 'add' && !empty($errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addNewPet'));
                myModal.show();
            });
        </script>
    <?php endif; ?>

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

        const dogBreeds = ["Akita", "American Bulldog", "Australian Shepherd", "Alaskan Malamute", "Airedale Terrier", "Anatolian Shepherd", "Afghan Hound", "Beagle", "Boxer", "Boston Terrier", "Border Collie", "Bichon Frise", "Bernese Mountain Dog", "Bull Terrier", "Belgian Malinois", "Bloodhound", "Chihuahua", "Cocker Spaniel", "Cavalier King Charles Spaniel", "Chow Chow", "Collie", "Chesapeake Bay Retriever", "Cairn Terrier", "Cardigan Welsh Corgi", "Clumber Spaniel", "Dachshund", "Dalmatian", "Doberman Pinscher", "Dogo Argentino", "Dutch Shepherd", "Dandie Dinmont Terrier", "English Bulldog", "English Setter", "English Springer Spaniel", "Entlebucher Mountain Dog", "English Foxhound", "French Bulldog", "Flat-Coated Retriever", "Finnish Spitz", "Finnish Lapphund", "Field Spaniel", "Fox Terrier", "Golden Retriever", "German Shepherd", "Great Dane", "Greyhound", "Glen of Imaal Terrier", "Gordon Setter", "Giant Schnauzer", "German Shorthaired Pointer", "Husky (Siberian Husky)", "Havanese", "Harrier", "Hungarian Vizsla", "Hungarian Puli", "Hound (various breeds)", "Irish Setter", "Irish Terrier", "Irish Wolfhound", "Icelandic Sheepdog", "Italian Greyhound", "Jack Russell Terrier", "Japanese Chin", "Japanese Spitz", "King Charles Spaniel", "Keeshond", "Kerry Blue Terrier", "Kuvasz", "Komondor", "Labrador Retriever", "Lhasa Apso", "Lowchen", "Lagotto Romagnolo", "Maltese", "Mastiff (English Mastiff)", "Miniature Schnauzer", "Manchester Terrier", "Norwegian Lundehund", "Newfoundland", "Norfolk Terrier", "Neapolitan Mastiff", "Norwegian Elkhound", "Otterhound", "Old English Sheepdog", "Olde English Bulldogge", "Poodle", "Pembroke Welsh Corgi", "Papillon", "Pug", "Portuguese Water Dog", "Polish Lowland Sheepdog", "Queensland Heeler (Australian Cattle Dog)", "Rottweiler", "Rhodesian Ridgeback", "Russian Toy", "Rat Terrier", "Shih Tzu", "Samoyed", "Saint Bernard", "Scottish Terrier", "Staffordshire Bull Terrier", "Shar Pei", "Tibetan Mastiff", "Toy Fox Terrier", "Treeing Walker Coonhound", "Tervuren", "Utonagan", "Vizsla", "Volpino Italiano", "Weimaraner", "Welsh Springer Spaniel", "West Highland White Terrier", "Whippet", "Wire Fox Terrier", "Xoloitzcuintli (Mexican Hairless Dog)", "Yorkshire Terrier", "Zwergspitz (Pomeranian)", "Zuchon (Shichon, Shih Tzu + Bichon mix)"];

        const catBreeds = ["Siamese", "Persian", "Maine Coon", "Ragdoll", "British Shorthair", "Bengal", "Sphynx", "Scottish Fold", "Abyssinian", "Birman", "Oriental Shorthair", "Devon Rex", "Russian Blue", "Norwegian Forest Cat", "Exotic Shorthair", "Turkish Angora", "Cornish Rex", "Himalayan", "Balinese", "Tonkinese", "American Shorthair", "Manx", "LaPerm", "Singapura", "Somali", "Ocicat", "Bombay", "Chartreux", "Turkish Van", "Ragamuffin"];

        const speciesSelect = document.getElementById('species');
        const breedContainer = document.getElementById('breed-container');

        speciesSelect.addEventListener('change', function() {
            const selectedSpecies = this.value;
            breedContainer.innerHTML = '';

            const label = document.createElement('label');
            label.textContent = 'Breed';
            label.className = 'form-label';

            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group';

            if (selectedSpecies === 'Dog' || selectedSpecies === 'Cat') {
                const select = document.createElement('select');
                select.name = 'breed';
                select.id = 'breed';
                select.className = 'form-select';
                select.required = true;

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select breed';
                select.appendChild(defaultOption);

                const breeds = (selectedSpecies === 'Dog') ? dogBreeds : catBreeds;
                breeds.forEach(breed => {
                    const option = document.createElement('option');
                    option.value = breed;
                    option.textContent = breed;
                    select.appendChild(option);
                });

                inputGroup.appendChild(select);
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.placeholder = 'Enter breed';
                label.textContent = 'Please specify breed';
                input.name = 'breed';
                input.id = 'breed';
                input.required = true;
                inputGroup.appendChild(input);
            }

            breedContainer.appendChild(label);
            breedContainer.appendChild(inputGroup);
        });
    </script>
</body>

</html>