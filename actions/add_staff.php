<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Required fields
    $requiredFields = [
        'first_name',
        'last_name',
        'username',
        'password'
    ];

    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $type = "staff";

    // Additional validations
    if (!empty($first_name) && !preg_match("/^[a-zA-Z]+$/", $first_name)) {
        $errors['first_name'] = "Firstname must contain only letters.";
    }

    if (!empty($middle_name) && !preg_match("/^[a-zA-Z]+$/", $middle_name)) {
        $errors['middle_name'] = "Middlename must contain only letters.";
    }

    if (!empty($last_name) && !preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $errors['last_name'] = "Lastname must contain only letters.";
    }

    if (!empty($username)) {
        if (strlen($username) < 4) {
            $errors['username'] = "Username must be at least 4 characters.";
        } else {
            $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->bind_result($count);
            $check->fetch();
            $check->close();

            if ($count > 0) {
                $errors['username'] = "Username already exists. Choose a different one.";
            }
        }
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pethaus-staff.php?modal=add");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, username, password, type) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $username, $hashed_password, $type);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['success'] = "Successfully added new staff.";
        header("Location: ../admin/pethaus-staff.php?success=Successfully added new staff.");
        exit();
    } else {
        $stmt->close();
        $_SESSION['form_errors'] = ['general' => 'Failed to add new staff.'];
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pethaus-staff.php?modal=add");
        exit();
    }
} else {
    header("Location: ../admin/pethaus-staff.php");
    exit();
}
