<?php
include('conn.php');
session_start();

$check_admin = $conn->query("SELECT * FROM users WHERE type = 'admin' LIMIT 1");
if ($check_admin->num_rows > 0) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $type = "admin";

    $errors = [];

    if (empty($first_name) || !preg_match("/^[a-zA-Z]+$/", $first_name)) {
        $errors[] = "Firstname is required and must contain only letters.";
    }

    if (!empty($middle_name) && !preg_match("/^[a-zA-Z]+$/", $middle_name)) {
        $errors[] = "Middlename must contain only letters.";
    }

    if (empty($last_name) || !preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $errors[] = "Lastname is required and must contain only letters.";
    }

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters.";
    } else {
        $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $errors[] = "Username already exists. Choose a different one.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (!empty($errors)) {
        $error_message = urlencode(implode(" ", $errors));
        header("Location: index.php?error=$error_message");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, username, password, type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $username, $hashed_password, $type);
    $stmt->execute();
    $stmt->close();

    header("Location: login.php?success=Registered Successfully.");
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
    <title>Pethaus Register</title>
</head>

<body>
    <div class="container-fluid p-0 min-vh-100">
        <div class="d-flex flex-column flex-md-row w-100 h-100">
            <div class="d-none d-md-flex col-md-6 justify-content-center align-items-center text-center bg-black text-white p-4">
                <div>
                    <img src="assets/images/pethaus_logo.png" alt="PetHaus Logo" class="mb-4" style="width: 150px;">
                    <h2 class="fw-bold">PetHaus Animal Clinic and Supplies</h2>
                    <p>“The first and finest in veterinary care”</p>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center col-12 col-md-6 p-4" style="min-height: 100vh;">
                <div class="w-100" style="max-width: 500px;">
                    <h2 class="fw-bold">New here?</h2>
                    <p class="text-muted">Welcome! Let's set up your admin account to get started.</p>

                    <form method="POST">
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
                                <input type="text" class="form-control" placeholder="Enter your middle name (optional)" name="middle_name" id="middle_name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Enter your last name" name="last_name" id="last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
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

                        <button type="submit" class="btn bg-black text-white w-100">REGISTER</button>

                        <p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-black">Login</a></p>

                        <?php include('components/toast.php'); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>