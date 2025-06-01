<?php
$conn = mysqli_connect("localhost", "root", "", "pethaus");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    // echo "Database connected.";
}
