<?php
include('conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || strlen($username) < 4) {
        header('Location: login.php?error=' . urlencode('Please enter a valid username.'));
        exit();
    }

    if (empty($password) || strlen($password) < 6) {
        header('Location: login.php?error=' . urlencode('Please enter your password.'));
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['type'] = $user['type'];
            $_SESSION['logged_in'] = true;

            header('Location: admin/dashboard.php');
            exit();
        }
    }

    header('Location: login.php?error=' . urlencode('Incorrect username or password.'));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/images/pethaus_logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Pethaus Login</title>
</head>

<body>
    <div class="container-fluid p-0 min-vh-100">
        <div class="d-flex flex-column flex-md-row w-100 h-100">
            <div class="d-none d-md-flex flex-md-column justify-content-center align-items-center text-white col-md-6 p-4" style="background-color: #296849;">
                <div class="text-center">
                    <img src="assets/images/pethaus_logo.png" alt="PetHaus Logo" class="mb-4" style="width: 150px;">
                    <h2 class="fw-bold">PetHaus Animal Clinic and Supplies</h2>
                    <p>“The first and finest in veterinary care”</p>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center col-12 col-md-6 p-4" style="min-height: 100vh;">
                <div class="w-100" style="max-width: 400px;">
                    <h2 class="fw-bold">Welcome back admin</h2>
                    <p class="text-muted">Please enter your credentials to proceed</p>

                    <form method="POST">
                        <div class="mb-3 mt-5">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Enter your username" name="username" id="username" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" placeholder="Enter your password" name="password" id="password" required>
                            </div>
                        </div>

                        <?php include('components/toast.php'); ?>

                        <button type="submit" class="btn text-black w-100" style="background-color: #FFD531;">LOGIN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>