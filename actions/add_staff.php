<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $type = "staff";

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

    header("Location: ../admin/pethaus-staff.php?success=Registered Successfully.");
    exit();
}
