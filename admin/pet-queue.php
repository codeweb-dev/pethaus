<?php
session_start();
include('../conn.php');

// Handle registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $ownerName = $_POST['owner_name'];
    $petName = $_POST['pet_name'];
    $serviceType = $_POST['service_type'];
    $userId = $_SESSION['user_id'];

    // Generate queue number (e.g., Q0001, Q0002, etc.)
    $result = $conn->query("SELECT MAX(queue_id) AS max_id FROM pet_queue");
    $row = $result->fetch_assoc();
    $nextId = $row['max_id'] + 1;
    $queueNumber = 'Q' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO pet_queue (queue_number, owner_name, pet_name, service_type, status, created_at, user_id) VALUES (?, ?, ?, ?, 'Waiting', NOW(), ?)");
    $stmt->bind_param("ssssi", $queueNumber, $ownerName, $petName, $serviceType, $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: pet-queue.php");
    exit();
}

// Handle action links
if (isset($_GET['action']) && isset($_GET['id'])) {
    $queue_id = intval($_GET['id']);
    switch ($_GET['action']) {
        case 'serve':
            // Allow only max 3 "In Service"
            $count = $conn->query("SELECT COUNT(*) as cnt FROM pet_queue WHERE status='In Service'")->fetch_assoc()['cnt'];
            if ($count < 3) {
                $conn->query("UPDATE pet_queue SET status='In Service' WHERE queue_id=$queue_id");
            }
            break;
        case 'complete':
            $conn->query("UPDATE pet_queue SET status='Done' WHERE queue_id=$queue_id");
            $_SESSION['play_sound'] = true;
            break;
        case 'remove':
            $conn->query("DELETE FROM pet_queue WHERE queue_id=$queue_id");
            break;
    }
    header("Location: pet-queue.php");
    exit();
}

// Get data
$queueList = $conn->query("SELECT * FROM pet_queue ORDER BY created_at ASC");
$currentServing = $conn->query("SELECT * FROM pet_queue WHERE status='In Service' ORDER BY created_at ASC LIMIT 1")->fetch_assoc();
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
    <title>Pet Queue</title>
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
            background-color: #296849;
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
            background-color: #296849;
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
    <audio id="queue-complete-sound" preload="auto">
        <source src="../assets/sounds/complete.mp3" type="audio/mpeg">
    </audio>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="main-wrapper">
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

        <div id="main-content" class="main-content p-4">
            <div class="w-auto">
                <h3 class="fw-bold mb-4">Pet Queue</h3>

                <div class="mb-4 text-end">
                    <button class="btn text-black" style="background-color: #FFD531;" data-bs-toggle="modal" data-bs-target="#registrationModal">
                        <i class="fa-solid fa-plus"></i> Register Pet to Queue
                    </button>
                </div>

                <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST">
                                <input type="hidden" name="register" value="1">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="registrationModalLabel">Register Pet</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label for="ownerName" class="form-label">Owner Name</label>
                                        <input type="text" class="form-control" id="ownerName" name="owner_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="petName" class="form-label">Pet Name</label>
                                        <input type="text" class="form-control" id="petName" name="pet_name" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="serviceType" class="form-label">Service Type</label>
                                        <select class="form-select" id="serviceType" name="service_type" required>
                                            <option selected disabled>Select service...</option>
                                            <option value="Checkup">Checkup</option>
                                            <option value="Vaccination">Vaccination</option>
                                            <option value="Grooming">Grooming</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn text-black" style="background-color: #ABD29B;" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn text-black" style="background-color: #FFD531;">Register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MANAGE QUEUE -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white text-black fw-bold">Manage Queue</div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>Queue No.</th>
                                    <th>Owner</th>
                                    <th>Pet</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <?php
                            $counter = 1;
                            $queueList->data_seek(0);
                            while ($row = $queueList->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td><?= htmlspecialchars($row['queue_number']) ?></td>
                                    <td><?= htmlspecialchars($row['owner_name']) ?></td>
                                    <td><?= htmlspecialchars($row['pet_name']) ?></td>
                                    <td><?= htmlspecialchars($row['service_type']) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Waiting'): ?>
                                            <span class="badge bg-warning text-dark">Waiting</span>
                                        <?php elseif ($row['status'] === 'In Service'): ?>
                                            <span class="badge bg-success">In Service</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Done</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'Waiting'): ?>
                                            <a href="?action=serve&id=<?= $row['queue_id'] ?>" class="btn btn-sm btn-success">Serve</a>
                                        <?php elseif ($row['status'] === 'In Service'): ?>
                                            <a href="?action=complete&id=<?= $row['queue_id'] ?>" class="btn btn-sm btn-secondary">Complete</a>
                                        <?php else: ?>
                                            <a href="?action=remove&id=<?= $row['queue_id'] ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        </table>
                    </div>
                </div>

                <!-- DISPLAY QUEUE -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-black fw-bold">Display Queue</div>
                    <div class="card-body">
                        <h4 class="text-primary mb-4">Currently Serving:
                            <span class="fw-bold text-dark">
                                <?= $currentServing ? $currentServing['pet_name'] . ' (' . $currentServing['owner_name'] . ')' : 'None' ?>
                            </span>
                        </h4>

                        <?php
                        $queueList->data_seek(0);
                        $waiting = $inService = $done = [];

                        while ($row = $queueList->fetch_assoc()) {
                            switch ($row['status']) {
                                case 'Waiting':
                                    $waiting[] = $row;
                                    break;
                                case 'In Service':
                                    $inService[] = $row;
                                    break;
                                default:
                                    $done[] = $row;
                                    break;
                            }
                        }

                        function renderQueueCards($data, $badgeClass)
                        {
                            foreach ($data as $item) {
                                echo '<div class="card p-3 shadow-sm mb-3">';
                                echo '<h6 class="fw-bold mb-1">'
                                    . htmlspecialchars($item['queue_number']) . ' - ' . htmlspecialchars($item['pet_name']) .
                                    ' <span class="badge ' . $badgeClass . '">' . htmlspecialchars($item['status']) . '</span></h6>';
                                echo '<small>Owner: ' . htmlspecialchars($item['owner_name']) . '</small>';
                                echo '</div>';
                            }
                        }
                        ?>

                        <div class="row">
                            <div class="col-md-4">
                                <h5 class="text-center">⏳ Waiting</h5>
                                <?php renderQueueCards($waiting, 'bg-warning text-dark'); ?>
                            </div>
                            <div class="col-md-4">
                                <h5 class="text-center">🛠️ In Service</h5>
                                <?php renderQueueCards($inService, 'bg-success'); ?>
                            </div>
                            <div class="col-md-4">
                                <h5 class="text-center">✅ Completed</h5>
                                <?php renderQueueCards($done, 'bg-secondary'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <?php if (isset($_SESSION['play_sound']) && $_SESSION['play_sound']): ?>
        <script>
            const audio = new Audio('../assets/sounds/complete.mp3');
            audio.play().catch(error => {
                console.warn("Autoplay blocked:", error);
            });
        </script>
    <?php unset($_SESSION['play_sound']);
    endif; ?>

    <?php include('../components/script.php'); ?>
</body>

</html>