<?php
include('../conn.php');
session_start();
include('../actions/check_user.php');

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalUsersResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM pet_owner_records");
$totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
$totalUsers = $totalUsersRow['total'];
$totalPages = ceil($totalUsers / $limit);

$owners = [];
$query = "
    SELECT o.*, COUNT(p.pet_id) AS pet_count
    FROM pet_owner_records o
    LEFT JOIN pet_records p ON o.owner_id = p.owner_id
    GROUP BY o.owner_id
    ORDER BY o.owner_id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $owners[] = $row;
    }
}

$petRecordsByOwner = [];

$petQuery = "
    SELECT * FROM pet_records
    ORDER BY owner_id DESC
";
$petResult = mysqli_query($conn, $petQuery);
if ($petResult) {
    while ($pet = mysqli_fetch_assoc($petResult)) {
        $petRecordsByOwner[$pet['owner_id']][] = $pet;
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
    <title>Pet Owner Profiles</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0 d-none d-md-block">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-3">Pet Owner Profiles</h3>

                <div class="d-flex justify-content-lg-between mb-5 gap-3 flex-md-row flex-column">
                    <div class=" w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search for owners...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn bg-black text-white" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>

                        <button type="button" class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#addOwner">
                            <i class="fa-solid fa-plus"></i> Add new owner
                        </button>

                        <div class="modal fade" id="addOwner" tabindex="-1" aria-labelledby="addOwnerLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="../actions/add_owner.php">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bold" id="addNewStaffLabel">Add new owner</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your first name" name="first_name" id="first_name" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="middle_name" class="form-label">Middle name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your middle name (Optional)" name="middle_name" id="middle_name">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your last name" name="last_name" id="last_name">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your address" name="address" id="address" required>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your mobile number" name="mobile_number" id="mobile_number" required>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="messenger_account" class="form-label">Messenger Account</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-circle-user"></i></span>
                                                    <input type="text" class="form-control" placeholder="Enter your messenger account (Optional)" name="messenger_account" id="messenger_account">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-black text-white">Add new owner</button>
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
                                <th class="p-3" scope="col">Full Name</th>
                                <th class="p-3" scope="col">Address</th>
                                <th class="p-3" scope="col">Mobile Number</th>
                                <th class="p-3" scope="col">Messenger Account</th>
                                <th class="p-3" scope="col">Number Pets Owned</th>
                                <th class="p-3" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted" id="staffTableBody">
                            <?php foreach ($owners as $owner): ?>
                                <tr class="hover:bg-gray-100 border-b">
                                    <td class="p-3"><?php echo $owner['owner_id']; ?></td>
                                    <td class="p-3">
                                        <?php
                                        echo $owner['first_name'] . ' ';
                                        echo !empty($owner['middle_name']) ? $owner['middle_name'][0] . '. ' : '';
                                        echo $owner['last_name'];
                                        ?>
                                    </td>
                                    <td class="p-3"><?php echo $owner['address']; ?></td>
                                    <td class="p-3"><?php echo $owner['mobile_number']; ?></td>
                                    <td class="p-3"><?php echo $owner['messenger_account']; ?></td>
                                    <td class="p-3"><?php echo $owner['pet_count']; ?></td>

                                    <td class="d-flex gap-3 p-3">
                                        <i class="fa-solid fa-paw" title="View Pets" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#viewPetsModal<?php echo $owner['owner_id']; ?>"></i>

                                        <div class="modal fade" id="viewPetsModal<?php echo $owner['owner_id']; ?>" tabindex="-1" aria-labelledby="viewPetsModalLabel<?php echo $owner['owner_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewPetsModalLabel<?php echo $owner['owner_id']; ?>">
                                                            Pets of <?php echo $owner['first_name'] . ' ' . $owner['last_name']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (!empty($petRecordsByOwner[$owner['owner_id']])): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="p-3">Name</th>
                                                                            <th class="p-3">Species</th>
                                                                            <th class="p-3">Breed</th>
                                                                            <th class="p-3">Color</th>
                                                                            <th class="p-3">Sex</th>
                                                                            <th class="p-3">Birthdate</th>
                                                                            <th class="p-3">Age</th>
                                                                            <th class="p-3">Markings</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($petRecordsByOwner[$owner['owner_id']] as $pet): ?>
                                                                            <tr>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['name']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['species']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['breed']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['color']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['sex']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['birthdate']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['age']); ?></td>
                                                                                <td class="p-3"><?php echo htmlspecialchars($pet['markings']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-muted">No pets found for this owner.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <i class="fa-solid fa-pen-to-square" title="Edit owner" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editOwnerModal<?php echo $owner['owner_id']; ?>"></i>

                                        <div class="modal fade" id="editOwnerModal<?php echo $owner['owner_id']; ?>" tabindex="-1" aria-labelledby="editPetModalLabel<?php echo $owner['owner_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST" action="../actions/update_owner.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editPetModalLabel<?php echo $owner['owner_id']; ?>">Edit Owner: <?php echo $owner['first_name']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body row">
                                                            <input type="hidden" name="owner_id" value="<?php echo $owner['owner_id']; ?>">

                                                            <div class="mb-3 col-md-6">
                                                                <label for="first_name" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" name="first_name" value="<?php echo $owner['first_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="middle_name" class="form-label">Middle Name</label>
                                                                <input type="text" class="form-control" name="middle_name" value="<?php echo $owner['middle_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="last_name" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" name="last_name" value="<?php echo $owner['last_name']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="address" class="form-label">Address</label>
                                                                <input type="text" class="form-control" name="address" value="<?php echo $owner['address']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                                                <input type="text" class="form-control" name="mobile_number" value="<?php echo $owner['mobile_number']; ?>" required>
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label for="messenger_account" class="form-label">Messenger Account</label>
                                                                <input type="text" class="form-control" name="messenger_account" value="<?php echo $owner['messenger_account']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn bg-black text-white">Update Owner</button>
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
    </script>
</body>

</html>